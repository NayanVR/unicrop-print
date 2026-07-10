<x-app-layout>
    <x-slot name="header">Storage Usage</x-slot>

    @php
        function fmtBytes(int|float|null $bytes): string {
            $bytes = (float) $bytes;
            if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
            if ($bytes >= 1048576)    return round($bytes / 1048576, 1)  . ' MB';
            if ($bytes >= 1024)       return round($bytes / 1024, 1)     . ' KB';
            return $bytes . ' B';
        }
    @endphp

    <div class="mb-6">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:0.06em;color:#111;line-height:1;">Storage Usage</h2>
        <p style="font-size:13px;color:#717171;margin-top:4px;">Total space used by uploaded design files.</p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Used</div>
            <div class="text-3xl font-black text-slate-800">{{ fmtBytes($totalBytes) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $activeCount + $binCount }} files total</div>
        </div>
        <div class="bg-white border border-emerald-200 rounded-xl p-5">
            <div class="text-xs font-semibold text-emerald-500 uppercase tracking-wide mb-1">Active Jobs</div>
            <div class="text-3xl font-black text-emerald-700">{{ fmtBytes($activeBytes) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $activeCount }} files</div>
        </div>
        <div class="bg-white border border-red-200 rounded-xl p-5">
            <div class="text-xs font-semibold text-red-400 uppercase tracking-wide mb-1">In Bin</div>
            <div class="text-3xl font-black text-red-500">{{ fmtBytes($binBytes) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $binCount }} files</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Per uploader --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users text-slate-400"></i> By Uploader
            </h3>
            @if ($byUploader->isEmpty())
                <p class="text-slate-400 text-sm">No uploads yet.</p>
            @else
                @php $maxBytes = $byUploader->max('bytes'); @endphp
                <div class="space-y-3">
                    @foreach ($byUploader as $row)
                        @php $pct = $maxBytes > 0 ? round($row->bytes / $maxBytes * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-semibold text-slate-700">{{ $row->name }}</span>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-slate-800">{{ fmtBytes($row->bytes) }}</span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $row->files }} files</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="h-2 bg-teal-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Daily usage (last 30 days) --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-slate-400"></i> Last 30 Days (Daily Uploads)
            </h3>
            @if ($byDay->isEmpty())
                <p class="text-slate-400 text-sm">No uploads in last 30 days.</p>
            @else
                @php $maxDayBytes = $byDay->max('bytes'); @endphp
                <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                    @foreach ($byDay->sortByDesc('day') as $row)
                        @php $pct = $maxDayBytes > 0 ? round($row->bytes / $maxDayBytes * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between items-center mb-0.5">
                                <span class="text-xs font-semibold text-slate-600">
                                    {{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}
                                </span>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-slate-700">{{ fmtBytes($row->bytes) }}</span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $row->files }} files</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="h-1.5 bg-sky-400 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
