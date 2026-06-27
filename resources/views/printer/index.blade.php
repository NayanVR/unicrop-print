<x-app-layout>
    <x-slot name="header">Print Queue Station</x-slot>

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

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('id', 'Job ID') !!}</th>
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
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
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
                                <span class="text-emerald-600 font-bold text-xs">Rate: {{ $job->rate }} Rs</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" form="print-job-{{ $job->id }}" name="sheets" value="{{ $job->sheets }}" min="1" class="w-20 rounded border-slate-300 px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <form id="print-job-{{ $job->id }}" method="POST" action="{{ route('printer.update', $job) }}">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                @if ($canPrint)
                                    <button type="submit" form="print-job-{{ $job->id }}" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded inline-flex items-center gap-1">
                                        Mark Printed <i class="fa-solid fa-arrow-right"></i>
                                    </button>
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
</x-app-layout>
