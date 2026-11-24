<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <title>Sign in · Pharmacy POS</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Segoe UI, Tahoma, Arial, sans-serif;
            color: #0f172a;
            background:
                linear-gradient(165deg, rgba(6, 32, 38, 0.78) 0%, rgba(15, 118, 110, 0.62) 48%, rgba(236, 254, 255, 0.82) 100%),
                url("{{ asset('img/pharmacy-login-bg.jpg') }}") center / cover no-repeat fixed;
        }
        .box {
            width: min(400px, 92vw);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 14px;
            padding: 22px 20px 18px;
            box-shadow: 0 16px 40px rgba(0,0,0,.28);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }
        h1 { margin: 0 0 4px; font-size: 1.25rem; }
        p { margin: 0 0 16px; color: #64748b; font-size: .95rem; }
        label { display: block; font-size: .85rem; font-weight: 600; margin: 0 0 6px; }
        input[type="text"], input[type="password"] {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            margin-bottom: 12px;
        }
        input:focus { outline: 2px solid #14b8a6; border-color: #14b8a6; }
        button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background: #0f766e;
            cursor: pointer;
        }
        button:disabled {
            opacity: .7;
            cursor: wait;
        }
        .err {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: .9rem;
        }
        .quick {
            margin-top: 14px;
            display: grid;
            gap: 8px;
        }
        .quick-label {
            font-size: .8rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }
        .btn-quick {
            background: #0f172a;
            font-size: .92rem;
            font-weight: 600;
        }
        .btn-quick.admin {
            background: #115e59;
        }
        .btn-quick.shahid {
            background: #1e293b;
        }
        .show { font-size: .85rem; color: #475569; margin: -4px 0 12px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Coditium Pharmacy POS</h1>
        <p>Sign in to continue</p>

        @if($errors->any())
            <div class="err" role="alert">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form id="loginForm" method="POST" action="{{ url('/login') }}" autocomplete="off">
            @csrf
            <input type="text" name="username" style="display:none" tabindex="-1" autocomplete="off">
            <input type="password" name="password" style="display:none" tabindex="-1" autocomplete="off">

            <label for="user_name">Username</label>
            <input id="user_name" name="user_name" type="text" value="{{ old('user_name') }}" required autofocus autocomplete="off" spellcheck="false">

            <label for="login_pass">Password</label>
            <input id="login_pass" name="login_pass" type="password" required autocomplete="off">
            <label class="show"><input type="checkbox" id="show_pass"> Show password</label>

            <button type="submit" id="loginBtn">Login</button>
        </form>

        <div class="quick">
            <p class="quick-label">Quick sign-in (fills username &amp; password, then logs in)</p>
            <button type="button" class="btn-quick admin" id="fillAdmin" data-user="admin">Login as Admin</button>
            <button type="button" class="btn-quick shahid" id="fillShahid" data-user="shahid">Login as Shahid</button>
        </div>
    </div>
    <script>
        (function () {
            var form = document.getElementById('loginForm');
            var userInput = document.getElementById('user_name');
            var passInput = document.getElementById('login_pass');
            var loginBtn = document.getElementById('loginBtn');

            // Kept in JS only — never rendered as visible page text.
            var accounts = {
                admin: { user: 'admin', pass: 'admin123' },
                shahid: { user: 'shahid', pass: 'ShahidGhauri786' }
            };

            document.getElementById('show_pass').addEventListener('change', function () {
                passInput.type = this.checked ? 'text' : 'password';
            });

            function fillAndLogin(key) {
                var acct = accounts[key];
                if (!acct) return;
                userInput.value = acct.user;
                passInput.value = acct.pass;
                passInput.type = 'password';
                document.getElementById('show_pass').checked = false;
                loginBtn.disabled = true;
                loginBtn.textContent = 'Signing in…';
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }

            document.getElementById('fillAdmin').addEventListener('click', function () {
                fillAndLogin('admin');
            });
            document.getElementById('fillShahid').addEventListener('click', function () {
                fillAndLogin('shahid');
            });
        })();
    </script>
</body>
</html>
