<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Unicrop Print') }} — Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bebas-neue:400|dm-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; background: #F0F2F5; }

        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #fff;
        }

        .login-right {
            width: 480px;
            background: #111;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            position: relative;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .login-right { display: none; }
        }

        .login-right .bg-text {
            position: absolute;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 220px;
            color: rgba(255,255,255,0.03);
            line-height: 1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            white-space: nowrap;
            letter-spacing: 0.05em;
            pointer-events: none;
            user-select: none;
        }

        .form-wrap {
            width: 100%;
            max-width: 380px;
        }

        .form-wrap .logo { margin-bottom: 32px; }

        .form-wrap h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 52px;
            letter-spacing: 0.05em;
            color: #111;
            line-height: 1;
            margin-bottom: 6px;
        }

        .form-wrap p {
            font-size: 13.5px;
            color: #717171;
            margin-bottom: 32px;
        }

        .field { margin-bottom: 14px; }

        .field input {
            display: block;
            width: 100%;
            padding: 12px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            border: 1.5px solid #E5E5E5;
            border-radius: 10px;
            background: #FAFAF8;
            color: #111;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .field input:focus {
            border-color: #F05A28;
            box-shadow: 0 0 0 3px rgba(240,90,40,0.1);
            background: #fff;
        }

        .field input::placeholder { color: #B0B0B0; }

        .row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }

        .remember { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #555; cursor: pointer; }
        .remember input { accent-color: #F05A28; width: 15px; height: 15px; }

        .forgot { font-size: 13px; font-weight: 600; color: #F05A28; text-decoration: none; }
        .forgot:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #111;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: background 0.15s;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover { background: #F05A28; }

        .error-msg {
            background: #FFF0F0;
            border: 1.5px solid #FECACA;
            color: #B91C1C;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

    {{-- Left: form --}}
    <div class="login-left">
        <div class="form-wrap">
            <div class="logo">
                <x-unicrop-logo variant="dark" />
            </div>

            <h1>Welcome<br>Back.</h1>
            <p>Log in to your Unicrop Print account.</p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if ($errors->any())
                <div class="error-msg">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required autofocus>
                </div>
                <div class="field">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="row">
                    <label class="remember">
                        <input type="checkbox" name="remember" checked> Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                    @endif
                </div>
                <button type="submit" class="btn-login">Log In</button>
            </form>
        </div>
    </div>

    {{-- Right: black panel --}}
    <div class="login-right">
        <div class="bg-text">PRINT</div>
        <div style="position:relative;z-index:1;text-align:center;">
            <div style="font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:0.18em;color:rgba(255,255,255,0.35);margin-bottom:20px;">UNICROP PRINT</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:62px;letter-spacing:0.05em;color:#fff;line-height:1;margin-bottom:10px;">One Platform.<br>All Prints.</div>
            <p style="font-size:13.5px;color:rgba(255,255,255,0.45);line-height:1.7;max-width:280px;margin:0 auto;">Upload, print, cut, dispatch — manage the complete print workflow from a single screen.</p>
            <div style="display:inline-flex;align-items:center;gap:7px;margin-top:28px;background:#F05A28;border-radius:999px;padding:8px 20px;">
                <div style="width:7px;height:7px;background:#fff;border-radius:50%;"></div>
                <span style="font-size:12px;font-weight:700;color:#fff;letter-spacing:0.06em;">SYSTEM ONLINE</span>
            </div>
        </div>
    </div>

</body>
</html>
