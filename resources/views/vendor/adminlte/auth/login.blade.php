<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMNASTHENIOX — Login</title>

    {{-- CSRF for Laravel --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- GSAP --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        :root {
            --bg-deep: #0a0a0f;
            --bg-panel: #111118;
            --bg-card: #16161f;
            --accent: #c8f731;
            --accent-dim: #9fbd28;
            --text-main: #eeeef2;
            --text-muted: #6b6b80;
            --border: #2a2a3a;
            --danger: #ff4d6d;
            --input-bg: #1e1e2a;
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
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text-main);
            overflow: hidden;
        }

        /* ── CANVAS BACKGROUND ── */
        #bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── GRID OVERLAY ── */
        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            background-image:
                linear-gradient(rgba(200, 247, 49, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(200, 247, 49, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* ── MAIN LAYOUT ── */
        .page-wrapper {
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 64px;
            border-right: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(200, 247, 49, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            opacity: 0;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--bg-deep);
            font-weight: 900;
            box-shadow: 0 0 24px rgba(200, 247, 49, 0.35);
        }

        .brand-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 22px;
            letter-spacing: 3px;
            color: var(--text-main);
        }

        .hero-text {
            opacity: 0;
        }

        .hero-text .eyebrow {
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-text .eyebrow::before {
            content: '';
            display: block;
            width: 32px;
            height: 1px;
            background: var(--accent);
        }

        .hero-text h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(52px, 5.5vw, 84px);
            line-height: 0.95;
            letter-spacing: 2px;
            color: var(--text-main);
            margin-bottom: 24px;
        }

        .hero-text h1 span {
            color: var(--accent);
            display: block;
        }

        .hero-text p {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 320px;
            line-height: 1.7;
        }

        .stats-row {
            display: flex;
            gap: 40px;
            opacity: 0;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-value {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            color: var(--accent);
            letter-spacing: 1px;
        }

        .stat-label {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 56px 80px;
            position: relative;
        }

        /* ── CARD ── */
        .login-card {
            width: 100%;
            max-width: 420px;
            opacity: 0;
            transform: translateY(30px);
        }

        .card-header-text {
            margin-bottom: 40px;
        }

        .card-header-text .welcome-tag {
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .card-header-text h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 40px;
            letter-spacing: 2px;
            color: var(--text-main);
        }

        /* ── ALERTS ── */
        .alert-error {
            background: rgba(255, 77, 109, 0.1);
            border: 1px solid rgba(255, 77, 109, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 13px;
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── FORM ── */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            transition: color 0.3s;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-main);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(200, 247, 49, 0.1);
        }

        .form-control:focus+.input-icon i,
        .input-wrap:focus-within i {
            color: var(--accent);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(255, 77, 109, 0.1);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        /* Password toggle */
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            transition: color 0.3s;
            font-size: 14px;
        }

        .toggle-pass:hover {
            color: var(--accent);
        }

        /* ── REMEMBER + FORGOT ── */
        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .remember-wrap span {
            font-size: 13px;
            color: var(--text-muted);
        }

        .forgot-link {
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: var(--accent);
        }

        /* ── SUBMIT BTN ── */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            color: var(--bg-deep);
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            letter-spacing: 3px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background 0.3s, transform 0.15s, box-shadow 0.3s;
            box-shadow: 0 4px 24px rgba(200, 247, 49, 0.25);
        }

        .btn-login:hover {
            background: #d4ff3a;
            box-shadow: 0 6px 32px rgba(200, 247, 49, 0.4);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0px);
        }

        .btn-login .btn-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.15);
            transform: scale(0);
            pointer-events: none;
            animation: ripple 0.6s linear;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* ── FOOTER NOTE ── */
        .card-footer-note {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: var(--text-muted);
            opacity: 0;
        }

        .card-footer-note span {
            color: var(--accent);
        }

        /* ── CORNER DECO ── */
        .corner-deco {
            position: absolute;
            bottom: 40px;
            right: 40px;
            opacity: 0.06;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 120px;
            line-height: 1;
            letter-spacing: -4px;
            color: var(--accent);
            user-select: none;
            pointer-events: none;
        }

        /* ── LOADING STATE ── */
        .btn-login.loading {
            pointer-events: none;
            background: var(--accent-dim);
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.3);
            border-top-color: var(--bg-deep);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .page-wrapper {
                grid-template-columns: 1fr;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 40px 32px;
            }
        }
    </style>
</head>

<body>

    {{-- Animated background canvas --}}
    <canvas id="bg-canvas"></canvas>
    <div class="grid-overlay"></div>

    <div class="page-wrapper">

        {{-- ── LEFT PANEL ── --}}
        <div class="left-panel">
            <div class="brand">
                <div class="brand-icon">G</div>
                <div class="brand-name">GYMNASTHENIOX</div>
            </div>

            <div class="hero-text">
                <div class="eyebrow">Inventory System</div>
                <h1>TRACK.<br>MANAGE.<br><span>DOMINATE.</span></h1>
                <p>Real-time gym equipment tracking, purchase orders, manpower management — all in one command center.
                </p>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-value" data-count="500">0</span>
                    <span class="stat-label">Equipment</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" data-count="24">0</span>
                    <span class="stat-label">Warehouses</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" data-count="99">0</span>
                    <span class="stat-label">Uptime %</span>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL ── --}}
        <div class="right-panel">
            <div class="corner-deco">GX</div>

            <div class="login-card">
                <div class="card-header-text">
                    <div class="welcome-tag">Welcome back</div>
                    <h2>SIGN IN</h2>
                </div>

                {{-- Session / Validation Errors --}}
                @if ($errors->any())
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert-error"
                        style="color: var(--accent); border-color: rgba(200,247,49,0.3); background: rgba(200,247,49,0.07);">
                        <i class="fas fa-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- LOGIN FORM --}}
                <form action="{{ url('login') }}" method="POST" id="loginForm">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email') }}" placeholder="you@gymnastheniox.com" autocomplete="email"
                                autofocus>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password"
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="••••••••••" autocomplete="current-password">
                            <button type="button" class="toggle-pass" id="togglePass" aria-label="Toggle password">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="form-meta">
                        <label class="remember-wrap">
                            <input type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>

                        @php
                            $passResetUrl = config('adminlte.use_route_url', false)
                                ? route(config('adminlte.password_reset_url', 'password/reset'))
                                : url(config('adminlte.password_reset_url', 'password/reset'));
                        @endphp
                        <a href="{{ $passResetUrl }}" class="forgot-link">Forgot password?</a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-login" id="loginBtn">
                        ENTER SYSTEM
                    </button>
                </form>

                <div class="card-footer-note">
                    Authorized personnel only &mdash; <span>GYMNASTHENIOX</span>
                </div>
            </div>
        </div>

    </div>

    <script>
        // ── CANVAS PARTICLES ──────────────────────────────────────────────
        (function() {
            const canvas = document.getElementById('bg-canvas');
            const ctx = canvas.getContext('2d');
            let W, H, particles = [];

            function resize() {
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }

            function createParticles() {
                particles = [];
                const count = Math.floor(W / 18);
                for (let i = 0; i < count; i++) {
                    particles.push({
                        x: Math.random() * W,
                        y: Math.random() * H,
                        r: Math.random() * 1.5 + 0.3,
                        dx: (Math.random() - 0.5) * 0.3,
                        dy: (Math.random() - 0.5) * 0.3,
                        alpha: Math.random() * 0.4 + 0.05
                    });
                }
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);
                particles.forEach(p => {
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(200,247,49,${p.alpha})`;
                    ctx.fill();
                    p.x += p.dx;
                    p.y += p.dy;
                    if (p.x < 0 || p.x > W) p.dx *= -1;
                    if (p.y < 0 || p.y > H) p.dy *= -1;
                });
                requestAnimationFrame(draw);
            }

            resize();
            createParticles();
            draw();
            window.addEventListener('resize', () => {
                resize();
                createParticles();
            });
        })();

        // ── GSAP ENTRANCE ANIMATIONS ──────────────────────────────────────
        window.addEventListener('DOMContentLoaded', () => {
            const tl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });

            // Brand
            tl.to('.brand', {
                    opacity: 1,
                    y: 0,
                    duration: 0.7
                }, 0.2)

                // Hero text lines — stagger in
                .from('.hero-text .eyebrow', {
                    opacity: 0,
                    x: -20,
                    duration: 0.6
                }, 0.5)
                .to('.hero-text', {
                    opacity: 1,
                    duration: 0
                }, 0.5)
                .from('.hero-text h1', {
                    opacity: 0,
                    y: 40,
                    duration: 0.8
                }, 0.6)
                .from('.hero-text p', {
                    opacity: 0,
                    y: 20,
                    duration: 0.7
                }, 0.9)

                // Stats row
                .to('.stats-row', {
                    opacity: 1,
                    duration: 0.6
                }, 1.1)
                .from('.stat-item', {
                    opacity: 0,
                    y: 20,
                    stagger: 0.15,
                    duration: 0.5
                }, 1.1)

                // Login card
                .to('.login-card', {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: 'power2.out'
                }, 0.4)
                .from('.card-header-text', {
                    opacity: 0,
                    y: 20,
                    duration: 0.6
                }, 0.7)
                .from('.form-group', {
                    opacity: 0,
                    y: 16,
                    stagger: 0.12,
                    duration: 0.5
                }, 0.85)
                .from('.form-meta', {
                    opacity: 0,
                    y: 10,
                    duration: 0.5
                }, 1.2)
                .from('.btn-login', {
                    opacity: 0,
                    scale: 0.95,
                    duration: 0.5
                }, 1.35)
                .to('.card-footer-note', {
                    opacity: 1,
                    duration: 0.5
                }, 1.5);

            // Animated stat counters
            document.querySelectorAll('.stat-value[data-count]').forEach(el => {
                const target = +el.getAttribute('data-count');
                gsap.to({
                    val: 0
                }, {
                    val: target,
                    duration: 1.8,
                    delay: 1.1,
                    ease: 'power2.out',
                    onUpdate: function() {
                        el.textContent = Math.round(this.targets()[0].val) + (target === 99 ?
                            '%' : '+');
                    }
                });
            });
        });

        // ── PASSWORD TOGGLE ───────────────────────────────────────────────
        document.getElementById('togglePass').addEventListener('click', function() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const isHidden = pw.type === 'password';
            pw.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            gsap.from(icon, {
                scale: 0.6,
                duration: 0.25,
                ease: 'back.out(2)'
            });
        });

        // ── FORM SUBMIT ANIMATION ─────────────────────────────────────────
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.textContent = 'AUTHENTICATING';
            gsap.to(btn, {
                scale: 0.98,
                duration: 0.15
            });
        });

        // ── BTN RIPPLE ────────────────────────────────────────────────────
        document.getElementById('loginBtn').addEventListener('click', function(e) {
            const btn = this;
            const circle = document.createElement('span');
            const d = Math.max(btn.clientWidth, btn.clientHeight);
            const rect = btn.getBoundingClientRect();
            circle.className = 'btn-ripple';
            circle.style.cssText =
                `width:${d}px;height:${d}px;left:${e.clientX - rect.left - d/2}px;top:${e.clientY - rect.top - d/2}px;`;
            btn.appendChild(circle);
            setTimeout(() => circle.remove(), 700);
        });

        // ── INPUT FOCUS MICRO-ANIMATIONS ─────────────────────────────────
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', () => {
                gsap.to(input.closest('.input-wrap'), {
                    scale: 1.01,
                    duration: 0.2,
                    ease: 'power1.out'
                });
            });
            input.addEventListener('blur', () => {
                gsap.to(input.closest('.input-wrap'), {
                    scale: 1,
                    duration: 0.2
                });
            });
        });
    </script>

</body>

</html>
