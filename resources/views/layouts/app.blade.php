<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Unicrop Print') }}</title>

        {{-- Fonts: Bebas Neue (display) + DM Sans (body) --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bebas-neue:400|dm-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            :root {
                --orange:      #F05A28;
                --orange-dark: #C94418;
                --orange-pale: #FFF1EC;
                --black:       #111111;
                --black-2:     #1C1C1C;
                --black-3:     #2A2A2A;
                --white:       #FFFFFF;
                --bg:          #F5F5F3;
                --text:        #1A1A1A;
                --muted:       #7A7A7A;
                --border:      #E5E5E5;
            }

            html, body { height: 100%; }

            body {
                font-family: 'DM Sans', sans-serif;
                background: var(--bg);
                color: var(--text);
                display: flex;
                height: 100vh;
                overflow: hidden;
                -webkit-font-smoothing: antialiased;
            }

            /* ─────────────────────────────────────
               SIDEBAR
            ───────────────────────────────────── */
            .sidebar {
                width: 230px;
                flex-shrink: 0;
                background: var(--black);
                display: flex;
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
            }

            .sidebar-logo {
                padding: 22px 20px 18px;
                border-bottom: 1px solid rgba(255,255,255,0.07);
            }

            .sidebar-logo .app-name {
                font-family: 'Bebas Neue', sans-serif;
                font-size: 26px;
                letter-spacing: 0.06em;
                color: var(--white);
                line-height: 1;
                margin-top: 6px;
            }

            .sidebar-logo .app-sub {
                font-size: 10px;
                font-weight: 500;
                color: var(--orange);
                letter-spacing: 0.12em;
                text-transform: uppercase;
                margin-top: 2px;
            }

            .sidebar-nav {
                flex: 1;
                overflow-y: auto;
                padding: 10px 10px;
                display: flex;
                flex-direction: column;
                gap: 1px;
            }

            .sidebar-nav::-webkit-scrollbar { width: 0; }

            .nav-label {
                font-size: 9.5px;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: rgba(255,255,255,0.22);
                padding: 12px 10px 4px;
                margin-top: 4px;
            }

            .nav-item {
                display: flex;
                align-items: center;
                gap: 11px;
                padding: 9px 12px;
                border-radius: 8px;
                font-size: 13.5px;
                font-weight: 500;
                color: rgba(255,255,255,0.55);
                text-decoration: none;
                transition: background 0.12s, color 0.12s;
                position: relative;
            }

            .nav-item:hover {
                background: rgba(255,255,255,0.07);
                color: rgba(255,255,255,0.9);
            }

            .nav-item.active {
                background: var(--orange);
                color: var(--white);
                font-weight: 600;
            }

            .nav-item .nav-icon {
                width: 16px;
                text-align: center;
                font-size: 12.5px;
                opacity: 0.8;
                flex-shrink: 0;
            }

            .nav-item.active .nav-icon { opacity: 1; }

            .bin-badge {
                margin-left: auto;
                background: #ef4444;
                color: white;
                font-size: 10px;
                font-weight: 700;
                padding: 1px 6px;
                border-radius: 999px;
            }

            /* Sidebar footer */
            .sidebar-footer {
                border-top: 1px solid rgba(255,255,255,0.07);
                padding: 14px 16px;
            }

            .sidebar-user {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: var(--orange);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-family: 'Bebas Neue', sans-serif;
                font-size: 15px;
                letter-spacing: 0.03em;
                flex-shrink: 0;
            }

            .user-name {
                font-size: 12.5px;
                font-weight: 600;
                color: rgba(255,255,255,0.9);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .user-role {
                font-size: 10px;
                font-weight: 600;
                color: var(--orange);
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .logout-btn {
                background: none;
                border: none;
                cursor: pointer;
                color: rgba(255,255,255,0.3);
                font-size: 13px;
                padding: 5px;
                border-radius: 6px;
                transition: all 0.15s;
                line-height: 1;
            }

            .logout-btn:hover {
                color: #ef4444;
                background: rgba(239,68,68,0.12);
            }

            /* ─────────────────────────────────────
               MAIN
            ───────────────────────────────────── */
            .main-wrap {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            /* ── Topbar ── */
            .topbar {
                height: 58px;
                background: var(--white);
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 28px;
                flex-shrink: 0;
            }

            .topbar-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            /* Status pill */
            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11.5px;
                font-weight: 600;
                color: #16a34a;
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                padding: 3px 10px 3px 7px;
                border-radius: 999px;
                letter-spacing: 0.01em;
            }

            @keyframes pulse-green {
                0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.5); }
                50%       { box-shadow: 0 0 0 4px rgba(22,163,74,0); }
            }
            .pulse-dot {
                width: 7px; height: 7px;
                background: #22c55e;
                border-radius: 50%;
                flex-shrink: 0;
                animation: pulse-green 2s ease-in-out infinite;
            }

            /* Clock */
            .clock-pill {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                font-size: 12px;
                font-weight: 500;
                color: var(--muted);
                background: var(--bg);
                border: 1px solid var(--border);
                padding: 4px 12px;
                border-radius: 999px;
            }

            .clock-pill .ct {
                font-family: 'DM Sans', monospace;
                font-weight: 700;
                color: var(--text);
                font-variant-numeric: tabular-nums;
            }

            .clock-pill .divider {
                color: #D0D5DD;
                font-weight: 300;
            }

            /* Page header pill */
            .page-header-pill {
                font-family: 'Bebas Neue', sans-serif;
                font-size: 15px;
                letter-spacing: 0.1em;
                color: var(--black);
                background: var(--bg);
                border: 1px solid var(--border);
                padding: 5px 16px;
                border-radius: 999px;
            }

            /* ── Page content ── */
            .page-body {
                flex: 1;
                overflow-y: auto;
                padding: 28px 30px;
            }

            /* ── Alert banners ── */
            .alert {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                border-radius: 10px;
                padding: 12px 16px;
                font-size: 13.5px;
                font-weight: 500;
                margin-bottom: 20px;
            }

            .alert-success {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #15803d;
            }

            .alert-error {
                background: #fff0f0;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }
        </style>
    </head>

    <body>

        {{-- ══════════ SIDEBAR ══════════ --}}
        <aside class="sidebar">

            <div class="sidebar-logo">
                <x-unicrop-logo variant="light" />
                <div class="app-name">Unicrop Print</div>
                <div class="app-sub">Print Management</div>
            </div>

            <nav class="sidebar-nav">

                <div class="nav-label">Main</div>

                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span> Dashboard
                </a>

                @if (auth()->user()->hasPermission('label_checker'))
                <a href="{{ route('label-checker.index') }}" class="nav-item {{ request()->routeIs('label-checker.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-tag"></i></span> Label Checker
                </a>
                @endif

                @if (auth()->user()->hasPermission('upload_design'))
                <div class="nav-label">Design</div>
                <a href="{{ route('uploader.create') }}" class="nav-item {{ request()->routeIs('uploader.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span> Upload Design
                </a>
                @endif

                @if (auth()->user()->hasPermission('print_station') || auth()->user()->hasPermission('cutting_station') || auth()->user()->hasPermission('dispatch'))
                <div class="nav-label">Production</div>
                @endif

                @if (auth()->user()->hasPermission('print_station'))
                <a href="{{ route('printer.index') }}" class="nav-item {{ request()->routeIs('printer.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-print"></i></span> Print Station
                </a>
                @endif

                @if (auth()->user()->hasPermission('cutting_station'))
                <a href="{{ route('cutting.index') }}" class="nav-item {{ request()->routeIs('cutting.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-scissors"></i></span> Cutting Station
                </a>
                @endif

                @if (auth()->user()->hasPermission('dispatch'))
                <a href="{{ route('dispatch.index') }}" class="nav-item {{ request()->routeIs('dispatch.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-truck"></i></span> Dispatch
                </a>
                @endif

                @if (auth()->user()->hasPermission('billing_logs') || auth()->user()->hasPermission('storage') || auth()->user()->hasPermission('bin') || auth()->user()->hasPermission('system_settings') || auth()->user()->isAdmin())
                <div class="nav-label">Management</div>
                @endif

                @if (auth()->user()->hasPermission('billing_logs'))
                <a href="{{ route('records.index') }}" class="nav-item {{ request()->routeIs('records.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span> Billing Logs
                </a>
                @endif

                @if (auth()->user()->hasPermission('system_settings'))
                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-gear"></i></span> System Settings
                </a>
                @endif

                @if (auth()->user()->hasPermission('storage'))
                <a href="{{ route('storage.index') }}" class="nav-item {{ request()->routeIs('storage.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-hard-drive"></i></span> Storage
                </a>
                @endif

                @if (auth()->user()->hasPermission('bin'))
                @php $binCount = \App\Models\PrintJob::onlyTrashed()->count(); @endphp
                <a href="{{ route('bin.index') }}" class="nav-item {{ request()->routeIs('bin.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-trash-can"></i></span> Bin
                    @if ($binCount > 0)
                        <span class="bin-badge">{{ $binCount }}</span>
                    @endif
                </a>
                @endif

                @if (auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-user-gear"></i></span> Manage Users
                </a>
                @endif

            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div style="flex:1; min-width:0;">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn" title="Logout">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        {{-- ══════════ MAIN ══════════ --}}
        <div class="main-wrap">

            <div class="topbar">
                <div class="topbar-left">
                    <div class="status-pill">
                        <div class="pulse-dot"></div>
                        System Online
                    </div>

                    <div class="clock-pill"
                         x-data="{
                             t:'', d:'',
                             tick(){
                                 const n=new Date(), ist=new Date(n.toLocaleString('en-US',{timeZone:'Asia/Kolkata'}));
                                 const h=ist.getHours(), m=ist.getMinutes(), s=ist.getSeconds();
                                 const ap=h>=12?'PM':'AM', hh=h%12||12;
                                 this.t=String(hh).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
                                 const dy=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],mo=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                 this.d=dy[ist.getDay()]+' '+String(ist.getDate()).padStart(2,'0')+' '+mo[ist.getMonth()]+' '+ist.getFullYear();
                             }
                         }"
                         x-init="tick();setInterval(()=>tick(),1000)">
                        <i class="fa-regular fa-clock" style="color:var(--orange);font-size:11px;"></i>
                        <span class="ct" x-text="t"></span>
                        <span class="divider">|</span>
                        <span x-text="d"></span>
                    </div>
                </div>

                @isset($header)
                    <div class="page-header-pill">{{ $header }}</div>
                @endisset
            </div>

            <div class="page-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check mt-0.5"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>

        </div>

    </body>
</html>
