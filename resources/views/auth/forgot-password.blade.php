<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMNASTHENIOX — Forgot Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        :root {
            --white: #ffffff;
            --off-white: #f7f8fc;
            --border: #e2e5f0;
            --text-dark: #0f0f1a;
            --text-mid: #4a4a6a;
            --text-light: #9a9ab0;
            --accent: #1a1a2e;
            --lime: #b8e000;
            --input-bg: #ffffff;
            --danger: #e53e3e;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--off-white);
            color: var(--text-dark);
        }

        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .brand-panel {
            background: var(--accent);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 60px;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(184, 224, 0, 0.08);
        }

        .grid-lines {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .brand-top {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
        }

        .logo-badge {
            width: 44px;
            height: 44px;
            background: var(--lime);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 20px;
            color: var(--accent);
            box-shadow: 0 0 20px rgba(184, 224, 0, 0.4);
        }

        .brand-name-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            letter-spacing: 4px;
            color: rgba(255, 255, 255, 0.9);
        }

        .brand-middle {
            position: relative;
            z-index: 2;
            opacity: 0;
        }

        .brand-middle .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(184, 224, 0, 0.15);
            border: 1px solid rgba(184, 224, 0, 0.3);
            border-radius: 100px;
            padding: 6px 14px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--lime);
            margin-bottom: 28px;
        }

        .brand-middle h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(48px, 5vw, 76px);
            line-height: 0.92;
            letter-spacing: 2px;
            color: #fff;
            margin-bottom: 24px;
        }

        .brand-middle h1 em {
            font-style: normal;
            color: var(--lime);
        }

        .brand-middle p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.45);
            line-height: 1.75;
            max-width: 300px;
        }

        .brand-bottom {
            position: relative;
            z-index: 2;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            opacity: 0;
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .security-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background: rgba(184, 224, 0, 0.12);
            border: 1px solid rgba(184, 224, 0, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--lime);
            font-size: 15px;
        }

        .security-text h4 {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 4px;
        }

        .security-text p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            line-height: 1.6;
        }

        /* ── RIGHT PANEL ── */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            background: var(--off-white);
        }

        .form-box {
            width: 100%;
            max-width: 400px;
        }

        .form-box-header {
            margin-bottom: 32px;
            opacity: 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-light);
            text-decoration: none;
            margin-bottom: 28px;
            transition: color 0.25s;
        }

        .back-link:hover {
            color: var(--text-dark);
        }

        .form-box-header h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 40px;
            letter-spacing: 2px;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 10px;
        }

        .form-box-header p {
            font-size: 13px;
            color: var(--text-light);
            line-height: 1.65;
        }

        /* Alert */
        .alert {
            padding: 13px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert i {
            margin-top: 1px;
        }

        .alert-success {
            background: #f0fff4;
            border: 1px solid #c6f6d5;
            color: #276749;
        }

        .alert-error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: var(--danger);
        }

        /* Field */
        .field {
            margin-bottom: 20px;
            opacity: 0;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text-mid);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 13px;
            pointer-events: none;
            transition: color 0.25s;
        }

        input.form-control {
            width: 100%;
            padding: 13px 14px 13px 40px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
            box-shadow: 0 1px 3px rgba(15, 15, 26, 0.06);
        }

        input.form-control::placeholder {
            color: var(--text-light);
        }

        input.form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(26, 26, 46, 0.07);
        }

        .input-wrap:focus-within .input-icon {
            color: var(--accent);
        }

        input.form-control.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(229, 62, 62, 0.07);
        }

        .invalid-msg {
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: #fff;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            letter-spacing: 3px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.25s, transform 0.15s, box-shadow 0.25s;
            box-shadow: 0 4px 16px rgba(26, 26, 46, 0.25);
            position: relative;
            overflow: hidden;
            opacity: 0;
        }

        .btn-submit:hover {
            background: #262640;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(26, 26, 46, 0.35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.75;
        }

        .btn-submit.loading::after {
            content: '';
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: scale(0);
            pointer-events: none;
            animation: ripple-anim 0.55s linear;
        }

        @keyframes ripple-anim {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .form-note {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-light);
            opacity: 0;
        }

        .form-note a {
            color: var(--text-dark);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.25s;
        }

        .form-note a:hover {
            color: var(--accent);
        }

        @media (max-width: 860px) {
            .page {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                padding: 40px 28px;
            }
        }
    </style>
</head>

<body>
    <div class="page">

        {{-- ── LEFT BRAND PANEL ── --}}
        <div class="brand-panel">
            <div class="grid-lines"></div>

            <div class="brand-top">
                <div class="logo-badge">G</div>
                <span class="brand-name-text">GYMNASTHENIOX</span>
            </div>

            <div class="brand-middle">
                <div class="tag"><i class="fas fa-circle" style="font-size:6px"></i> Account Recovery</div>
                <h1>RESET YOUR<br><em>ACCESS.</em></h1>
                <p>Enter your registered email and we'll send you a secure link to reset your password. Link expires in
                    60 minutes.</p>
            </div>

            <div class="brand-bottom">
                <div class="security-note">
                    <div class="security-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="security-text">
                        <h4>Secure Reset Process</h4>
                        <p>The reset link is single-use and expires automatically for your account's protection.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT FORM PANEL ── --}}
        <div class="form-panel">
            <div class="form-box">

                <div class="form-box-header">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fas fa-arrow-left" style="font-size:10px"></i>
                        Back to sign in
                    </a>
                    <h2>FORGOT PASSWORD</h2>
                    <p>No worries — enter your email below and we'll send you a reset link.</p>
                </div>

                {{-- Success status --}}
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                    @csrf

                    <div class="field" id="f-email">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email') }}" placeholder="your@email.com" autocomplete="email" autofocus>
                            @error('email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        SEND RESET LINK
                    </button>
                </form>

                <div class="form-note">
                    Remember your password?
                    <a href="{{ route('login') }}">Sign in here</a>
                </div>

            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const tl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });
            tl.to('.brand-top', {
                    opacity: 1,
                    duration: 0.7
                }, 0.1)
                .to('.brand-middle', {
                    opacity: 1,
                    duration: 0.8
                }, 0.3)
                .from('.brand-middle .tag', {
                    opacity: 0,
                    x: -16,
                    duration: 0.5
                }, 0.3)
                .from('.brand-middle h1', {
                    opacity: 0,
                    y: 30,
                    duration: 0.7
                }, 0.45)
                .from('.brand-middle p', {
                    opacity: 0,
                    y: 16,
                    duration: 0.6
                }, 0.65)
                .to('.brand-bottom', {
                    opacity: 1,
                    duration: 0.6
                }, 0.75)
                .to('.form-box-header', {
                    opacity: 1,
                    duration: 0.7
                }, 0.35)
                .to('#f-email', {
                    opacity: 1,
                    duration: 0.55
                }, 0.55)
                .to('.btn-submit', {
                    opacity: 1,
                    duration: 0.5
                }, 0.72)
                .to('.form-note', {
                    opacity: 1,
                    duration: 0.4
                }, 0.88);
        });

        document.getElementById('forgotForm').addEventListener('submit', () => {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.textContent = 'SENDING...';
            gsap.to(btn, {
                scale: 0.98,
                duration: 0.12
            });
        });

        document.getElementById('submitBtn').addEventListener('click', function(e) {
            const r = document.createElement('span');
            const d = Math.max(this.clientWidth, this.clientHeight);
            const rect = this.getBoundingClientRect();
            r.className = 'ripple';
            r.style.cssText =
                `width:${d}px;height:${d}px;left:${e.clientX-rect.left-d/2}px;top:${e.clientY-rect.top-d/2}px`;
            this.appendChild(r);
            setTimeout(() => r.remove(), 600);
        });

        document.querySelectorAll('.form-control').forEach(inp => {
            inp.addEventListener('focus', () => gsap.to(inp.parentElement, {
                scale: 1.01,
                duration: 0.18
            }));
            inp.addEventListener('blur', () => gsap.to(inp.parentElement, {
                scale: 1,
                duration: 0.18
            }));
        });
    </script>
</body>

</html>
