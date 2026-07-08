<x-app-layout>
    <x-slot name="header">Print Queue Station</x-slot>

    {{-- Live new-job notification --}}
    <div id="new-job-banner" class="hidden mb-4 bg-emerald-500 text-white rounded-xl px-5 py-3 flex items-center justify-between gap-4 shadow-lg">
        <span class="flex items-center gap-2 font-semibold text-sm">
            <i class="fa-solid fa-bell animate-bounce"></i>
            <span id="new-job-text">New print jobs arrived!</span>
        </span>
        <button onclick="location.reload()"
            class="bg-white text-emerald-700 font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-emerald-50 transition">
            Refresh Now
        </button>
    </div>

    <script>
        (function () {
            const pollUrl = '{{ route('printer.poll') }}';
            let knownLatestId = {{ $jobs->isNotEmpty() ? $jobs->first()->id : 0 }};
            let knownCount = {{ $jobs->total() }};
            let notified = false;

            async function poll() {
                try {
                    const res = await fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) return;
                    const data = await res.json();

                    if (!notified && (data.latest_id > knownLatestId || data.count > knownCount)) {
                        notified = true;
                        const diff = data.count - knownCount;
                        document.getElementById('new-job-text').textContent =
                            (diff > 0 ? diff + ' new print job' + (diff > 1 ? 's' : '') + ' arrived!' : 'Print queue updated!');
                        document.getElementById('new-job-banner').classList.remove('hidden');
                        // auto-reload after 8 seconds if user doesn't act
                        setTimeout(() => location.reload(), 8000);
                    }
                } catch {}
            }

            // Poll every 10 seconds, pause when tab is hidden
            let interval = setInterval(poll, 10000);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(interval);
                } else {
                    poll();
                    interval = setInterval(poll, 10000);
                }
            });
        })();
    </script>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Print Queue Station</h2>
        <p class="text-slate-500 text-sm mt-1">Process pending print jobs. Once done, they will be sent to the Cutting Station.</p>
    </div>

    <form method="GET" action="{{ route('printer.index') }}" class="flex flex-wrap gap-4 items-center bg-slate-100 p-4 rounded-lg mb-6">
        <label class="font-bold text-sm">Filter By:</label>
        <select name="station_id" onchange="this.form.submit()" class="rounded border-slate-300 px-3 py-2 text-sm">
            <option value="all" @selected($stationId === 'all')>All Stations</option>
            @foreach ($stations as $station)
                <option value="{{ $station->id }}" @selected($stationId === (string) $station->id)>{{ $station->name }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search note or file name..." class="rounded border-slate-300 px-3 py-2 text-sm flex-1 min-w-[200px]">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <button type="submit" class="bg-slate-800 text-white text-sm px-4 py-2 rounded">Apply</button>
        @if ($stationId !== 'all' || $search !== '')
            <a href="{{ route('printer.index') }}" class="text-xs text-slate-500 underline">Reset filters</a>
        @endif
    </form>

    @php
        $sortLink = function (string $column, string $label) use ($sort, $direction) {
            $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
            $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection]);
            $icon = $sort === $column
                ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down')
                : 'fa-sort text-slate-300';

            return '<a href="'.$url.'" class="inline-flex items-center gap-1 hover:text-slate-800">'.$label.' <i class="fa-solid '.$icon.' text-[10px]"></i></a>';
        };
    @endphp

    <div class="bg-white border border-slate-200 rounded-xl p-6" x-data="{ previewUrl: '', previewMime: '', previewName: '', open: false }">

        {{-- File preview modal --}}
        <dialog x-bind:open="open" @click.self="open = false"
            class="fixed inset-0 z-50 w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl p-0 border-0 backdrop:bg-black/60 overflow-hidden">
            <div class="flex items-center justify-between bg-slate-800 text-white px-5 py-3">
                <span class="text-sm font-semibold truncate" x-text="previewName"></span>
                <button @click="open = false" class="text-white/70 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <div class="bg-black flex items-center justify-center" style="height: calc(90vh - 52px)">
                <template x-if="previewMime === 'application/pdf'">
                    <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                </template>
                <template x-if="previewMime !== 'application/pdf'">
                    <img :src="previewUrl" :alt="previewName" class="max-w-full max-h-full object-contain">
                </template>
            </div>
        </dialog>

        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('id', 'Job ID') !!}</th>
                        <th class="px-4 py-3 font-semibold">Preview</th>
                        <th class="px-4 py-3 font-semibold">Station</th>
                        <th class="px-4 py-3 font-semibold">File & Note</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('created_at', 'Upload Time') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('rate', 'Size & Rate') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('sheets', 'Req. Sheets') !!}</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        @if ($job->needs_lamination)
                            {{-- Lamination banner row --}}
                            <tr class="bg-indigo-600">
                                <td colspan="8" class="px-4 py-1.5">
                                    <span class="text-white text-xs font-bold flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group"></i>
                                        LAMINATION REQUIRED — {{ $job->laminationType?->name ?? 'Lamination' }}
                                        &nbsp;·&nbsp; {{ $job->lamination_rate }} Rs/sheet &nbsp;·&nbsp; Total: {{ number_format($job->lamination_total, 2) }} Rs
                                    </span>
                                </td>
                            </tr>
                        @endif
                        <tr class="{{ $job->needs_lamination ? 'bg-indigo-50 ring-2 ring-inset ring-indigo-400' : '' }}">
                            <td class="px-4 py-3 {{ $job->needs_lamination ? 'font-bold text-indigo-700' : '' }}">#{{ $job->id }}</td>
                            <td class="px-4 py-3">
                                @if ($job->fileUrl())
                                    @if (str_contains($job->mime_type ?? '', 'pdf'))
                                        <button type="button"
                                            @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; open = true"
                                            class="flex items-center justify-center w-16 h-16 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 transition text-red-500 flex-col gap-1">
                                            <i class="fa-solid fa-file-pdf text-2xl"></i>
                                            <span class="text-[9px] font-semibold">PDF</span>
                                        </button>
                                    @else
                                        <button type="button"
                                            @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; open = true"
                                            class="block w-16 h-16 rounded-lg {{ $job->needs_lamination ? 'border-2 border-indigo-400 ring-2 ring-indigo-300' : 'border border-slate-200' }} overflow-hidden hover:ring-2 hover:ring-purple-400 transition">
                                            <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}"
                                                class="w-full h-full object-cover" loading="lazy">
                                        </button>
                                    @endif
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300">
                                        <i class="fa-solid fa-image text-2xl"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded">{{ $job->printStation?->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <strong>Note: {{ $job->note }}</strong><br>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span><br>
                                <span class="text-xs text-slate-400">
                                    {{ $job->formattedFileSize() ?? 'Unknown size' }}
                                    @if ($job->mime_type)
                                        &middot; {{ $job->mime_type }}
                                    @endif
                                </span><br>
                                @if ($job->fileUrl())
                                    <div class="flex flex-wrap gap-1 mt-1" x-data="{ copied: false }">
                                        <a href="{{ $job->fileUrl() }}" target="_blank" class="inline-flex items-center gap-1 bg-purple-500 text-white text-xs px-3 py-1 rounded">
                                            <i class="fa-solid fa-print"></i> View/Print
                                        </a>
                                        <a href="{{ $job->downloadUrl() }}" class="inline-flex items-center gap-1 bg-slate-600 text-white text-xs px-3 py-1 rounded">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                        <button
                                            type="button"
                                            @click="navigator.clipboard.writeText('{{ $job->publicShareUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="inline-flex items-center gap-1 bg-sky-500 text-white text-xs px-3 py-1 rounded"
                                        >
                                            <i class="fa-solid fa-link"></i>
                                            <span x-text="copied ? 'Copied!' : 'Share Link'"></span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-slate-100 border border-slate-200 px-2 py-1 rounded">{{ $job->created_at->format('d/m/Y - h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $job->size->name }}<br>
                                <span class="text-emerald-600 font-bold text-xs">Rate: {{ $job->rate }} Rs</span><br>
                                @if ($job->needs_lamination)
                                    <span class="inline-flex items-center gap-1.5 mt-1 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                                        <i class="fa-solid fa-layer-group"></i> {{ $job->laminationType?->name ?? 'Lamination' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 mt-1 bg-slate-100 text-slate-400 text-[11px] px-2 py-0.5 rounded-full">No Lam</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" form="print-job-{{ $job->id }}" name="sheets" value="{{ $job->sheets }}" min="1" class="w-20 rounded border-slate-300 px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-3" x-data="{ open: false }">
                                <form id="print-job-{{ $job->id }}" method="POST" action="{{ route('printer.update', $job) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="cutting_required" x-ref="cr" value="">
                                </form>
                                @if ($canPrint)
                                    <button type="button" @click="open = true" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded inline-flex items-center gap-1">
                                        Mark Printed <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                    <dialog x-ref="dlg"
                                        x-effect="open ? $refs.dlg.showModal() : ($refs.dlg.open && $refs.dlg.close())"
                                        @click.self="open = false" @cancel.prevent="open = false"
                                        class="rounded-2xl shadow-2xl p-0 border-0 w-80 backdrop:bg-black/50">
                                        <div class="p-6 text-center">
                                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i class="fa-solid fa-scissors text-amber-500 text-xl"></i>
                                            </div>
                                            <h3 class="font-bold text-slate-800 text-base mb-1">Cutting Required?</h3>
                                            <p class="text-slate-500 text-xs mb-5">Job <strong>#{{ $job->id }}</strong> — choose where it goes next.</p>
                                            <div class="flex gap-3">
                                                <button type="button"
                                                    @click="$refs.cr.value = '1'; open = false; document.getElementById('print-job-{{ $job->id }}').submit()"
                                                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2.5 rounded-lg flex items-center justify-center gap-2">
                                                    <i class="fa-solid fa-scissors"></i> Cutting
                                                </button>
                                                <button type="button"
                                                    @click="$refs.cr.value = '0'; open = false; document.getElementById('print-job-{{ $job->id }}').submit()"
                                                    class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg flex items-center justify-center gap-2">
                                                    <i class="fa-solid fa-check"></i> Done
                                                </button>
                                            </div>
                                            <button type="button" @click="open = false" class="mt-3 text-xs text-slate-400 hover:text-slate-600 w-full">Cancel</button>
                                        </div>
                                    </dialog>
                                @else
                                    <span class="text-xs text-slate-400 italic">Printing blocked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No pending prints.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $jobs->links() }}
        </div>
    </div>
    </div>
</x-app-layout>
