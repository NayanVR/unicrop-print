<x-app-layout>
    <x-slot name="header">Cutting Station</x-slot>

    <div class="mb-6">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:0.06em;color:#111;line-height:1;">Cutting Station</h2>
        <p style="font-size:13px;color:#717171;margin-top:4px;">Process jobs that are printed and waiting for cutting.</p>
    </div>

    <form method="GET" action="{{ route('cutting.index') }}" class="filter-bar">
        <label class="">Filter By:</label>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search note or file name..." >
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <button type="submit">Apply</button>
        @if ($search !== '')
            <a href="{{ route('cutting.index') }}" style="color:rgba(255,255,255,0.45);font-size:12px;">Reset filters</a>
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

    <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px;" x-data="{ previewUrl: '', previewMime: '', previewName: '', open: false }">

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
                        <th class="px-4 py-3 font-semibold">File & Details</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('sheets', 'Sheets Printed') !!}</th>
                        <th class="px-4 py-3 font-semibold">Cutting Type & Rate</th>
                        <th class="px-4 py-3 font-semibold">Cutting Jobs</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
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
                                            class="block w-16 h-16 rounded-lg border border-slate-200 overflow-hidden hover:ring-2 hover:ring-purple-400 transition">
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
                            <td class="px-4 py-3" x-data="{ editNote: false }">
                                <div x-show="!editNote" class="flex items-center gap-1.5 mb-1">
                                    <strong class="text-sm">{{ $job->note }}</strong>
                                    <button type="button" @click="editNote = true" class="text-sky-400 hover:text-sky-600 text-xs"><i class="fa-solid fa-pen-to-square"></i></button>
                                </div>
                                <form x-show="editNote" method="POST" action="{{ route('jobs.note.update', $job) }}" class="flex gap-1.5 mb-1">
                                    @csrf @method('PATCH')
                                    <input type="text" name="note" value="{{ $job->note === '-' ? '' : $job->note }}" placeholder="Note..." class="rounded border-slate-300 px-2 py-1 text-sm w-36">
                                    <button type="submit" class="bg-sky-500 text-white text-xs px-2 py-1 rounded font-semibold">Save</button>
                                    <button type="button" @click="editNote = false" class="text-xs text-slate-400 hover:text-slate-600">✕</button>
                                </form>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span><br>
                                <span class="text-xs text-slate-400">
                                    {{ $job->formattedFileSize() ?? 'Unknown size' }}
                                    @if ($job->mime_type)
                                        &middot; {{ $job->mime_type }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $job->sheets }} Sheets<br>
                                <span class="text-xs text-slate-500">Printed At: {{ $job->printed_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-slate-600 font-semibold text-xs">{{ $job->cuttingType?->name ?? '-' }}</span><br>
                                <span class="text-emerald-600 font-bold text-xs">{{ $job->printStation->rateForCuttingType($job->cuttingType) }} Rs / Job</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" form="cut-job-{{ $job->id }}" name="cutting_jobs" value="1" min="0" class="w-20 rounded border-slate-300 px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <form id="cut-job-{{ $job->id }}" method="POST" action="{{ route('cutting.update', $job) }}">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <button type="submit" form="cut-job-{{ $job->id }}" style="background:#F05A28;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;">
                                    Mark Cut & Done
                                </button>
                                @if (auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('jobs.destroy', $job) }}" class="mt-2"
                                        onsubmit="return confirm('Delete Job #{{ $job->id }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 hover:bg-red-50 text-xs px-2 py-1 rounded transition inline-flex items-center gap-1">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No pending cutting jobs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $jobs->links() }}
        </div>
    </div>
</x-app-layout>
