<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes blob-move {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -60px) scale(1.15); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }
        .blob {
            animation: blob-move 14s ease-in-out infinite;
            filter: blur(60px);
        }
        .blob-2 { animation-delay: -4s; }
        .blob-3 { animation-delay: -8s; }

        .login-bg {
            background: linear-gradient(135deg, #ff0080 0%, #7928ca 35%, #2afadf 70%, #ff8a00 100%);
            background-size: 300% 300%;
            animation: gradient-shift 12s ease infinite;
        }
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="login-bg relative min-h-screen flex items-center justify-center overflow-hidden px-4">

        <!-- Decorative animated blobs -->
        <div class="blob absolute top-[-10%] left-[-10%] w-96 h-96 rounded-full bg-fuchsia-400 opacity-50"></div>
        <div class="blob blob-2 absolute bottom-[-15%] right-[-10%] w-[28rem] h-[28rem] rounded-full bg-cyan-300 opacity-50"></div>
        <div class="blob blob-3 absolute top-[20%] right-[15%] w-72 h-72 rounded-full bg-amber-300 opacity-40"></div>

        <div class="relative z-10 w-full sm:max-w-md">

            <div class="flex justify-center mb-6">
                <a href="/" class="inline-flex items-center justify-center w-20 h-20 rounded-2xl glass-card shadow-xl">
                    <x-application-logo class="w-12 h-12 fill-current text-white" />
                </a>
            </div>

            <div class="glass-card rounded-3xl shadow-2xl px-8 py-10">
                <h1 class="text-3xl font-extrabold text-white text-center tracking-tight drop-shadow-sm">
                    Welcome Back, Creator
                </h1>
                <p class="text-white/70 text-center mt-2 mb-8 text-sm">
                    Sign in and bring your designs to life
                </p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-white/90 mb-1">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="block w-full rounded-xl border-0 bg-white/20 text-white placeholder-white/60 px-4 py-3 shadow-inner focus:bg-white/30 focus:ring-2 focus:ring-fuchsia-300 transition" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-white/90 mb-1">{{ __('Password') }}</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="block w-full rounded-xl border-0 bg-white/20 text-white placeholder-white/60 px-4 py-3 shadow-inner focus:bg-white/30 focus:ring-2 focus:ring-fuchsia-300 transition" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="rounded border-white/40 bg-white/20 text-fuchsia-500 focus:ring-fuchsia-300">
                            <span class="ms-2 text-sm text-white/80">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-white/80 hover:text-white underline-offset-2 hover:underline transition" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl py-3 font-bold text-white bg-gradient-to-r from-fuchsia-500 via-purple-500 to-cyan-400 shadow-lg hover:shadow-fuchsia-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                        {{ __('Log in') }}
                    </button>
                </form>
            </div>

            <p class="text-center text-white/60 text-xs mt-6">
                &copy; {{ date('Y') }} {{ config('app.name', 'Unicrop Print') }}. Made for designers.
            </p>
        </div>
    </div>
</body>

</html>
