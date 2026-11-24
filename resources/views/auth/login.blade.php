<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Coditium Pharmacy POS | Medicine Billing, Stock &amp; Pharmacy Store Software</title>
    <meta name="description" content="Pharmacy POS for medical stores—fast medicine billing, expiry-aware stock, purchases, and sales reports built for pharmacies in Pakistan.">
    <meta property="og:title" content="Coditium Pharmacy POS | Medicine Billing, Stock &amp; Pharmacy Store Software">
    <meta property="og:description" content="Pharmacy POS for medical stores—fast medicine billing, expiry-aware stock, purchases, and sales reports built for pharmacies in Pakistan.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Coditium Solutions">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Coditium Solutions',
        'url' => 'https://www.coditium.com',
        'telephone' => '+923335191392',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --navy: #0a2a32;
            --navy-deep: #062026;
            --royal: #0f766e;
            --cyan: #0d9488;
            --cyan-soft: #5eead4;
            --ink: #0f172a;
            --muted: #64748b;
            --card: #ffffff;
            --line: rgba(148, 163, 184, 0.28);
            --surface: #f0fdfa;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --radius: 1.15rem;
            --font-display: "Outfit", system-ui, sans-serif;
            --font-body: "Source Sans 3", system-ui, sans-serif;
            --wa: #25d366;
            --mustard: #14b8a6;
            --mustard-deep: #0f766e;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: var(--font-body);
            color: var(--ink);
            background:
                linear-gradient(180deg, rgba(6, 32, 38, 0.72) 0%, rgba(10, 42, 50, 0.55) 22%, rgba(240, 253, 250, 0.92) 58%, #ffffff 100%),
                url("{{ asset('img/pharmacy-login-bg.jpg') }}") center top / cover no-repeat fixed;
            overflow-x: hidden;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        .nav-link:focus-visible,
        .btn:focus-visible {
            outline: 3px solid rgba(20, 184, 166, 0.65);
            outline-offset: 2px;
        }

        .site-nav {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: var(--navy-deep);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 28px rgba(7, 21, 40, 0.35);
            padding-top: 0.1rem;
            padding-bottom: 0.1rem;
            --bs-navbar-padding-y: 0.25rem;
            min-height: 0;
        }

        .site-nav .navbar-brand {
            font-family: var(--font-display);
            font-weight: 400;
            color: #fff;
            letter-spacing: -0.02em;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.88rem;
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
        }

        .site-nav .navbar-brand:hover { color: #fff; }

        .brand-mark {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.5rem;
            display: grid;
            place-items: center;
            color: #062026;
            background: linear-gradient(135deg, var(--mustard) 0%, #5eead4 100%);
            font-size: 0.9rem;
            flex: 0 0 auto;
        }

        .site-nav .nav-link {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            font-size: 0.9rem;
            padding: 0.28rem 0.55rem !important;
        }

        .site-nav .nav-link:hover { color: var(--mustard); }

        .site-nav .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.45);
            padding: 0.25rem 0.45rem;
        }

        .site-nav .navbar-toggler-icon {
            width: 1.1rem;
            height: 1.1rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.92)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .btn-nav-login {
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: transparent;
            color: #fff;
            font-weight: 400;
            border-radius: 0.6rem;
            padding: 0.28rem 0.75rem;
            font-size: 0.88rem;
            line-height: 1.2;
        }

        .btn-nav-login:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.55);
        }

        .btn-demo {
            background: linear-gradient(135deg, var(--mustard) 0%, #2dd4bf 100%);
            color: var(--navy-deep) !important;
            font-weight: 400;
            border: 0;
            border-radius: 0.6rem;
            padding: 0.3rem 0.85rem;
            font-size: 0.88rem;
            line-height: 1.2;
            box-shadow: 0 6px 14px rgba(13, 148, 136, 0.28);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-demo:hover {
            filter: saturate(1.08) brightness(1.03);
            color: var(--navy-deep) !important;
            transform: translateY(-1px);
        }

        .btn-outline-soft {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: var(--navy);
            font-weight: 700;
            border-radius: 0.85rem;
            padding: 0.7rem 1.15rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-outline-soft:hover {
            background: var(--surface);
            color: var(--royal);
        }

        .hero {
            position: relative;
            padding: clamp(2rem, 5vw, 4rem) 0 clamp(3rem, 6vw, 5rem);
            isolation: isolate;
            overflow: hidden;
            min-height: min(72vh, 640px);
            display: flex;
            align-items: center;
            background:
                linear-gradient(115deg, rgba(6, 32, 38, 0.78) 0%, rgba(10, 42, 50, 0.58) 42%, rgba(15, 118, 110, 0.42) 100%),
                url("{{ asset('img/pharmacy-login-bg.jpg') }}") center / cover no-repeat;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(800px 420px at 18% 12%, rgba(94, 234, 212, 0.18), transparent 62%),
                linear-gradient(180deg, rgba(6, 32, 38, 0.18) 0%, rgba(6, 32, 38, 0.08) 100%);
            pointer-events: none;
        }

        .hero > .container {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .hero-copy {
            position: relative;
            max-width: 36rem;
            padding: clamp(1.15rem, 2.4vw, 1.75rem) clamp(1.15rem, 2.6vw, 1.9rem);
            background: rgba(255, 255, 255, 0.88);
            border-radius: 0.35rem;
            box-shadow: 0 14px 36px rgba(6, 32, 38, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(6px);
        }

        .hero-copy h1 {
            font-family: var(--font-display);
            font-size: clamp(1.45rem, 2.6vw, 1.95rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.22;
            color: #04101f;
            margin: 0 0 0.75rem;
            max-width: 22ch;
            text-wrap: balance;
            text-shadow: none;
        }

        .hero-copy .lead {
            font-size: clamp(0.95rem, 1.25vw, 1.05rem);
            color: #0f172a;
            max-width: 34rem;
            margin-bottom: 1.25rem;
            line-height: 1.55;
            font-weight: 500;
            text-shadow: none;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.15rem;
        }

        .trust-line {
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .login-panel {
            display: flex;
            align-items: stretch;
            justify-content: center;
        }

        .login-card {
            width: min(420px, 100%);
            background: rgba(255, 255, 255, 0.94);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 18px 44px rgba(6, 32, 38, 0.22);
            padding: clamp(1.4rem, 3vw, 2.15rem);
            backdrop-filter: blur(8px);
        }

        .login-logo {
            width: 3.4rem;
            height: 3.4rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            margin: 0 auto 1rem;
            color: #fff;
            font-size: 1.45rem;
            background: linear-gradient(135deg, var(--royal) 0%, var(--cyan) 100%);
            box-shadow: 0 12px 24px rgba(13, 148, 136, 0.28);
        }

        .login-card .subtitle {
            text-align: center;
            color: var(--muted);
            margin-bottom: 1.35rem;
            font-size: 0.98rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .input-shell { position: relative; }

        .input-shell > i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1rem;
            pointer-events: none;
        }

        .input-shell .form-control {
            height: 3rem;
            padding-left: 2.55rem;
            border-radius: 0.85rem;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            font-size: 0.98rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .input-shell .form-control:focus {
            background: #fff;
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.25rem rgba(34, 211, 238, 0.18);
        }

        .input-shell.password-shell .form-control { padding-right: 2.75rem; }

        .toggle-password {
            position: absolute;
            right: 0.35rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.65rem;
            color: #64748b;
            display: grid;
            place-items: center;
        }

        .toggle-password:hover {
            background: #eef2ff;
            color: var(--royal);
        }

        .login-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin: 0.85rem 0 1.25rem;
            font-size: 0.9rem;
        }

        .login-meta .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            border-radius: 0.3rem;
            border-color: #cbd5e1;
        }

        .login-meta .form-check-input:checked {
            background-color: var(--royal);
            border-color: var(--royal);
        }

        .login-meta .form-check-label {
            color: #475569;
            font-weight: 500;
        }

        .forgot-link {
            color: var(--royal);
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .forgot-link:hover {
            color: var(--cyan);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 3.05rem;
            border: 0;
            border-radius: 0.9rem;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.02rem;
            letter-spacing: 0.01em;
            color: #fff;
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            box-shadow: 0 12px 24px rgba(13, 148, 136, 0.28);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .btn-login:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(13, 148, 136, 0.36);
            filter: saturate(1.08);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 8px 16px rgba(13, 148, 136, 0.25);
        }

        .login-alert {
            border: 0;
            border-radius: 0.85rem;
            background: #fef2f2;
            color: #991b1b;
            font-size: 0.9rem;
            padding: 0.75rem 0.9rem;
            margin-bottom: 1rem;
        }

        .login-footnote {
            margin-top: 1.15rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
        }

        .section {
            padding: clamp(3rem, 6vw, 5rem) 0;
            background: rgba(255, 255, 255, 0.94);
        }

        .section-muted { background: rgba(248, 250, 252, 0.96); }

        .section-title {
            font-family: var(--font-display);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }

        .section-lead {
            color: var(--muted);
            max-width: 40rem;
            margin: 0 auto 2.25rem;
        }

        .feature-card,
        .audience-tile,
        .step-card,
        .shot-card,
        .quote-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            height: 100%;
        }

        .feature-card {
            padding: 1.5rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 44px rgba(15, 23, 42, 0.1);
        }

        .feature-icon,
        .audience-icon,
        .step-num {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.8rem;
            display: grid;
            place-items: center;
            margin-bottom: 1rem;
            color: #fff;
            background: linear-gradient(135deg, var(--royal), var(--cyan));
            font-size: 1.15rem;
        }

        .feature-card h3,
        .step-card h3,
        .audience-tile h3 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }

        .feature-card p,
        .step-card p {
            color: #64748b;
            margin: 0;
            font-size: 0.95rem;
        }

        .audience-tile {
            padding: 1.25rem 1rem;
            text-align: center;
        }

        .audience-icon { margin: 0 auto 0.85rem; }

        .step-card { padding: 1.5rem; position: relative; }

        .step-num {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1rem;
        }

        .shot-card {
            min-height: 180px;
            display: grid;
            place-items: center;
            background:
                linear-gradient(135deg, rgba(29, 78, 216, 0.06), rgba(8, 145, 178, 0.08)),
                #fff;
            color: var(--muted);
            text-align: center;
            padding: 1.5rem;
        }

        .shot-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 0.75rem;
            background: #e2e8f0;
        }

        .quote-card {
            padding: 1.5rem;
            color: #64748b;
        }

        .quote-card strong {
            display: block;
            margin-top: 1rem;
            color: var(--navy);
            font-family: var(--font-display);
        }

        .accordion-button {
            font-family: var(--font-display);
            font-weight: 700;
            color: var(--navy);
        }

        .accordion-button:not(.collapsed) {
            background: #eff6ff;
            color: var(--royal);
            box-shadow: none;
        }

        .accordion-item {
            border: 1px solid var(--line);
            border-radius: 0.85rem !important;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .cta-banner {
            background:
                radial-gradient(circle at 15% 20%, rgba(34, 211, 238, 0.2), transparent 35%),
                linear-gradient(135deg, var(--navy-deep) 0%, var(--navy) 45%, #123a7a 100%);
            color: #fff;
            border-radius: calc(var(--radius) + 0.25rem);
            padding: clamp(2rem, 4vw, 3rem);
            box-shadow: 0 24px 50px rgba(7, 21, 40, 0.28);
        }

        .cta-banner h2 {
            font-family: var(--font-display);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.65rem;
        }

        .cta-banner p { color: rgba(226, 232, 240, 0.9); margin-bottom: 1.25rem; }

        .cta-banner a.contact-link {
            color: var(--cyan-soft);
            font-weight: 600;
            text-decoration: none;
        }

        .cta-banner a.contact-link:hover { text-decoration: underline; }

        .site-footer {
            border-top: 1px solid var(--line);
            padding: 1.5rem 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .site-footer a {
            color: var(--royal);
            font-weight: 600;
            text-decoration: none;
        }

        .site-footer a:hover { text-decoration: underline; }

        .wa-float {
            position: fixed;
            right: 1.1rem;
            bottom: 1.1rem;
            z-index: 1040;
            width: 3.4rem;
            height: 3.4rem;
            border-radius: 999px;
            background: var(--wa);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1.65rem;
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transition: transform 0.15s ease;
        }

        .wa-float:hover {
            color: #fff;
            transform: translateY(-2px) scale(1.04);
        }

        @media (max-width: 991.98px) {
            .hero-copy { margin-bottom: 2rem; }
            .login-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    @php
        $whatsappDemoUrl = 'https://wa.me/923335191392?text=Hi%2C%20I%20would%20like%20a%20demo%20of%20Coditium%20Pharmacy%20POS';
    @endphp

    <nav class="navbar navbar-expand-lg site-nav" aria-label="Primary">
        <div class="container">
            <a class="navbar-brand" href="#top">
                <span class="brand-mark" aria-hidden="true"><i class="bi bi-capsule"></i></span>
                Coditium Pharmacy POS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav" aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="primaryNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#who-its-for">Who It's For</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-nav-login" href="#login" id="navLoginBtn">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn-demo" href="{{ $whatsappDemoUrl }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            Book a Free Demo
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="top">
        <section class="hero">
            <div class="container">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-lg-6 hero-copy">
                        <h1>Coditium Pharmacy POS</h1>
                        <p class="lead">Medicine billing, stock, and pharmacy reports in one place—built for medical stores and pharmacy counters.</p>
                        <div class="hero-actions">
                            <a class="btn-demo" href="{{ $whatsappDemoUrl }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                Book a Free Demo
                            </a>
                            <a class="btn-outline-soft" href="#login" id="startTrialBtn">
                                Sign in to Pharmacy
                            </a>
                        </div>
                        <p class="trust-line mb-0">Expiry-aware stock · Fast medicine billing · Local pharmacy support</p>
                    </div>

                    <div class="col-lg-6">
                        <div class="login-panel" id="login">
                            <div class="login-card">
                                <div class="login-logo" aria-hidden="true">
                                    <i class="bi bi-capsule-pill"></i>
                                </div>
                                <p class="subtitle">Sign in to your pharmacy store dashboard.</p>

                                @if($errors->any())
                                    <div class="login-alert" role="alert">
                                        @foreach($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ url('/login') }}" autocomplete="on">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label" for="user_name">Username</label>
                                        <div class="input-shell">
                                            <i class="bi bi-person" aria-hidden="true"></i>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="user_name"
                                                name="user_name"
                                                value="{{ old('user_name') }}"
                                                required
                                                autofocus
                                                autocomplete="username"
                                                placeholder="admin">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label" for="password">Password</label>
                                        <div class="input-shell password-shell">
                                            <i class="bi bi-lock" aria-hidden="true"></i>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                required
                                                autocomplete="current-password"
                                                placeholder="Enter your password">
                                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                                                <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="login-meta">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="remember_ui">
                                            <label class="form-check-label" for="remember_ui">Remember Me</label>
                                        </div>
                                        <span class="forgot-link" title="Contact your administrator to reset your password">Forgot Password?</span>
                                    </div>

                                    <button type="submit" class="btn btn-login">
                                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
                                        Login
                                    </button>
                                </form>

                                <div class="login-footnote">
                                    Prefer a simpler screen?
                                    <a href="{{ url('/signin') }}">Open quick sign-in</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title">Everything your pharmacy counter needs</h2>
                    <p class="section-lead">Medicine stock, billing, purchases, and staff control—designed for pharmacy stores in Pakistan.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3">
                        <article class="feature-card">
                            <div class="feature-icon" aria-hidden="true"><i class="bi bi-capsule"></i></div>
                            <h3>Medicine stock under control</h3>
                            <p>Track batches, low stock, and expiry so shelves stay ready for every prescription.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="feature-card">
                            <div class="feature-icon" aria-hidden="true"><i class="bi bi-receipt"></i></div>
                            <h3>Bill medicines in seconds</h3>
                            <p>Fast pharmacy checkout with barcode scanning, receipts, and clear customer totals.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="feature-card">
                            <div class="feature-icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
                            <h3>Know what sells daily</h3>
                            <p>Daily sales, purchase, and best-selling medicine reports for smarter ordering.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="feature-card">
                            <div class="feature-icon" aria-hidden="true"><i class="bi bi-shop"></i></div>
                            <h3>Run one store or many</h3>
                            <p>Manage staff permissions and branches from one pharmacy workspace.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-muted" id="who-its-for">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title">Who it's for</h2>
                    <p class="section-lead">Built for pharmacy stores that keep communities healthy across Pakistan.</p>
                </div>
                <div class="row g-3 g-md-4 justify-content-center">
                    @foreach([
                        ['bi-capsule', 'Retail Pharmacies'],
                        ['bi-hospital', 'Medical Stores'],
                        ['bi-prescription2', 'Clinic Pharmacies'],
                        ['bi-building', 'Chain Pharmacies'],
                        ['bi-heart-pulse', 'Hospital Outlets'],
                        ['bi-bag-plus', 'Wholesale Chemists'],
                    ] as [$icon, $label])
                        <div class="col-6 col-md-4 col-xl-2">
                            <div class="audience-tile">
                                <div class="audience-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></div>
                                <h3 class="mb-0" style="font-size:0.98rem;">{{ $label }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" id="how-it-works">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title">How it works</h2>
                    <p class="section-lead">From first call to first medicine sale—without slowing your pharmacy counter.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <article class="step-card">
                            <div class="step-num" aria-hidden="true">1</div>
                            <h3>Sign up or request a demo</h3>
                            <p>Tell us about your pharmacy and we will show Coditium Pharmacy POS in action.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="step-card">
                            <div class="step-num" aria-hidden="true">2</div>
                            <h3>We set up your medicines and branches</h3>
                            <p>Import medicine catalog, configure stock, and prepare your pharmacy team to go live.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="step-card">
                            <div class="step-num" aria-hidden="true">3</div>
                            <h3>Start dispensing and track growth</h3>
                            <p>Bill customers, watch medicine inventory, and follow pharmacy sales from day one.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-muted" id="screenshots" aria-labelledby="screenshots-heading">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title" id="screenshots-heading">See Coditium Pharmacy POS</h2>
                    <p class="section-lead">Pharmacy product screenshots coming soon.</p>
                </div>
                {{-- TODO: replace with real product screenshots --}}
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="shot-card">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='360'%3E%3Crect fill='%23ccfbf1' width='100%25' height='100%25'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%230f766e' font-family='sans-serif' font-size='22'%3EPharmacy POS screen%3C/text%3E%3C/svg%3E" alt="Placeholder for Coditium Pharmacy POS sales screen screenshot">
                            <span class="mt-2 d-block">Pharmacy sales screen (TODO)</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="shot-card">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='360'%3E%3Crect fill='%23e2e8f0' width='100%25' height='100%25'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%2364748b' font-family='sans-serif' font-size='22'%3EInventory placeholder%3C/text%3E%3C/svg%3E" alt="Placeholder for Coditium POS inventory screenshot">
                            <span class="mt-2 d-block">Inventory (TODO)</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="shot-card">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='360'%3E%3Crect fill='%23e2e8f0' width='100%25' height='100%25'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%2364748b' font-family='sans-serif' font-size='22'%3EReports placeholder%3C/text%3E%3C/svg%3E" alt="Placeholder for Coditium POS reports screenshot">
                            <span class="mt-2 d-block">Reports (TODO)</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="testimonials" aria-labelledby="testimonials-heading">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title" id="testimonials-heading">What customers say</h2>
                    <p class="section-lead">Real stories will appear here once confirmed.</p>
                </div>
                {{-- TODO: replace with real customer testimonials --}}
                <div class="row g-4">
                    <div class="col-md-4">
                        <blockquote class="quote-card">
                            <p class="mb-0">“TODO: Add a short customer quote about billing speed or inventory control.”</p>
                            <strong>Customer name · Business type</strong>
                        </blockquote>
                    </div>
                    <div class="col-md-4">
                        <blockquote class="quote-card">
                            <p class="mb-0">“TODO: Add a short customer quote about multi-branch or reporting.”</p>
                            <strong>Customer name · Business type</strong>
                        </blockquote>
                    </div>
                    <div class="col-md-4">
                        <blockquote class="quote-card">
                            <p class="mb-0">“TODO: Add a short customer quote about support or onboarding.”</p>
                            <strong>Customer name · Business type</strong>
                        </blockquote>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-muted" id="faq">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title">FAQ</h2>
                    <p class="section-lead">Quick answers to common questions. Confirm details with the Coditium team before publishing.</p>
                </div>
                {{-- TODO: confirm FAQ answers with Coditium --}}
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqOneHeading">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                                        What hardware do I need?
                                    </button>
                                </h3>
                                <div id="faqOne" class="accordion-collapse collapse show" aria-labelledby="faqOneHeading" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        A computer or tablet with a browser is enough to start. Optional barcode scanners, receipt printers, and cash drawers are supported. <!-- TODO: confirm FAQ answers with Coditium -->
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqTwoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                        Does it work offline?
                                    </button>
                                </h3>
                                <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqTwoHeading" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Coditium Pharmacy POS is designed to keep your medicine counter moving when connectivity dips, then sync when you are back online. <!-- TODO: confirm FAQ answers with Coditium -->
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqThreeHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                        Can you migrate my existing data?
                                    </button>
                                </h3>
                                <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqThreeHeading" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes—products, customers, and stock can usually be imported during setup. Share a sample export and we will confirm the best path. <!-- TODO: confirm FAQ answers with Coditium -->
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqFourHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                                        Do you provide training and support?
                                    </button>
                                </h3>
                                <div id="faqFour" class="accordion-collapse collapse" aria-labelledby="faqFourHeading" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Local onboarding and ongoing support are available so your team can bill with confidence from day one. <!-- TODO: confirm FAQ answers with Coditium -->
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="faqFiveHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFive" aria-expanded="false" aria-controls="faqFive">
                                        Can I manage multiple branches?
                                    </button>
                                </h3>
                                <div id="faqFive" class="accordion-collapse collapse" aria-labelledby="faqFiveHeading" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes. Track stock, sales, and staff permissions across branches from one place. <!-- TODO: confirm FAQ answers with Coditium -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="contact">
            <div class="container">
                <div class="cta-banner">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <h2>Ready to modernize your pharmacy?</h2>
                            <p class="mb-2">Book a free demo with Coditium Solutions and see Pharmacy POS on your medicine counter.</p>
                            <p class="mb-0">
                                Cell / WhatsApp:
                                <a class="contact-link" href="tel:+923335191392">0333-5191392</a>
                                ·
                                <a class="contact-link" href="https://www.coditium.com" target="_blank" rel="noopener noreferrer">www.coditium.com</a>
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a class="btn-demo" href="{{ $whatsappDemoUrl }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                Book a Free Demo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>© 2026 Coditium Solutions. All rights reserved.</div>
            <div>
                <a href="https://www.coditium.com" target="_blank" rel="noopener noreferrer">Coditium Solutions</a>
            </div>
        </div>
    </footer>

    <a class="wa-float" href="{{ $whatsappDemoUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
        <i class="bi bi-whatsapp" aria-hidden="true"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var btn = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            var icon = document.getElementById('togglePasswordIcon');
            if (!btn || !input || !icon) return;

            btn.addEventListener('click', function () {
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
                btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        })();

        (function () {
            function focusLogin() {
                var field = document.getElementById('user_name');
                var card = document.getElementById('login');
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                if (field) {
                    setTimeout(function () { field.focus(); }, 350);
                }
            }

            var navLogin = document.getElementById('navLoginBtn');
            var startTrial = document.getElementById('startTrialBtn');
            if (navLogin) {
                navLogin.addEventListener('click', function (e) {
                    e.preventDefault();
                    focusLogin();
                });
            }
            if (startTrial) {
                startTrial.addEventListener('click', function (e) {
                    e.preventDefault();
                    focusLogin();
                });
            }

            if (window.location.hash === '#login') {
                focusLogin();
            }
        })();
    </script>
</body>
</html>
