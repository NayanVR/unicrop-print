<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Unicrop Print') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --brand: #00b96b;
                --brand-dark: #007a45;
                --brand-light: #e8faf3;
                --brand-mid: #c6f0dc;
            }

            * { box-sizing: border-box; }

            body {
                background: #f0f2f5;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }

            /* ── Sidebar ── */
            .sidebar {
                width: 240px;
                background: #ffffff;
                border-right: 1px solid #e8eaed;
                display: flex;
                flex-direction: column;
                height: 100vh;
                position: fixed;
                left: 0; top: 0; bottom: 0;
                z-index: 50;
            }

            .sidebar-logo {
                padding: 20px 20px 16px;
                border-bottom: 1px solid #f0f2f5;
            }

            .sidebar-nav {
                flex: 1;
                padding: 12px 10px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .nav-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px;
                border-radius: 10px;
                font-size: 13.5px;
                font-weight: 500;
                color: #5f6368;
                text-decoration: none;
                transition: all 0.15s ease;
                position: relative;
            }

            .nav-item:hover {
                background: #f5f7fa;
                color: #1a1a2e;
            }

            .nav-item.active {
                background: var(--brand-light);
                color: var(--brand-dark);
                font-weight: 600;
            }

            .nav-item .icon {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                font-size: 13px;
                background: #f0f2f5;
                color: #8a8fa8;
                transition: all 0.15s ease;
                flex-shrink: 0;
            }

            .nav-item:hover .icon {
                background: #e8eaed;
                color: #444;
            }

            .nav-item.active .icon {
                background: var(--brand);
                color: white;
                box-shadow: 0 3px 8px rgba(0,185,107,0.35);
            }

            .nav-section-label {
                font-size: 10.5px;
                font-weight: 700;
                letter-spacing: 0.07em;
                color: #b0b7c3;
                text-transform: uppercase;
                padding: 8px 12px 4px;
                margin-top: 6px;
            }

            .sidebar-footer {
                padding: 14px 16px;
                border-top: 1px solid #f0f2f5;
            }

            .sidebar-user {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .user-avatar {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--brand), #00d4ff);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 13px;
                font-weight: 700;
                flex-shrink: 0;
            }

            /* ── Main area ── */
            .main-area {
                margin-left: 240px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* ── Topbar ── */
            .topbar {
                height: 62px;
                background: rgba(255,255,255,0.92);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(0,0,0,0.06);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 28px;
                position: sticky;
                top: 0;
                z-index: 40;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            }

            .topbar-left {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .status-pill {
                display: flex;
                align-items: center;
                gap: 6px;
                background: #e8faf3;
                border: 1px solid #b7edcf;
                border-radius: 999px;
                padding: 4px 12px 4px 8px;
                font-size: 12px;
                font-weight: 600;
                color: #007a45;
            }

            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 0 0 rgba(0,185,107,0.5); }
                50% { box-shadow: 0 0 0 5px rgba(0,185,107,0); }
            }
            .pulse-dot { animation: pulse-glow 2s ease-in-out infinite; }

            .topbar-clock {
                display: flex;
                align-items: center;
                gap: 8px;
                background: #f7f8fa;
                border: 1px solid #e8eaed;
                border-radius: 999px;
                padding: 4px 14px;
                font-size: 12.5px;
                color: #5f6368;
            }

            .clock-time {
                font-weight: 700;
                color: #1a1a2e;
                font-variant-numeric: tabular-nums;
            }

            /* ── Page content ── */
            .page-content {
                flex: 1;
                padding: 28px 32px;
            }

            /* ── Cards / Panels ── */
            .card {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e8eaed;
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            }

            /* ── Alerts ── */
            .alert-success {
                background: #e8faf3;
                border: 1px solid #b7edcf;
                color: #007a45;
                border-radius: 10px;
                padding: 12px 16px;
                font-size: 13.5px;
                font-weight: 500;
                margin-bottom: 18px;
            }

            .alert-error {
                background: #fff0f0;
                border: 1px solid #fecaca;
                color: #b91c1c;
                border-radius: 10px;
                padding: 12px 16px;
                font-size: 13.5px;
                margin-bottom: 18px;
            }

            /* ── Bin badge ── */
            .bin-badge {
                margin-left: auto;
                background: #ef4444;
                color: white;
                font-size: 10px;
                font-weight: 700;
                padding: 1px 6px;
                border-radius: 999px;
                line-height: 16px;
            }
        </style>
    </head>
    <body class="antialiased">

        {{-- ══ SIDEBAR ══ --}}
        <aside class="sidebar">
            <div class="sidebar-logo">
                <x-unicrop-logo variant="dark" />
            </div>

            <nav class="sidebar-nav">

                <div class="nav-section-label">Main</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-chart-pie"></i></span>
                    Dashboard
                </a>

                @if (auth()->user()->hasPermission('label_checker'))
                <a href="{{ route('label-checker.index') }}"
                   class="nav-item {{ request()->routeIs('label-checker.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-tag"></i></span>
                    Label Checker
                </a>
                @endif

                @if (auth()->user()->hasPermission('upload_design'))
                <div class="nav-section-label">Design</div>
                <a href="{{ route('uploader.create') }}"
                   class="nav-item {{ request()->routeIs('uploader.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    Upload Design
                </a>
                @endif

                @if (auth()->user()->hasPermission('print_station') || auth()->user()->hasPermission('cutting_station') || auth()->user()->hasPermission('dispatch'))
                <div class="nav-section-label">Production</div>
                @endif

                @if (auth()->user()->hasPermission('print_station'))
                <a href="{{ route('printer.index') }}"
                   class="nav-item {{ request()->routeIs('printer.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-print"></i></span>
                    Print Station
                </a>
                @endif

                @if (auth()->user()->hasPermission('cutting_station'))
                <a href="{{ route('cutting.index') }}"
                   class="nav-item {{ request()->routeIs('cutting.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-scissors"></i></span>
                    Cutting Station
                </a>
                @endif

                @if (auth()->user()->hasPermission('dispatch'))
                <a href="{{ route('dispatch.index') }}"
                   class="nav-item {{ request()->routeIs('dispatch.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-truck"></i></span>
                    Dispatch
                </a>
                @endif

                @if (auth()->user()->hasPermission('billing_logs') || auth()->user()->hasPermission('storage') || auth()->user()->hasPermission('bin') || auth()->user()->hasPermission('system_settings') || auth()->user()->isAdmin())
                <div class="nav-section-label">Management</div>
                @endif

                @if (auth()->user()->hasPermission('billing_logs'))
                <a href="{{ route('records.index') }}"
                   class="nav-item {{ request()->routeIs('records.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-receipt"></i></span>
                    Billing Logs
                </a>
                @endif

                @if (auth()->user()->hasPermission('system_settings'))
                <a href="{{ route('settings.index') }}"
                   class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-gear"></i></span>
                    System Settings
                </a>
                @endif

                @if (auth()->user()->hasPermission('storage'))
                <a href="{{ route('storage.index') }}"
                   class="nav-item {{ request()->routeIs('storage.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-hard-drive"></i></span>
                    Storage
                </a>
                @endif

                @if (auth()->user()->hasPermission('bin'))
                <a href="{{ route('bin.index') }}"
                   class="nav-item {{ request()->routeIs('bin.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-trash-can"></i></span>
                    Bin
                    @php $binCount = \App\Models\PrintJob::onlyTrashed()->count(); @endphp
                    @if ($binCount > 0)
                        <span class="bin-badge">{{ $binCount }}</span>
                    @endif
                </a>
                @endif

                @if (auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                   class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fa-solid fa-user-gear"></i></span>
                    Manage Users
                </a>
                @endif

            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:600; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px; color:#00b96b; font-weight:600; text-transform:capitalize;">{{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                            style="background:none; border:none; cursor:pointer; color:#b0b7c3; font-size:14px; padding:4px; border-radius:6px; transition:all 0.15s;"
                            onmouseover="this.style.color='#ef4444'; this.style.background='#fff0f0';"
                            onmouseout="this.style.color='#b0b7c3'; this.style.background='none';">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ══ MAIN ══ --}}
        <div class="main-area">

            {{-- Topbar --}}
            <div class="topbar">
                <div class="topbar-left">
                    <div class="status-pill">
                        <div class="pulse-dot w-2 h-2 rounded-full" style="background:#00b96b; width:8px; height:8px; flex-shrink:0;"></div>
                        System Online
                    </div>

                    <div class="topbar-clock"
                         x-data="{
                             time: '',
                             date: '',
                             tick() {
                                 const now = new Date();
                                 const ist = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Kolkata' }));
                                 const h = ist.getHours(), m = ist.getMinutes(), s = ist.getSeconds();
                                 const ampm = h >= 12 ? 'PM' : 'AM';
                                 const hh = h % 12 || 12;
                                 this.time = String(hh).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
                                 const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                                 const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                 this.date = days[ist.getDay()] + ' ' + String(ist.getDate()).padStart(2,'0') + ' ' + months[ist.getMonth()] + ' ' + ist.getFullYear();
                             }
                         }"
                         x-init="tick(); setInterval(() => tick(), 1000)">
                        <i class="fa-regular fa-clock" style="color:#00b96b; font-size:12px;"></i>
                        <span class="clock-time" x-text="time"></span>
                        <span style="color:#d0d5dd;">·</span>
                        <span x-text="date"></span>
                    </div>
                </div>

                @isset($header)
                    <div style="font-size:13px; font-weight:600; color:#5f6368; background:#f7f8fa; border:1px solid #e8eaed; padding:5px 14px; border-radius:999px;">
                        {{ $header }}
                    </div>
                @endisset
            </div>

            {{-- Page Content --}}
            <main class="page-content">
                @if (session('status'))
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert-error">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

    </body>
</html>
