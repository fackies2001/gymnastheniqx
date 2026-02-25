<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMNASTHENIOX — Gym Inventory System</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <style>
        :root {
            --dark: #0f0f1a;
            --dark-2: #16162a;
            --dark-3: #1e1e35;
            --white: #ffffff;
            --off-white: #f7f8fc;
            --border: #e8eaf2;
            --text-dark: #0f0f1a;
            --text-mid: #4a4a6a;
            --text-light: #9a9ab0;
            --lime: #b8e000;
            --lime-dark: #8aaa00;
            --lime-glow: rgba(184, 224, 0, 0.25);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--white);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ════════════════════════════════════════
           NAV
        ════════════════════════════════════════ */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 0 60px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo {
            width: 36px;
            height: 36px;
            background: var(--dark);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 16px;
            color: var(--lime);
        }

        .nav-wordmark {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 17px;
            letter-spacing: 3px;
            color: var(--dark);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 36px;
            list-style: none;
        }

        .nav-links a {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-mid);
            text-decoration: none;
            transition: color 0.25s;
        }

        .nav-links a:hover {
            color: var(--dark);
        }

        .nav-cta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-outline {
            padding: 9px 20px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-mid);
            text-decoration: none;
            transition: all 0.25s;
        }

        .btn-outline:hover {
            border-color: var(--dark);
            color: var(--dark);
        }

        .btn-primary {
            padding: 9px 22px;
            background: var(--dark);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: var(--dark-2);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 15, 26, 0.2);
        }

        /* ════════════════════════════════════════
           HERO
        ════════════════════════════════════════ */
        .hero {
            min-height: 100vh;
            background: var(--dark);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 140px 60px 80px;
        }

        /* Animated grid */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 56px 56px;
            pointer-events: none;
        }

        /* Lime glow blob */
        .hero-blob {
            position: absolute;
            top: -200px;
            right: -200px;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(184, 224, 0, 0.12) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-blob-2 {
            position: absolute;
            bottom: -150px;
            left: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(184, 224, 0, 0.06) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 820px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(184, 224, 0, 0.12);
            border: 1px solid rgba(184, 224, 0, 0.25);
            border-radius: 100px;
            padding: 7px 16px;
            font-size: 11px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--lime);
            margin-bottom: 32px;
            opacity: 0;
        }

        .hero-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--lime);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.75);
            }
        }

        .hero-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(64px, 8vw, 112px);
            line-height: 0.9;
            letter-spacing: 2px;
            color: #fff;
            margin-bottom: 32px;
            opacity: 0;
        }

        .hero-title em {
            font-style: normal;
            color: var(--lime);
        }

        .hero-title .line-outline {
            -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.25);
            color: transparent;
        }

        .hero-sub {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.5);
            max-width: 520px;
            line-height: 1.75;
            margin-bottom: 44px;
            opacity: 0;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            opacity: 0;
        }

        .hero-btn-main {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: var(--lime);
            color: var(--dark);
            font-family: 'Bebas Neue', sans-serif;
            font-size: 17px;
            letter-spacing: 2px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.25s;
            box-shadow: 0 4px 24px var(--lime-glow);
        }

        .hero-btn-main:hover {
            background: #c8f000;
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(184, 224, 0, 0.4);
        }

        .hero-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.25s;
        }

        .hero-btn-ghost:hover {
            border-color: rgba(255, 255, 255, 0.35);
            color: #fff;
        }

        /* Hero stats bar */
        .hero-stats {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 48px;
            margin-top: 80px;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            opacity: 0;
        }

        .h-stat-val {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 40px;
            color: var(--lime);
            letter-spacing: 1px;
        }

        .h-stat-lbl {
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.35);
            margin-top: 2px;
        }

        /* Scroll indicator */
        .scroll-hint {
            position: absolute;
            bottom: 36px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.25);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            z-index: 2;
            animation: float 2s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateX(-50%) translateY(0);
            }

            50% {
                transform: translateX(-50%) translateY(6px);
            }
        }

        /* ════════════════════════════════════════
           FEATURES
        ════════════════════════════════════════ */
        .section {
            padding: 100px 60px;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--lime-dark);
            font-weight: 700;
            margin-bottom: 16px;
        }

        .section-tag::before {
            content: '';
            display: block;
            width: 24px;
            height: 2px;
            background: var(--lime-dark);
            border-radius: 2px;
        }

        .section-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(36px, 4vw, 56px);
            letter-spacing: 1.5px;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 16px;
        }

        .section-sub {
            font-size: 15px;
            color: var(--text-light);
            max-width: 480px;
            line-height: 1.7;
        }

        /* Features grid */
        .features-section {
            background: var(--off-white);
        }

        .features-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: end;
            margin-bottom: 64px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px 28px;
            transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
            opacity: 0;
            transform: translateY(24px);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(15, 15, 26, 0.08);
            border-color: rgba(15, 15, 26, 0.12);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--dark);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--lime);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 13px;
            color: var(--text-light);
            line-height: 1.7;
        }

        /* ════════════════════════════════════════
           MODULES SECTION
        ════════════════════════════════════════ */
        .modules-section {
            background: var(--dark);
            position: relative;
            overflow: hidden;
        }

        .modules-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(184, 224, 0, 0.07) 0%, transparent 65%);
        }

        .modules-section .section-title {
            color: #fff;
        }

        .modules-section .section-sub {
            color: rgba(255, 255, 255, 0.4);
        }

        .modules-section .section-tag {
            color: var(--lime);
        }

        .modules-section .section-tag::before {
            background: var(--lime);
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 56px;
        }

        .module-item {
            background: var(--dark);
            padding: 36px 40px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            transition: background 0.3s;
            opacity: 0;
        }

        .module-item:hover {
            background: var(--dark-2);
        }

        .module-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 13px;
            letter-spacing: 2px;
            color: rgba(184, 224, 0, 0.4);
            min-width: 28px;
            margin-top: 2px;
        }

        .module-content h4 {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .module-content p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.35);
            line-height: 1.65;
        }

        /* ════════════════════════════════════════
           HOW IT WORKS
        ════════════════════════════════════════ */
        .steps-section {
            background: var(--white);
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-top: 64px;
            position: relative;
        }

        .steps-grid::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 24px;
            right: 24px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border) 15%, var(--border) 85%, transparent);
            z-index: 0;
        }

        .step-card {
            position: relative;
            z-index: 1;
            text-align: center;
            opacity: 0;
        }

        .step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 18px;
            color: var(--lime);
            margin: 0 auto 20px;
        }

        .step-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .step-card p {
            font-size: 13px;
            color: var(--text-light);
            line-height: 1.65;
        }

        /* ════════════════════════════════════════
           CTA BANNER
        ════════════════════════════════════════ */
        .cta-section {
            background: var(--dark);
            padding: 100px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, rgba(184, 224, 0, 0.1) 0%, transparent 65%);
            pointer-events: none;
        }

        .cta-section h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(44px, 5vw, 72px);
            letter-spacing: 2px;
            color: #fff;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            opacity: 0;
        }

        .cta-section p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.45);
            max-width: 400px;
            margin: 0 auto 40px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
            opacity: 0;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: var(--lime);
            color: var(--dark);
            font-family: 'Bebas Neue', sans-serif;
            font-size: 19px;
            letter-spacing: 2.5px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.25s;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 32px var(--lime-glow);
            opacity: 0;
        }

        .cta-btn:hover {
            background: #c8f000;
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(184, 224, 0, 0.45);
        }

        /* ════════════════════════════════════════
           FOOTER
        ════════════════════════════════════════ */
        footer {
            background: var(--dark);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 32px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-logo {
            width: 30px;
            height: 30px;
            background: var(--lime);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 13px;
            color: var(--dark);
        }

        .footer-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 14px;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-copy {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.25);
        }

        .footer-link {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.35);
            text-decoration: none;
            transition: color 0.25s;
        }

        .footer-link:hover {
            color: var(--lime);
        }

        @media (max-width: 960px) {
            nav {
                padding: 0 24px;
            }

            .nav-links {
                display: none;
            }

            .hero {
                padding: 120px 24px 64px;
            }

            .section {
                padding: 72px 24px;
            }

            .features-header {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .modules-grid {
                grid-template-columns: 1fr;
            }

            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps-grid::before {
                display: none;
            }

            .cta-section {
                padding: 72px 24px;
            }

            footer {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                padding: 24px;
            }

            .hero-stats {
                gap: 28px;
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

    {{-- ════ NAV ════ --}}
    <nav id="mainNav">
        <a href="{{ url('/') }}" class="nav-brand">
            <div class="nav-logo">G</div>
            <span class="nav-wordmark">GYMNASTHENIOX</span>
        </a>

        <ul class="nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#modules">Modules</a></li>
            <li><a href="#how-it-works">How it works</a></li>
        </ul>

        <div class="nav-cta">
            <a href="{{ url('login') }}" class="btn-primary">
                <i class="fas fa-sign-in-alt" style="font-size:12px"></i>
                Sign In
            </a>
        </div>
    </nav>


    {{-- ════ HERO ════ --}}
    <section class="hero" id="home">
        <div class="hero-grid"></div>
        <div class="hero-blob"></div>
        <div class="hero-blob-2"></div>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="dot"></span>
                Gym Inventory Management System
            </div>

            <h1 class="hero-title">
                CONTROL YOUR<br>
                <em>GYM.</em><br>
                <span class="line-outline">DOMINATE</span>
            </h1>

            <p class="hero-sub">
                GYMNASTHENIOX is a complete inventory management platform built for gyms and fitness centers — from
                equipment tracking to purchase orders, all in one place.
            </p>

            <div class="hero-actions">
                <a href="{{ url('login') }}" class="hero-btn-main">
                    <i class="fas fa-sign-in-alt"></i>
                    Access System
                </a>
                <a href="#features" class="hero-btn-ghost">
                    <i class="fas fa-play-circle" style="font-size:12px"></i>
                    Explore Features
                </a>
            </div>
        </div>

        <div class="hero-stats">
            <div>
                <div class="h-stat-val" data-count="500">0</div>
                <div class="h-stat-lbl">Equipment Tracked</div>
            </div>
            <div>
                <div class="h-stat-val" data-count="24">0</div>
                <div class="h-stat-lbl">Active Warehouses</div>
            </div>
            <div>
                <div class="h-stat-val" data-count="99">0</div>
                <div class="h-stat-lbl">System Uptime %</div>
            </div>
            <div>
                <div class="h-stat-val" data-count="12">0</div>
                <div class="h-stat-lbl">System Modules</div>
            </div>
        </div>

        <div class="scroll-hint">
            <i class="fas fa-chevron-down" style="font-size:10px"></i>
            Scroll
        </div>
    </section>


    {{-- ════ FEATURES ════ --}}
    <section class="section features-section" id="features">
        <div class="features-header">
            <div>
                <div class="section-tag">Core Features</div>
                <h2 class="section-title">EVERYTHING YOU NEED TO RUN YOUR GYM</h2>
            </div>
            <p class="section-sub">From serialized product tracking to supplier management — GYMNASTHENIOX handles every
                layer of your gym operations.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-barcode"></i></div>
                <h3>Barcode Scanning</h3>
                <p>Scan gym equipment and inventory items instantly. Real-time barcode lookup with full product history
                    and status tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                <h3>Inventory Management</h3>
                <p>Complete visibility over your stock levels, serialized products, and warehouse locations across
                    multiple branches.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-invoice"></i></div>
                <h3>Purchase Orders</h3>
                <p>Create, approve, and track purchase orders from request to delivery. Full audit trail for every
                    transaction.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-truck"></i></div>
                <h3>Supplier Management</h3>
                <p>Manage all your equipment suppliers, their product catalogs, and order history from a single
                    dashboard.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-dumbbell"></i></div>
                <h3>Gym Equipment Registry</h3>
                <p>Dedicated module for gym equipment tracking — condition status, maintenance history, and location
                    assignment.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Reports & Analytics</h3>
                <p>Daily, weekly, monthly and yearly reports. Export-ready data to make informed decisions for your
                    business.</p>
            </div>
        </div>
    </section>


    {{-- ════ MODULES ════ --}}
    <section class="section modules-section" id="modules">
        <div class="section-tag">System Modules</div>
        <h2 class="section-title">BUILT FOR EVERY CORNER<br>OF YOUR OPERATIONS</h2>
        <p class="section-sub">Twelve interconnected modules covering every aspect of gym inventory and operations
            management.</p>

        <div class="modules-grid">
            <div class="module-item">
                <span class="module-num">01</span>
                <div class="module-content">
                    <h4>Dashboard & Analytics</h4>
                    <p>Real-time overview of inventory health, pending orders, and key performance metrics.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">02</span>
                <div class="module-content">
                    <h4>Gym Equipment Management</h4>
                    <p>Track every piece of gym equipment — status, location, serial numbers, and maintenance logs.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">03</span>
                <div class="module-content">
                    <h4>Purchase Request & Orders</h4>
                    <p>Multi-level approval workflow for purchase requests with automated PO generation.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">04</span>
                <div class="module-content">
                    <h4>Serialized Products</h4>
                    <p>Individual tracking for every serialized item with full lifecycle visibility from intake to
                        disposal.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">05</span>
                <div class="module-content">
                    <h4>Supplier & Retailer Portal</h4>
                    <p>Manage supplier catalogs and handle retailer orders from one integrated interface.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">06</span>
                <div class="module-content">
                    <h4>Warehouse Management</h4>
                    <p>Multi-warehouse support with zone-based storage, transfer records, and capacity monitoring.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">07</span>
                <div class="module-content">
                    <h4>Manpower Management</h4>
                    <p>Manage coaches and gym staff records, schedules, and role assignments.</p>
                </div>
            </div>
            <div class="module-item">
                <span class="module-num">08</span>
                <div class="module-content">
                    <h4>Reports & Exports</h4>
                    <p>Generate daily, weekly, monthly, quarterly and strategic reports with one-click export.</p>
                </div>
            </div>
        </div>
    </section>


    {{-- ════ HOW IT WORKS ════ --}}
    <section class="section steps-section" id="how-it-works">
        <div class="section-tag">How It Works</div>
        <h2 class="section-title">FROM LOGIN TO FULL CONTROL<br>IN FOUR STEPS</h2>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <h4>Log In Securely</h4>
                <p>Authenticate with your credentials and PIN. Role-based access ensures the right people see the right
                    data.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h4>View Your Dashboard</h4>
                <p>Instantly see inventory alerts, pending purchase orders, and equipment status at a glance.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h4>Scan & Update</h4>
                <p>Use barcode scanning to update stock levels, receive deliveries, and track serialized items in real
                    time.</p>
            </div>
            <div class="step-card">
                <div class="step-num">4</div>
                <h4>Generate Reports</h4>
                <p>Review performance data, approve requests, and export reports for stakeholder review.</p>
            </div>
        </div>
    </section>


    {{-- ════ CTA ════ --}}
    <section class="cta-section">
        <h2>READY TO TAKE CONTROL?</h2>
        <p>Sign in to your GYMNASTHENIOX account and manage your gym inventory today.</p>
        <a href="{{ url('login') }}" class="cta-btn">
            <i class="fas fa-sign-in-alt"></i>
            ACCESS THE SYSTEM
        </a>
    </section>


    {{-- ════ FOOTER ════ --}}
    <footer>
        <div class="footer-brand">
            <div class="footer-logo">G</div>
            <span class="footer-name">GYMNASTHENIOX</span>
        </div>
        <span class="footer-copy">&copy; {{ date('Y') }} Gymnastheniox. All rights reserved.</span>
        <a href="{{ url('login') }}" class="footer-link">Sign In →</a>
    </footer>


    <script>
        gsap.registerPlugin(ScrollTrigger);

        // ── HERO ENTRANCE ─────────────────────────────────────────────────
        window.addEventListener('DOMContentLoaded', () => {
            const tl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });

            tl.to('.hero-badge', {
                    opacity: 1,
                    y: 0,
                    duration: 0.7
                }, 0.2)
                .to('.hero-title', {
                    opacity: 1,
                    y: 0,
                    duration: 0.9
                }, 0.4)
                .to('.hero-sub', {
                    opacity: 1,
                    y: 0,
                    duration: 0.7
                }, 0.7)
                .to('.hero-actions', {
                    opacity: 1,
                    y: 0,
                    duration: 0.7
                }, 0.9)
                .to('.hero-stats', {
                    opacity: 1,
                    y: 0,
                    duration: 0.7
                }, 1.1);

            // Stat counters
            const sfx = {
                500: '+',
                24: '+',
                99: '%',
                12: '+'
            };
            document.querySelectorAll('.h-stat-val[data-count]').forEach(el => {
                const t = +el.getAttribute('data-count');
                gsap.to({
                    v: 0
                }, {
                    v: t,
                    duration: 1.8,
                    delay: 1.1,
                    ease: 'power2.out',
                    onUpdate: function() {
                        el.textContent = Math.round(this.targets()[0].v) + (sfx[t] || '');
                    }
                });
            });
        });

        // ── SCROLL ANIMATIONS ─────────────────────────────────────────────
        // Feature cards
        gsap.utils.toArray('.feature-card').forEach((card, i) => {
            gsap.to(card, {
                opacity: 1,
                y: 0,
                duration: 0.6,
                delay: (i % 3) * 0.1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 88%'
                }
            });
        });

        // Module items
        gsap.utils.toArray('.module-item').forEach((item, i) => {
            gsap.to(item, {
                opacity: 1,
                x: 0,
                duration: 0.6,
                delay: (i % 2) * 0.12,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: item,
                    start: 'top 90%'
                }
            });
        });

        // Step cards
        gsap.utils.toArray('.step-card').forEach((card, i) => {
            gsap.to(card, {
                opacity: 1,
                y: 0,
                duration: 0.6,
                delay: i * 0.1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: '.steps-grid',
                    start: 'top 80%'
                }
            });
        });

        // CTA
        gsap.to('.cta-section h2', {
            opacity: 1,
            y: 0,
            duration: 0.7,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            }
        });
        gsap.to('.cta-section p', {
            opacity: 1,
            y: 0,
            duration: 0.6,
            delay: 0.15,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            }
        });
        gsap.to('.cta-btn', {
            opacity: 1,
            scale: 1,
            duration: 0.6,
            delay: 0.3,
            ease: 'back.out(1.5)',
            scrollTrigger: {
                trigger: '.cta-section',
                start: 'top 80%'
            }
        });

        // Nav scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 60) {
                nav.style.boxShadow = '0 2px 20px rgba(15,15,26,0.08)';
            } else {
                nav.style.boxShadow = 'none';
            }
        });
    </script>
</body>

</html>
