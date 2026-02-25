<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMNASTHENIOX — Reset Password</title>
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

        /* ── LEFT PANEL (same as login/forgot) ── */
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

        /* Password requirements hint */
        .brand-bottom {
            position: relative;
            z-index: 2;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            opacity: 0;
        }

        .req-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .req-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.35);
        }

        .req-list li i {
            width: 18px;
            height: 18px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: rgba(184, 224, 0, 0.5);
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

        .alert-error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: var(--danger);
        }

        /* Field */
        .field {
            margin-bottom: 18px;
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
            padding: 13px 42px 13px 40px;
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
        }

        .invalid-msg {
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
        }

        .pass-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 13px;
            padding: 4px;
            transition: color 0.25s;
        }

        .pass-toggle:hover {
            color: var(--text-dark);
        }

        /* Strength bar */
        .strength-bar {
            margin-top: 8px;
            height: 3px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 3px;
            width: 0;
            transition: width 0.4s ease, background 0.4s ease;
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
            margin-top: 6px;
        }

        .btn-submit:hover {
            background: #262640;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(26, 26, 46, 0.35);
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
                <div class="tag"><i class="fas fa-circle" style="font-size:6px"></i> New Password</div>
                <h1>SECURE YOUR<br><em>ACCOUNT.</em></h1>
                <p>Choose a strong password that you haven't used before to keep your account safe.</p>
            </div>
            <div class="brand-bottom">
                <ul class="req-list">
                    <li><i class="fas fa-check"></i> At least 8 characters long</li>
                    <li><i class="fas fa-check"></i> Mix of letters and numbers</li>
                    <li><i class="fas fa-check"></i> Avoid common passwords</li>
                </ul>
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
                    <h2>NEW PASSWORD</h2>
                    <p>Enter and confirm your new password below.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email --}}
                    <div class="field" id="f-email">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email', $request->email) }}" placeholder="your@email.com"
                                autocomplete="username" autofocus>
                            @error('email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div class="field" id="f-pass">
                        <label for="password">New Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password"
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="••••••••" autocomplete="new-password" oninput="checkStrength(this.value)">
                            <button type="button" class="pass-toggle" onclick="togglePass('password','eye1')">
                                <i class="fas fa-eye" id="eye1"></i>
                            </button>
                            @error('password')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="field" id="f-confirm">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" placeholder="••••••••" autocomplete="new-password">
                            <button type="button" class="pass-toggle"
                                onclick="togglePass('password_confirmation','eye2')">
                                <i class="fas fa-eye" id="eye2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        RESET PASSWORD
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
                .from('.req-list li', {
                    opacity: 0,
                    x: -12,
                    stagger: 0.1,
                    duration: 0.4
                }, 0.8)
                .to('.form-box-header', {
                    opacity: 1,
                    duration: 0.7
                }, 0.35)
                .to('#f-email', {
                    opacity: 1,
                    duration: 0.5
                }, 0.5)
                .to('#f-pass', {
                    opacity: 1,
                    duration: 0.5
                }, 0.62)
                .to('#f-confirm', {
                    opacity: 1,
                    duration: 0.5
                }, 0.74)
                .to('.btn-submit', {
                    opacity: 1,
                    duration: 0.5
                }, 0.86)
                .to('.form-note', {
                    opacity: 1,
                    duration: 0.4
                }, 1.0);
        });

        function togglePass(inputId, iconId) {
            const pw = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            gsap.from(icon, {
                scale: 0.5,
                duration: 0.2,
                ease: 'back.out(2)'
            });
        }

        function checkStrength(val) {
            const fill = document.getElementById('strengthFill');
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const widths = ['0%', '30%', '55%', '80%', '100%'];
            const colors = ['transparent', '#e53e3e', '#e5923e', '#b8e000', '#38a169'];
            fill.style.width = widths[score];
            fill.style.background = colors[score];
        }

        document.getElementById('resetForm').addEventListener('submit', () => {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.textContent = 'RESETTING...';
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
