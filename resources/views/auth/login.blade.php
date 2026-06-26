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
        .leaf-pattern {
            background-color: #0f4023;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(111, 191, 63, 0.35) 0, transparent 18%),
                radial-gradient(circle at 80% 20%, rgba(111, 191, 63, 0.3) 0, transparent 20%),
                radial-gradient(circle at 60% 70%, rgba(63, 155, 63, 0.4) 0, transparent 22%),
                radial-gradient(circle at 30% 85%, rgba(111, 191, 63, 0.3) 0, transparent 20%),
                linear-gradient(160deg, #0f4023 0%, #1b5e2e 45%, #3f9b3f 100%);
        }
        .leaf-shape {
            position: absolute;
            border-radius: 0% 60% 0% 60%;
            background: rgba(255, 255, 255, 0.08);
        }
        @keyframes sway {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(6deg); }
        }
        .sway { animation: sway 6s ease-in-out infinite; }
        .sway-slow { animation: sway 9s ease-in-out infinite; animation-delay: -3s; }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center bg-[#f4f1ea] p-4">
        <div class="w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden grid md:grid-cols-2 bg-white border border-[#0f4023]/10">

            <!-- Left: form -->
            <div class="px-8 sm:px-12 py-12 flex flex-col justify-center">
                <x-unicrop-logo class="mb-10" />

                <h1 class="text-3xl font-extrabold text-[#1b5e2e] tracking-tight">
                    {{ __('Welcome Back!') }}
                </h1>
                <p class="text-gray-500 mt-1 mb-8">{{ __('Please log in to your account.') }}</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="{{ __('Email Address') }}"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30 transition" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="{{ __('Password') }}"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30 transition" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me / Forgot -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="rounded border-gray-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-rose-500 hover:text-rose-600 transition" href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="submit"
                            class="flex-1 rounded-lg py-3 font-semibold text-white bg-[#1b5e2e] hover:bg-[#164d26] shadow-md transition">
                            {{ __('Login') }}
                        </button>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="flex-1 text-center rounded-lg py-3 font-semibold text-[#1b5e2e] border border-[#1b5e2e] hover:bg-[#1b5e2e]/5 transition">
                                {{ __('Create account') }}
                            </a>
                        @endif
                    </div>
                </form>

                <p class="text-xs text-gray-400 mt-10">
                    {{ __('By signing up you agree to our terms and that you have read our data policy.') }}
                </p>
            </div>

            <!-- Right: green nature panel -->
            <div class="leaf-pattern relative hidden md:block overflow-hidden">
                <div class="leaf-shape sway w-40 h-40 top-10 left-10"></div>
                <div class="leaf-shape sway-slow w-56 h-56 bottom-16 -right-10"></div>
                <div class="leaf-shape sway w-32 h-32 bottom-1/3 left-1/4"></div>
                <div class="leaf-shape sway-slow w-24 h-24 top-1/3 right-10"></div>

                <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-10">
                    <svg viewBox="0 0 100 100" class="w-20 h-20 mb-6">
                        <path d="M50 90C50 90 20 65 20 40C20 22 33 10 50 10C67 10 80 22 80 40C80 65 50 90 50 90Z" fill="rgba(255,255,255,0.15)" stroke="white" stroke-width="2"/>
                        <path d="M50 80V30M50 30C50 30 35 35 35 50M50 30C50 30 65 35 65 50" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </svg>
                    <h2 class="text-white text-2xl font-bold">{{ __('Grow with Unicrop Biochem') }}</h2>
                    <p class="text-white/70 mt-2 max-w-xs">
                        {{ __('Sustainable bio-solutions for healthier crops and a greener tomorrow.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
