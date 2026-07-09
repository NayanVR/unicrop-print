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
            .sidebar-energy {
                background: linear-gradient(165deg, #0f4023 0%, #1b5e2e 55%, #3f9b3f 100%);
            }
            .nav-active {
                background: linear-gradient(90deg, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
                box-shadow: inset 3px 0 0 0 #bff047;
            }
            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 0 0 rgba(191, 240, 71, 0.5); }
                50% { box-shadow: 0 0 0 6px rgba(191, 240, 71, 0); }
            }
            .pulse-dot { animation: pulse-glow 2s ease-in-out infinite; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f4f9f0] text-slate-800 h-screen overflow-hidden flex">

        <div class="sidebar-energy w-64 flex flex-col p-4 justify-between overflow-y-auto">
            <div>
                <div class="mb-8 pl-2">
                    <x-unicrop-logo variant="light" />
                </div>
                <nav class="flex flex-col gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-4"></i> Dashboard
                    </a>
                    @if (auth()->user()->isAdmin())
                    <a href="{{ route('label-checker.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('label-checker.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-tag w-4"></i> Label Checker
                    </a>
                    @endif
                    @if (auth()->user()->hasPermission('upload_design'))
                        <a href="{{ route('uploader.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('uploader.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-cloud-arrow-up w-4"></i> Upload Design
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('print_station'))
                        <a href="{{ route('printer.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('printer.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-print w-4"></i> Print Station
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('cutting_station'))
                        <a href="{{ route('cutting.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('cutting.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-scissors w-4"></i> Cutting Station
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('dispatch'))
                        <a href="{{ route('dispatch.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('dispatch.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-truck w-4"></i> Dispatch
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('billing_logs'))
                        <a href="{{ route('records.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('records.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-receipt w-4"></i> Billing Logs
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('system_settings'))
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-gear w-4"></i> System Settings
                        </a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('users.*') ? 'nav-active text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid fa-user-gear w-4"></i> Manage Users
                        </a>
                    @endif
                </nav>
            </div>

            <div class="border-t border-white/15 pt-4 flex items-center justify-between text-white mt-5">
                <div>
                    <span class="font-semibold text-sm block">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-[#bff047] capitalize font-medium">{{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white/70 hover:text-white transition" title="Logout">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex-grow flex flex-col overflow-hidden">
            <div class="h-[70px] bg-white border-b border-[#1b5e2e]/10 flex items-center justify-between px-8 shrink-0">
                <div class="flex items-center gap-2 text-sm font-semibold text-[#1b5e2e]">
                    <div class="pulse-dot w-2 h-2 bg-[#3f9b3f] rounded-full"></div>
                    <span>System Online</span>
                </div>
                @isset($header)
                    <div class="text-sm text-slate-500">{{ $header }}</div>
                @endisset
            </div>

            <main class="p-8 overflow-y-auto flex-grow">
                @if (session('status'))
                    <div class="mb-5 bg-[#eaf7e1] border border-[#3f9b3f]/30 text-[#1b5e2e] px-4 py-3 rounded-lg text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
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
