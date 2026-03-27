{{-- 📁 resources/views/layouts/adminlte.blade.php --}}
@extends('adminlte::page')

{{-- =========================
|   HEAD
|========================= --}}
@section('adminlte_head')
    @if (session()->has('sanctum_token'))
        <meta name="api-token" content="{{ session('sanctum_token') }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

{{-- =========================
|   TITLE
|========================= --}}
@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle')
        - @yield('subtitle')
    @endif
@stop

{{-- =========================
|   CONTENT HEADER
|========================= --}}
@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted">
            @yield('content_header_title')
            @hasSection('content_header_subtitle')
                <small class="text-dark">
                    <i class="fas fa-xs fa-angle-right text-muted"></i>
                    @yield('content_header_subtitle')
                </small>
            @endif
        </h1>
    @endif
@stop

{{-- =========================
|   MAIN CONTENT
|========================= --}}
@section('content')
    @yield('content_body')
@stop

{{-- =========================
|   CSS
|========================= --}}
@section('css')
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        /* PIN MODAL */
        .pin-digit:focus {
            border-color: #764ba2 !important;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25) !important;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        body.pin-modal-active {
            overflow: hidden;
        }

        /* USER DROPDOWN */
        .user-menu-dropdown {
            min-width: 280px !important;
        }

        .user-menu-dropdown .dropdown-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
        }

        .user-menu-dropdown .dropdown-header img {
            width: 60px;
            height: 60px;
            border: 3px solid white;
            margin-bottom: 10px;
        }

        .user-menu-dropdown .dropdown-item {
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .user-menu-dropdown .dropdown-item:hover {
            background-color: rgba(102, 126, 234, 0.1);
            padding-left: 25px;
        }

        .user-menu-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .user-menu-dropdown .dropdown-divider {
            margin: 5px 0;
        }

        /* NOTIFICATION BELL */
        #notificationBellWrapper .nav-link {
            color: inherit;
        }

        #notificationBellWrapper .nav-link:hover {
            color: #667eea !important;
        }

        #notifBadge {
            animation: none;
        }

        .notif-item:hover {
            background-color: rgba(102, 126, 234, 0.08) !important;
        }

        .notif-item.unread {
            background-color: rgba(102, 126, 234, 0.05);
            border-left: 3px solid #667eea !important;
        }

        /* DARK MODE */
        body.dark-mode,
        body.dark-mode .wrapper {
            background-color: #1a1a2e !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .content-wrapper {
            background-color: #1a1a2e !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .main-header,
        body.dark-mode .navbar-white {
            background-color: #16213e !important;
            border-bottom-color: #2a3f5f !important;
        }

        body.dark-mode .navbar-light .navbar-nav .nav-link,
        body.dark-mode .main-header .navbar-nav .nav-link {
            color: #e2e8f0 !important;
        }

        body.dark-mode .main-sidebar,
        body.dark-mode .sidebar {
            background-color: #16213e !important;
        }

        body.dark-mode .brand-link {
            background-color: #16213e !important;
            border-bottom-color: #2a3f5f !important;
            color: #fff !important;
        }

        body.dark-mode .brand-text {
            color: #fff !important;
        }

        body.dark-mode .nav-sidebar .nav-link {
            color: #bbc6d4 !important;
        }

        body.dark-mode .nav-sidebar .nav-item.menu-open>.nav-link,
        body.dark-mode .nav-sidebar .nav-link.active {
            background-color: #1e2a45 !important;
            color: #fff !important;
        }

        body.dark-mode .user-panel {
            border-bottom-color: #2a3f5f !important;
        }

        body.dark-mode .user-panel .info a {
            color: #e2e8f0 !important;
        }

        body.dark-mode .card {
            background-color: #1e2a45 !important;
            border-color: #2a3f5f !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .card-header {
            background-color: #162035 !important;
            border-bottom-color: #2a3f5f !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .card-title,
        body.dark-mode .card-body {
            color: #e2e8f0 !important;
        }

        body.dark-mode .card-footer {
            background-color: #162035 !important;
            border-top-color: #2a3f5f !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .table {
            color: #e2e8f0 !important;
        }

        body.dark-mode .table thead th {
            background-color: #162035 !important;
            color: #e2e8f0 !important;
            border-color: #2a3f5f !important;
        }

        body.dark-mode .table td,
        body.dark-mode .table th {
            border-color: #2a3f5f !important;
        }

        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        body.dark-mode .sticky-top.bg-white {
            background-color: #162035 !important;
        }

        body.dark-mode .form-control {
            background-color: #0d1b2e !important;
            border-color: #2a3f5f !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .dropdown-menu {
            background-color: #1e2a45 !important;
            border-color: #2a3f5f !important;
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0 !important;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #2a3f5f !important;
        }

        body.dark-mode h1,
        body.dark-mode h2,
        body.dark-mode h3,
        body.dark-mode h4,
        body.dark-mode h5,
        body.dark-mode h6,
        body.dark-mode p,
        body.dark-mode label {
            color: #e2e8f0 !important;
        }

        body.dark-mode .text-muted {
            color: #8899bb !important;
        }

        body.dark-mode .text-dark {
            color: #e2e8f0 !important;
        }

        body.dark-mode .bg-white,
        body.dark-mode .bg-light {
            background-color: #1e2a45 !important;
        }

        body.dark-mode hr {
            border-color: #2a3f5f !important;
        }

        body.dark-mode .activity-item {
            background-color: #162035 !important;
            border-left-color: #4d9fff !important;
        }

        body.dark-mode .content-header h1 {
            color: #e2e8f0 !important;
        }

        body.dark-mode .breadcrumb {
            background-color: transparent !important;
        }

        body.dark-mode .breadcrumb-item,
        body.dark-mode .breadcrumb-item a {
            color: #bbc6d4 !important;
        }

        body.dark-mode .modal-content {
            background-color: #1e2a45 !important;
            color: #e2e8f0 !important;
            border-color: #2a3f5f !important;
        }

        body.dark-mode .modal-header {
            background-color: #162035 !important;
            border-bottom-color: #2a3f5f !important;
        }

        body.dark-mode .modal-footer {
            background-color: #162035 !important;
            border-top-color: #2a3f5f !important;
        }

        body.dark-mode .close {
            color: #e2e8f0 !important;
        }

        body.dark-mode select.form-control option {
            background-color: #0d1b2e !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .input-group-text {
            background-color: #162035 !important;
            border-color: #2a3f5f !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode #notifDropdown {
            background-color: #1e2a45 !important;
        }

        body.dark-mode .notif-item {
            color: #e2e8f0 !important;
            border-bottom-color: rgba(255, 255, 255, 0.05) !important;
        }

        body.dark-mode .notif-item:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        body.dark-mode .dropdown-divider {
            border-color: #2a3f5f !important;
        }

        body.dark-mode #markAllReadBtn {
            color: #4d9fff !important;
        }

        body.dark-mode .user-menu-dropdown .dropdown-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        }

        /* SESSION MODAL Z-INDEX */
        #sessionWarningModal {
            z-index: 99999 !important;
        }

        /* ✅ SESSION HIJACK MODAL */
        #sessionHijackModal {
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #sessionHijackModal .hijack-box {
            background: white;
            border-radius: 15px;
            padding: 40px 35px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: hijackSlideIn 0.4s ease;
        }

        @keyframes hijackSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        #sessionHijackModal .hijack-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        #sessionHijackModal .hijack-icon i {
            font-size: 32px;
            color: white;
        }

        #sessionHijackModal h4 {
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 10px;
            font-size: 18px;
        }

        #sessionHijackModal p {
            color: #666;
            font-size: 14px;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        #sessionHijackModal .hijack-warning {
            background: #fff8f8;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #c53030;
        }

        #sessionHijackModal .btn-hijack-logout {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px 30px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: opacity 0.2s;
        }

        #sessionHijackModal .btn-hijack-logout:hover {
            opacity: 0.9;
        }
    </style>
    @stack('css')
@stop

{{-- =========================
|   JAVASCRIPT
|========================= --}}
@section('js')

    {{-- ✅ PIN CODE MODAL --}}
    @auth
        <div class="modal fade" id="pincodeModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:15px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                    <div class="modal-header"
                        style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius:15px 15px 0 0; border:none;">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="fas fa-lock mr-2"></i>
                            @if (Auth::user()->pin)
                                Enter Your PIN to Continue
                            @else
                                Set Your Security PIN
                            @endif
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        @if (Auth::user()->pin)
                            <p class="text-muted mb-4">Please enter your 6-digit PIN to access the dashboard</p>
                        @else
                            <p class="text-muted mb-4">Create a 6-digit PIN to secure your account</p>
                        @endif

                        <form id="pincodeForm" method="POST"
                            action="{{ Auth::user()->pin ? route('user.verify.pin') : route('user.update.pin') }}">
                            @csrf
                            @if (!Auth::user()->pin)
                                @method('PUT')
                            @endif

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                @for ($i = 0; $i < 6; $i++)
                                    <input type="password" class="pin-digit form-control text-center" name="pin[]"
                                        style="width:55px; height:55px; font-size:22px; font-weight:bold; border:2px solid #667eea; border-radius:10px;"
                                        required autocomplete="off" inputmode="numeric">
                                @endfor
                            </div>

                            <button type="submit" id="savePincodeBtn" class="btn btn-primary btn-lg btn-block"
                                style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border:none; border-radius:10px; font-weight:bold;">
                                <i class="fas fa-lock mr-2"></i>
                                @if (Auth::user()->pin)
                                    Verify PIN
                                @else
                                    Save PIN & Continue
                                @endif
                            </button>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    @if (Auth::user()->pin)
                                        Forgot PIN? Contact administrator
                                    @else
                                        You'll need this PIN every time you log in
                                    @endif
                                </small>
                            </div>

                            <p class="mt-2 text-muted" style="font-size:0.75rem;">
                                <i class="fas fa-info-circle mr-1"></i>
                                This modal cannot be closed until you complete the action.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ SESSION TIMEOUT MODAL --}}
        <div class="modal fade" id="sessionWarningModal" tabindex="-1" role="dialog" style="z-index:99999;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius:15px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                    <div class="modal-header"
                        style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius:15px 15px 0 0; border:none;">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Session Expiring Soon!
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-1">Your session will expire in</p>
                        <h2 class="font-weight-bold text-danger"><span id="countdown">60</span>s</h2>
                        <p class="text-muted small">Click "Stay Logged In" to continue your session.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-success px-4" id="stayLoggedIn">
                            <i class="fas fa-check mr-1"></i> Stay Logged In
                        </button>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ SESSION HIJACK MODAL --}}
        <div id="sessionHijackModal" style="display:none;">
            <div class="hijack-box">
                <div class="hijack-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Account Accessed on Another Device!</h4>
                <p>
                    Your account has been logged in from another device or browser.
                    For your security, you will be redirected to the login page.
                </p>
                <div class="hijack-warning">
                    <i class="fas fa-info-circle mr-1"></i>
                    If this wasn't you, please change your password immediately.
                </div>
                <button class="btn-hijack-logout" onclick="window.location.href='/logout-forced'">
                    <i class="fas fa-sign-out-alt mr-2"></i> OK, Logout Me
                </button>
            </div>
        </div>
    @endauth

    @vite('resources/js/app.js')
    @vite('resources/js/pincode_LOCKED.js')
    @stack('js')

    {{-- ✅ BADGE PULSE ANIMATION --}}
    <style>
        @keyframes notifPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.4);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>

    {{-- ✅ DARK MODE — prevent flash --}}
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>

    {{-- ✅ PIN MODAL INITIALIZATION --}}
    <script>
        $(document).ready(function() {
            @if (session('show_pin_modal'))
                console.log('[PIN] Initializing modal... mode: {{ session('pin_mode') }}');

                var $modal = $('#pincodeModal');
                if ($modal.length === 0) {
                    console.warn('[PIN] #pincodeModal not in DOM');
                    return;
                }

                $modal.modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });

                $modal.on('hide.bs.modal', function(e) {
                    e.preventDefault();
                    return false;
                });

                document.body.classList.add('pin-modal-active');

                $(document).on('keydown.pinmodal', function(e) {
                    if (e.keyCode === 27) {
                        e.preventDefault();
                        return false;
                    }
                });

                window.history.pushState(null, '', window.location.href);
                window.onpopstate = function() {
                    window.history.pushState(null, '', window.location.href);
                };

                setTimeout(function() {
                    $('.pin-digit').first().focus();
                }, 400);

                console.log('[PIN] Modal initialized ✅');
            @endif
        });
    </script>

    {{-- ✅ SESSION TIMEOUT — 2 minutes inactivity --}}
    @auth
        <script>
            (function() {
                'use strict';

                const INACTIVITY_LIMIT = 2 * 60 * 1000;
                const COUNTDOWN_SECONDS = 60;

                let inactivityTimer = null;
                let countdownTimer = null;
                let secondsLeft = COUNTDOWN_SECONDS;
                let warningShown = false;

                // ✅ DAGDAG — declare dito para accessible sa buong IIFE
                let hijackDetected = false;
                let hijackCheckInterval = null;

                function resetInactivityTimer() {
                    if (warningShown) return;
                    clearTimeout(inactivityTimer);
                    inactivityTimer = setTimeout(showWarning, INACTIVITY_LIMIT);
                }

                function showWarning() {
                    warningShown = true;
                    secondsLeft = COUNTDOWN_SECONDS;
                    $('#sessionWarningModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#sessionWarningModal').modal('show');
                    updateCountdownDisplay();
                    startCountdown();
                }

                function startCountdown() {
                    clearInterval(countdownTimer);
                    countdownTimer = setInterval(function() {
                        secondsLeft--;
                        updateCountdownDisplay();
                        if (secondsLeft <= 0) {
                            clearInterval(countdownTimer);
                            autoLogout();
                        }
                    }, 1000);
                }

                function updateCountdownDisplay() {
                    var el = document.getElementById('countdown');
                    if (el) el.textContent = secondsLeft;
                }

                function autoLogout() {
                    var form = document.querySelector('#sessionWarningModal form[action*="logout"]');
                    if (form) {
                        form.submit();
                    } else {
                        window.location.href = '/logout-forced';
                    }
                }

                function stayLoggedIn() {
                    clearInterval(countdownTimer);
                    warningShown = false;
                    $('#sessionWarningModal').modal('hide');
                    fetch('/keep-alive', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }).catch(() => {});
                    resetInactivityTimer();
                }

                document.getElementById('stayLoggedIn')?.addEventListener('click', stayLoggedIn);

                ['mousemove', 'mousedown', 'keydown', 'touchstart', 'touchmove', 'scroll', 'click'].forEach(function(evt) {
                    document.addEventListener(evt, resetInactivityTimer, {
                        passive: true
                    });
                });

                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        clearTimeout(inactivityTimer);
                    } else {
                        if (!warningShown) resetInactivityTimer();
                    }
                });

                resetInactivityTimer();
                console.log('[Session] Inactivity timer started ✅ (2 min limit)');

                // ============================================================
                // ✅ SESSION HIJACK DETECTION — poll every 5 seconds
                // Kapag nag-login ang ibang device, lalabas ang modal dito
                // ============================================================
                let failCount = 0; // ← dagdag mo ito

                function checkSessionValidity() {
                    if (hijackDetected) return;

                    fetch('/check-session-status', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                            }
                        })
                        .then(r => {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(data => {
                            if (!data.valid) {
                                failCount++;
                                // ✅ 3 consecutive failures before mag-trigger
                                if (failCount >= 3) {
                                    hijackDetected = true;
                                    clearInterval(hijackCheckInterval);
                                    showHijackModal();
                                }
                            } else {
                                failCount = 0; // ✅ i-reset kapag valid
                            }
                        })
                        .catch(() => {
                            // ✅ network error — hindi counted as hijack
                            console.warn('[Session] Check failed, ignoring...');
                        });
                }

                function showHijackModal() {
                    // ✅ Itago ang lahat ng ibang modal
                    $('#sessionWarningModal').modal('hide');
                    $('#pincodeModal').modal('hide');

                    // ✅ Ipakita ang hijack modal
                    const modal = document.getElementById('sessionHijackModal');
                    if (modal) {
                        modal.style.display = 'flex';
                    }

                    console.warn('[Session] ⚠️ Account accessed from another device!');
                }

                // ✅ Start polling every 5 seconds
                hijackCheckInterval = setInterval(checkSessionValidity, 5000);
                console.log('[Session] Hijack detection started ✅');
            })
            ();
        </script>
    @endauth

    {{-- ✅ NOTIFICATION BELL --}}
    <script>
        (function() {
            'use strict';

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const POLL_INTERVAL = 30000;
            let pollTimer = null;

            function fetchNotifications() {
                fetch('/notifications/get', {
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        updateBadge(data.count ?? 0);
                        renderNotifications(data.notifications ?? []);
                    })
                    .catch(err => {
                        console.warn('[Bell] Fetch error:', err);
                        renderNotifications([]);
                    });
            }

            function updateBadge(count) {
                const badge = document.getElementById('notifBadge');
                const label = document.getElementById('notifCountLabel');
                if (!badge) return;
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                    badge.style.animation = 'none';
                    badge.offsetHeight;
                    badge.style.animation = 'notifPulse 0.4s ease';
                } else {
                    badge.style.display = 'none';
                }
                if (label) label.textContent = count + ' unread';
            }

            function renderNotifications(notifications) {
                const container = document.getElementById('notifItemsContainer');
                if (!container) return;
                if (!notifications.length) {
                    container.innerHTML = `<div class="text-center text-muted py-4 px-3">
                        <i class="fas fa-bell-slash mb-2" style="font-size:1.8rem;opacity:0.35;display:block;"></i>
                        <span style="font-size:0.85rem;">No new notifications</span></div>`;
                    return;
                }
                let html = '';
                notifications.forEach(n => {
                    html += `<a href="${esc(n.url||'#')}" class="dropdown-item notif-item d-flex align-items-start py-2 px-3 unread"
                        data-notif-id="${esc(n.id)}" style="border-bottom:1px solid rgba(0,0,0,0.05);white-space:normal;cursor:pointer;">
                        <div class="mr-2 mt-1" style="min-width:28px;text-align:center;">
                            <i class="${esc(n.icon||'fas fa-bell text-info')}" style="font-size:1rem;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.8rem;font-weight:600;line-height:1.3;">${getActionLabel(n.type,n.action)}</div>
                            <div style="font-size:0.78rem;margin-top:2px;opacity:0.85;line-height:1.3;">${esc(n.message)}</div>
                            <div style="font-size:0.7rem;color:#999;margin-top:3px;">
                                <i class="far fa-clock mr-1"></i>${esc(n.time_ago)}
                                <span style="font-size:0.65rem;margin-left:4px;">${esc(n.time)}</span>
                            </div>
                        </div></a>`;
                });
                html += `<div class="dropdown-divider my-0"></div>
                    <a href="#" id="markAllReadBtn" class="dropdown-item text-center py-2"
                        style="font-size:0.8rem;color:#667eea;font-weight:700;">
                        <i class="fas fa-check-double mr-1"></i> Mark all as read</a>`;
                container.innerHTML = html;
                container.querySelectorAll('.notif-item[data-notif-id]').forEach(el => {
                    el.addEventListener('click', () => markAsRead(el.dataset.notifId, el));
                });
                const markAllBtn = document.getElementById('markAllReadBtn');
                if (markAllBtn) markAllBtn.addEventListener('click', e => {
                    e.preventDefault();
                    markAllAsRead();
                });
            }

            function markAsRead(id, el) {
                fetch(`/notifications/read/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(() => {
                    if (el) {
                        el.classList.remove('unread');
                        el.style.opacity = '0.55';
                    }
                    fetchNotifications();
                }).catch(err => console.warn('[Bell] Mark read error:', err));
            }

            function markAllAsRead() {
                fetch('/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()).then(() => fetchNotifications())
                    .catch(err => console.warn('[Bell] Mark all read error:', err));
            }

            function getActionLabel(type, action) {
                const map = {
                    purchase_request: {
                        created: '📋 New Purchase Request',
                        approved: '✅ PR Approved',
                        rejected: '❌ PR Rejected'
                    },
                    purchase_order: {
                        created: '🛒 New Purchase Order',
                        completed: '📦 PO Completed'
                    },
                    retailer_order: {
                        created: '🛍️ New Retailer Order',
                        approved: '✅ Order Approved',
                        rejected: '❌ Order Rejected',
                        completed: '📦 Order Shipped'
                    },
                };
                return map[type]?.[action] ?? '🔔 New Notification';
            }

            function esc(str) {
                const d = document.createElement('div');
                d.appendChild(document.createTextNode(str ?? ''));
                return d.innerHTML;
            }

            function startPolling() {
                fetchNotifications();
                clearInterval(pollTimer);
                pollTimer = setInterval(fetchNotifications, POLL_INTERVAL);
            }

            document.addEventListener('visibilitychange', () => {
                document.hidden ? clearInterval(pollTimer) : startPolling();
            });

            document.readyState === 'loading' ?
                document.addEventListener('DOMContentLoaded', startPolling) : startPolling();
        })();
    </script>
@stop
