<x-app-layout>
    <x-slot name="header">Storage</x-slot>

    @php
        function fmtBytes(int|float|null $bytes): string {
            $bytes = (float) $bytes;
            if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
            if ($bytes >= 1048576)    return round($bytes / 1048576, 1)  . ' MB';
            if ($bytes >= 1024)       return round($bytes / 1024, 1)     . ' KB';
            return $bytes . ' B';
        }
    @endphp

    <div class="mb-8">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:48px;letter-spacing:0.06em;color:#111;line-height:1;">Storage Usage</h2>
        <p style="font-size:13px;color:#717171;margin-top:4px;">Total space used by uploaded design files.</p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div style="background:#111;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:10.5px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:8px;">Total Used</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:44px;color:#F05A28;line-height:1;">{{ fmtBytes($totalBytes) }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:4px;">{{ $activeCount + $binCount }} files total</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:24px;color:rgba(255,255,255,0.05);"><i class="fa-solid fa-hard-drive"></i></div>
        </div>
        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:10.5px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#A0A0A0;margin-bottom:8px;">Active Jobs</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:44px;color:#111;line-height:1;">{{ fmtBytes($activeBytes) }}</div>
            <div style="font-size:11px;color:#A0A0A0;margin-top:4px;">{{ $activeCount }} files</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:24px;color:rgba(0,0,0,0.04);"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div style="background:#fff;border:1.5px solid #FECACA;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:10.5px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#F87171;margin-bottom:8px;">In Bin</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:44px;color:#EF4444;line-height:1;">{{ fmtBytes($binBytes) }}</div>
            <div style="font-size:11px;color:#A0A0A0;margin-top:4px;">{{ $binCount }} files</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:24px;color:rgba(239,68,68,0.06);"><i class="fa-solid fa-trash-can"></i></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Per uploader --}}
        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                <div style="width:32px;height:32px;background:#111;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-users" style="color:#F05A28;font-size:13px;"></i>
                </div>
                <span style="font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:0.06em;color:#111;">By Uploader</span>
            </div>
            @if ($byUploader->isEmpty())
                <p style="color:#A0A0A0;font-size:13.5px;">No uploads yet.</p>
            @else
                @php $maxBytes = $byUploader->max('bytes'); @endphp
                <div style="display:flex;flex-direction:column;gap:14px;">
                    @foreach ($byUploader as $row)
                        @php $pct = $maxBytes > 0 ? round($row->bytes / $maxBytes * 100) : 0; @endphp
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                                <span style="font-size:13px;font-weight:600;color:#1A1A1A;">{{ $row->name }}</span>
                                <div style="text-align:right;">
                                    <span style="font-size:13px;font-weight:700;color:#111;">{{ fmtBytes($row->bytes) }}</span>
                                    <span style="font-size:11px;color:#A0A0A0;margin-left:5px;">{{ $row->files }} files</span>
                                </div>
                            </div>
                            <div style="width:100%;background:#F0F0EE;border-radius:999px;height:6px;">
                                <div style="height:6px;background:#F05A28;border-radius:999px;width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Daily usage --}}
        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                <div style="width:32px;height:32px;background:#111;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-calendar-days" style="color:#F05A28;font-size:13px;"></i>
                </div>
                <span style="font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:0.06em;color:#111;">Last 30 Days</span>
            </div>
            @if ($byDay->isEmpty())
                <p style="color:#A0A0A0;font-size:13.5px;">No uploads in last 30 days.</p>
            @else
                @php $maxDayBytes = $byDay->max('bytes'); @endphp
                <div style="display:flex;flex-direction:column;gap:10px;max-height:320px;overflow-y:auto;padding-right:4px;">
                    @foreach ($byDay->sortByDesc('day') as $row)
                        @php $pct = $maxDayBytes > 0 ? round($row->bytes / $maxDayBytes * 100) : 0; @endphp
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:12px;font-weight:600;color:#444;">{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</span>
                                <div>
                                    <span style="font-size:12px;font-weight:700;color:#111;">{{ fmtBytes($row->bytes) }}</span>
                                    <span style="font-size:11px;color:#A0A0A0;margin-left:4px;">{{ $row->files }} files</span>
                                </div>
                            </div>
                            <div style="width:100%;background:#F0F0EE;border-radius:999px;height:5px;">
                                <div style="height:5px;background:#111;border-radius:999px;width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
