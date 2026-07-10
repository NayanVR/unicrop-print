<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- Hero heading --}}
    <div class="mb-8 flex items-end justify-between">
        <div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:48px;letter-spacing:0.06em;line-height:1;color:#111;">
                Dashboard Overview
            </div>
            <p style="font-size:13.5px;color:#717171;margin-top:4px;">
                Real-time status of all print jobs across stations.
            </p>
        </div>
        <div style="font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:0.12em;color:#F05A28;background:#FFF1EC;border:1.5px solid #F05A28;padding:6px 16px;border-radius:999px;">
            IST Live
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">

        <div style="background:#111;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:10px;">Pending Prints</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:#F05A28;line-height:1;">{{ $pending_prints }}</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:22px;color:rgba(255,255,255,0.06);">
                <i class="fa-solid fa-print"></i>
            </div>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#A0A0A0;margin-bottom:10px;">Pending Cuts</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:#111;line-height:1;">{{ $pending_cuts }}</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:22px;color:rgba(0,0,0,0.05);">
                <i class="fa-solid fa-scissors"></i>
            </div>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#A0A0A0;margin-bottom:10px;">Completed Jobs</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:#111;line-height:1;">{{ $completed }}</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:22px;color:rgba(0,0,0,0.05);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div style="background:#F05A28;border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.65);margin-bottom:10px;">Total Revenue</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:40px;color:#fff;line-height:1;word-break:break-all;">{{ number_format($revenue, 0) }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.6);margin-top:3px;font-weight:600;">Rs.</div>
            <div style="position:absolute;bottom:14px;right:16px;font-size:22px;color:rgba(255,255,255,0.12);">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
        </div>

    </div>

    {{-- Divider + Station breakdown --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:0.07em;color:#111;white-space:nowrap;">By Print Station</div>
        <div style="flex:1;height:1.5px;background:#E5E5E5;"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($stationStats as $stat)
            <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:12px;overflow:hidden;">
                <div style="background:#111;padding:12px 16px;display:flex;align-items:center;gap:9px;">
                    <i class="fa-solid fa-print" style="color:#F05A28;font-size:13px;"></i>
                    <span style="font-family:'Bebas Neue',sans-serif;font-size:17px;letter-spacing:0.07em;color:#fff;">{{ $stat['station']->name }}</span>
                </div>
                <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#A0A0A0;">Pending Prints</div>
                        <div style="font-family:'Bebas Neue',sans-serif;font-size:28px;color:#F05A28;margin-top:2px;">{{ $stat['pending_prints'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#A0A0A0;">Pending Cuts</div>
                        <div style="font-family:'Bebas Neue',sans-serif;font-size:28px;color:#111;margin-top:2px;">{{ $stat['pending_cuts'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#A0A0A0;">Completed</div>
                        <div style="font-family:'Bebas Neue',sans-serif;font-size:28px;color:#111;margin-top:2px;">{{ $stat['completed'] }}</div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#A0A0A0;">Revenue</div>
                        <div style="font-family:'Bebas Neue',sans-serif;font-size:22px;color:#F05A28;margin-top:4px;line-height:1.1;">{{ number_format($stat['revenue'], 0) }} <span style="font-size:12px;color:#A0A0A0;font-family:'DM Sans',sans-serif;">Rs</span></div>
                    </div>
                </div>
            </div>
        @empty
            <p style="color:#717171;font-size:13.5px;">No print stations assigned.</p>
        @endforelse
    </div>

</x-app-layout>
