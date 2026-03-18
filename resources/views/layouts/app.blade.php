@extends('adminlte::page')

{{-- ========================= --}}
@section('adminlte_head')
    @if (session()->has('sanctum_token'))
        <meta name="api-token" content="{{ session('sanctum_token') }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

{{-- ========================= --}}
@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle')
        - @yield('subtitle')
    @endif
@stop

{{-- ========================= --}}
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

{{-- ========================= --}}
@section('content')
    @yield('content_body')

    {{-- Shared Modals --}}
    <x-bootstrap.pincode />

    {{-- ✅ SESSION HIJACKED MODAL (kapag na-redirect mula sa ibang device) --}}
    @if (session('session_hijacked'))
        <div id="hijackModal"
            style="
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.65);
        display: flex; align-items: center; justify-content: center;">
            <div
                style="
            background: #fff; border-radius: 12px;
            padding: 2.5rem; max-width: 420px; width: 100%;
            text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
                <div style="font-size: 3rem;">⚠️</div>
                <h5 style="margin-top: 1rem; font-weight: 700;">Session Terminated</h5>
                <p style="color: #6c757d;">
                    Your account was logged in on another device.
                    You have been logged out for security.
                </p>
                <button onclick="document.getElementById('hijackModal').remove()"
                    style="background:#dc3545; color:#fff; border:none;
                padding: 0.5rem 1.5rem; border-radius: 8px;
                font-weight: 600; cursor: pointer;">
                    OK, Got it
                </button>
            </div>
        </div>
    @endif

    {{-- ✅ REAL-TIME SESSION WARNING MODAL --}}
    @auth
        <div id="sessionWarningModal"
            style="
    display: none !important; position: fixed; inset: 0; z-index: 99999;
        background: rgba(0,0,0,0.65);
        align-items: center; justify-content: center;">
            <div
                style="
            background: #fff; border-radius: 12px;
            padding: 2.5rem; max-width: 420px; width: 100%;
            text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
                <div style="font-size: 3rem;">🔐</div>
                <h5 style="margin-top: 1rem; font-weight: 700;">Account Logged In Elsewhere</h5>
                <p style="color: #6c757d;">Your account has been accessed on another device.</p>
                <p style="color: #6c757d;">
                    Auto-logout in
                    <span id="sessionCountdown" style="font-weight:700; color:#dc3545;">10</span>
                    seconds.
                </p>
                <button onclick="forceLogout()"
                    style="
                background:#dc3545; color:#fff; border:none;
                padding: 0.5rem 1.5rem; border-radius: 8px;
                font-weight: 600; cursor: pointer; margin-top: 0.5rem;">
                    Logout Now
                </button>
            </div>
        </div>
    @endauth
@stop

{{-- ========================= --}}
@section('css')
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        .navbar-nav .nav-item.dropdown.user-menu .dropdown-toggle {
            padding: 0.5rem 1rem;
        }

        .navbar-nav .dropdown-menu .dropdown-header {
            display: none !important;
        }

        .user-panel .info {
            padding-left: 10px;
            white-space: normal;
            max-width: 150px;
        }

        .user-panel .info a {
            font-size: 14px;
            font-weight: 500;
        }

        .user-panel .info small {
            line-height: 1.5;
            display: block;
            margin-top: 2px;
        }

        .user-panel .image img {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .navbar-nav .user-menu .user-image {
            width: 30px;
            height: 30px;
            object-fit: cover;
        }
    </style>
    @stack('css')
@stop

{{-- ========================= --}}
@section('js')
    {{-- 1. VITE APP.JS FIRST --}}
    @vite('resources/js/app.js')

    {{-- 2. STACK JS --}}
    @stack('js')

    {{-- 3. PINCODE JS --}}
    @vite('resources/js/pincode_LOCKED.js')

    {{-- 4. PIN MODAL LOGIC --}}
    <script>
        (function() {
            'use strict';

            function initPinModal() {
                const shouldShowPinModal = {{ session('show_pin_modal') ? 'true' : 'false' }};
                if (!shouldShowPinModal) return;

                const hasPinInDatabase =
                    {{ auth()->user() && auth()->user()->employee && auth()->user()->employee->pin ? 'true' : 'false' }};
                const form = document.getElementById('pincodeForm');
                const modalElement = document.getElementById('pincodeModal');

                if (!form || !modalElement) {
                    console.warn('PIN modal elements not found');
                    return;
                }

                const modalTitle = modalElement.querySelector('.modal-title');
                const methodInput = form.querySelector('input[name="_method"]');

                if (hasPinInDatabase) {
                    if (modalTitle) modalTitle.innerText = "Enter PIN Code";
                    form.action = "{{ route('user.verify.pin') }}";
                    if (methodInput) methodInput.value = "POST";
                } else {
                    if (modalTitle) modalTitle.innerText = "Set New PIN Code";
                    form.action = "{{ route('user.update.pin') }}";
                    if (methodInput) methodInput.value = "PUT";
                }

                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#pincodeModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                } else {
                    console.error('jQuery or Bootstrap modal not available');
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPinModal);
            } else {
                if (typeof $ !== 'undefined') {
                    $(document).ready(initPinModal);
                } else {
                    initPinModal();
                }
            }

            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:load', initPinModal);
                document.addEventListener('livewire:navigated', initPinModal);
            }
        })();
    </script>

    {{-- 5. ✅ SINGLE DEVICE LOGIN — Polling Script --}}
    @auth
        <script>
            let sessionWarningShown = false;

            function forceLogout() {
                window.location.href = '/logout-forced';
            }

            function startSessionCountdown() {
                let s = 10;
                document.getElementById('sessionCountdown').textContent = s;
                const interval = setInterval(() => {
                    s--;
                    document.getElementById('sessionCountdown').textContent = s;
                    if (s <= 0) {
                        clearInterval(interval);
                        forceLogout();
                    }
                }, 1000);
            }

            function showSessionWarning() {
                const modal = document.getElementById('sessionWarningModal');
                modal.classList.remove('fade');
                modal.style.cssText =
                    'display: flex !important; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.65); align-items: center; justify-content: center;';
                startSessionCountdown();
            }

            setInterval(async () => {
                if (sessionWarningShown) return;
                try {
                    const res = await fetch('/check-session-status');
                    const data = await res.json();
                    if (!data.valid) {
                        sessionWarningShown = true;
                        showSessionWarning();
                    }
                } catch (e) {}
            }, 5000);
        </script>
    @endauth
@stop
