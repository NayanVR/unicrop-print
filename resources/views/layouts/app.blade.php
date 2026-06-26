<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Unicrop Print') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800 h-screen overflow-hidden flex">

        <div class="w-64 bg-slate-900 flex flex-col p-4 justify-between overflow-y-auto">
            <div>
                <div class="flex items-center gap-3 text-white text-xl font-bold mb-8 pl-2">
                    <i class="fa-solid fa-circle-nodes"></i>
                    <span>Unicrop Print</span>
                </div>
                <nav class="flex flex-col gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-4"></i> Dashboard
                    </a>
                    @if (auth()->user()->isAdmin() || auth()->user()->isUploader())
                        <a href="{{ route('uploader.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('uploader.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-cloud-arrow-up w-4"></i> Upload Design
                        </a>
                    @endif
                    @if (auth()->user()->isAdmin() || auth()->user()->isPrinter())
                        <a href="{{ route('printer.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('printer.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-print w-4"></i> Print Station
                        </a>
                        <a href="{{ route('cutting.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('cutting.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-scissors w-4"></i> Cutting Station
                        </a>
                    @endif
                    <a href="{{ route('records.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('records.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-receipt w-4"></i> Billing Logs
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-gear w-4"></i> System Settings
                        </a>
                    @endif
                </nav>
            </div>

            <div class="border-t border-slate-800 pt-4 flex items-center justify-between text-white mt-5">
                <div>
                    <span class="font-semibold text-sm block">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-400 capitalize">{{ auth()->user()->role->value }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-400" title="Logout">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex-grow flex flex-col overflow-hidden">
            <div class="h-[70px] bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span>System Online</span>
                </div>
                @isset($header)
                    <div class="text-sm text-slate-500">{{ $header }}</div>
                @endisset
            </div>

            <main class="p-8 overflow-y-auto flex-grow">
                @if (session('status'))
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
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
