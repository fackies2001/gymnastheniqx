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
|   PIN CODE MODAL
|========================= --}}
@if (Auth::check() && !session()->has('pin_verified'))
    <div class="modal fade" id="pincodeModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; border: none;">
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
                        <p class="text-muted mb-4">Please enter your 4-digit PIN to access the dashboard</p>
                    @else
                        <p class="text-muted mb-4">Create a 4-digit PIN to secure your account</p>
                    @endif

                    <form id="pincodeForm" method="POST" action="{{ route('user.verify.pin') }}">
                        @csrf
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <input type="text" class="pin-digit form-control text-center" maxlength="1"
                                name="pin[]"
                                style="width: 60px; height: 60px; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 10px;"
                                required autocomplete="off" inputmode="numeric">
                            <input type="text" class="pin-digit form-control text-center" maxlength="1"
                                name="pin[]"
                                style="width: 60px; height: 60px; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 10px;"
                                required autocomplete="off" inputmode="numeric">
                            <input type="text" class="pin-digit form-control text-center" maxlength="1"
                                name="pin[]"
                                style="width: 60px; height: 60px; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 10px;"
                                required autocomplete="off" inputmode="numeric">
                            <input type="text" class="pin-digit form-control text-center" maxlength="1"
                                name="pin[]"
                                style="width: 60px; height: 60px; font-size: 24px; font-weight: bold; border: 2px solid #667eea; border-radius: 10px;"
                                required autocomplete="off" inputmode="numeric">
                        </div>

                        <button type="submit" id="savePincodeBtn" class="btn btn-primary btn-lg btn-block"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; font-weight: bold;">
                            <i class="fas fa-lock mr-2"></i>
                            @if (Auth::user()->pin)
                                Verify PIN
                            @else
                                Save PIN & Continue
                            @endif
                        </button>
                    </form>

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
                </div>
            </div>
        </div>
    </div>

    <style>
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
    </style>
@endif

{{-- =========================
|   SESSION TIMEOUT MODAL
|========================= --}}
@auth
    <div class="modal fade" id="sessionWarningModal" tabindex="-1" role="dialog" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px 15px 0 0; border: none;">
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
@endauth

{{-- =========================
|   CSS
|========================= --}}
@section('css')
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        /* ============================================
                                                               USER DROPDOWN MENU STYLING
                                                            ============================================ */
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

        /* Notification bell styling */
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

        /* ============================================
                                                               DARK MODE
                                                            ============================================ */
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

        #sessionWarningModal .modal-dialog {
            z-index: 100000;
        }

        .modal-backdrop {
            z-index: 99998 !important;
        }
    </style>
    @stack('css')
@stop

{{-- =========================
|   JAVASCRIPT
|========================= --}}
@section('js')
    @vite('resources/js/app.js')
    @vite('resources/js/pincode_LOCKED.js')
    @stack('js')

    {{-- ✅ Badge pulse animation --}}
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

    {{-- ✅ DARK MODE — prevent flash, runs immediately --}}
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>

    {{-- ✅ USER DROPDOWN MENU HANDLER --}}
    <script>
        $(document).ready(function() {
            // Logout is natively handled in menu-item-dropdown-user-menu.blade.php
            // No JS needed here.
        });
    </script>

    {{-- ✅ PIN MODAL INITIALIZATION --}}
    <script>
        $(document).ready(function() {
            @if (!session()->has('pin_verified'))
                console.log('[PIN] Initializing modal...');

                $('#pincodeModal').modal('show');

                $('#pincodeModal').on('hide.bs.modal', function(e) {
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

                console.log('[PIN] Modal initialized ✅');
            @endif
        });
    </script>

    {{-- ✅ SESSION TIMEOUT SCRIPT --}}
    @auth
        <script>
            $(document).ready(function() {
                // Inject modal HTML into body dynamically
                $('body').append(`
                    <div class="modal fade" id="sessionWarningModal" tabindex="-1" role="dialog" style="z-index:99999;">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content" style="border-radius:15px;border:none;box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                                <div class="modal-header" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);border-radius:15px 15px 0 0;border:none;">
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
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="fas fa-sign-out-alt mr-1"></i> Logout Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                const SESSION_MINUTES = 2;
                const WARNING_SECONDS = 60;
                const sessionMs = SESSION_MINUTES * 60 * 1000;
                const warningMs = WARNING_SECONDS * 1000;

                let warnTimer, logoutTimer, countdownInterval;

                function resetTimers() {
                    clearTimeout(warnTimer);
                    clearTimeout(logoutTimer);
                    clearInterval(countdownInterval);
                    warnTimer = setTimeout(showWarning, sessionMs - warningMs);
                    logoutTimer = setTimeout(autoLogout, sessionMs);
                }

                function showWarning() {
                    let secs = WARNING_SECONDS;
                    $('#countdown').text(secs);
                    $('#sessionWarningModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#sessionWarningModal').modal('show');

                    countdownInterval = setInterval(function() {
                        secs--;
                        $('#countdown').text(secs);
                        if (secs <= 0) clearInterval(countdownInterval);
                    }, 1000);
                }

                function autoLogout() {
                    clearInterval(countdownInterval);
                    $('#sessionWarningModal form').submit();
                }

                $('#stayLoggedIn').on('click', function() {
                    fetch('/keep-alive', {
                            method: 'GET'
                        })
                        .then(function() {
                            $('#sessionWarningModal').modal('hide');
                            clearInterval(countdownInterval);
                            resetTimers();
                        });
                });

                ['click', 'mousemove', 'keypress', 'scroll'].forEach(function(evt) {
                    document.addEventListener(evt, resetTimers);
                });

                resetTimers();
            });
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
                    container.innerHTML = `
                        <div class="text-center text-muted py-4 px-3">
                            <i class="fas fa-bell-slash mb-2"
                               style="font-size:1.8rem; opacity:0.35; display:block;"></i>
                            <span style="font-size:0.85rem;">No new notifications</span>
                        </div>`;
                    return;
                }

                let html = '';
                notifications.forEach(n => {
                    html += `
                        <a href="${esc(n.url || '#')}"
                           class="dropdown-item notif-item d-flex align-items-start py-2 px-3 unread"
                           data-notif-id="${esc(n.id)}"
                           style="border-bottom:1px solid rgba(0,0,0,0.05);
                                  white-space:normal; cursor:pointer;">
                            <div class="mr-2 mt-1" style="min-width:28px; text-align:center;">
                                <i class="${esc(n.icon || 'fas fa-bell text-info')}"
                                   style="font-size:1rem;"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.8rem; font-weight:600; line-height:1.3;">
                                    ${getActionLabel(n.type, n.action)}
                                </div>
                                <div style="font-size:0.78rem; margin-top:2px;
                                            opacity:0.85; line-height:1.3;">
                                    ${esc(n.message)}
                                </div>
                                <div style="font-size:0.7rem; color:#999; margin-top:3px;">
                                    <i class="far fa-clock mr-1"></i>${esc(n.time_ago)}
                                    <span style="font-size:0.65rem; margin-left:4px;">
                                        ${esc(n.time)}
                                    </span>
                                </div>
                            </div>
                        </a>`;
                });

                html += `
                    <div class="dropdown-divider my-0"></div>
                    <a href="#" id="markAllReadBtn"
                       class="dropdown-item text-center py-2"
                       style="font-size:0.8rem; color:#667eea; font-weight:700;">
                        <i class="fas fa-check-double mr-1"></i> Mark all as read
                    </a>`;

                container.innerHTML = html;

                container.querySelectorAll('.notif-item[data-notif-id]').forEach(el => {
                    el.addEventListener('click', () => markAsRead(el.dataset.notifId, el));
                });

                const markAllBtn = document.getElementById('markAllReadBtn');
                if (markAllBtn) {
                    markAllBtn.addEventListener('click', e => {
                        e.preventDefault();
                        markAllAsRead();
                    });
                }
            }

            function markAsRead(id, el) {
                fetch(`/notifications/read/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(() => {
                        if (el) {
                            el.classList.remove('unread');
                            el.style.opacity = '0.55';
                        }
                        fetchNotifications();
                    })
                    .catch(err => console.warn('[Bell] Mark read error:', err));
            }

            function markAllAsRead() {
                fetch('/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(() => fetchNotifications())
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

            function init() {
                startPolling();
            }

            document.readyState === 'loading' ?
                document.addEventListener('DOMContentLoaded', init) :
                init();

        })();
    </script>
@stop
