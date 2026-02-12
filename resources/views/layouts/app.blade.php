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

    {{-- Shared Modals --}}
    <x-bootstrap.pincode />
@stop

{{-- =========================
|   CSS
|========================= --}}
@section('css')
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    {{-- ✅ Custom CSS for User Panel & Navbar --}}
    <style>
        /* Fix double name in navbar dropdown */
        .navbar-nav .nav-item.dropdown.user-menu .dropdown-toggle {
            padding: 0.5rem 1rem;
        }

        /* ✅ HIDE DUPLICATE NAME IN DROPDOWN */
        .navbar-nav .dropdown-menu .dropdown-header {
            display: none !important;
        }

        /* Sidebar user panel improvements */
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

        /* Fix navbar user image */
        .navbar-nav .user-menu .user-image {
            width: 30px;
            height: 30px;
            object-fit: cover;
        }
    </style>

    @stack('css')
@stop

{{-- =========================
|   JAVASCRIPT (FIXED ORDER)
|========================= --}}
@section('js')
    {{-- 
        ✅ 1. VITE APP.JS FIRST (jQuery global setup)
        This makes jQuery available globally for all other scripts
    --}}
    @vite('resources/js/app.js')

    {{-- 
        ✅ 2. STACK JS (DataTables, SweetAlert, etc.)
        Now these can safely use the jQuery from Vite
    --}}
    @stack('js')

    {{-- 
        ✅ 3. PINCODE JS (needs jQuery from Vite)
    --}}
    @vite('resources/js/pincode_LOCKED.js')

    {{-- =========================
    |   PIN MODAL LOGIC (Simplified)
    |========================= --}}
    <script>
        (function() {
            'use strict';

            function initPinModal() {
                // Check if we should show the modal
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

                // Update modal based on PIN existence
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

                // Show modal using jQuery (AdminLTE uses Bootstrap 4)
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

            // Wait for DOM and jQuery to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPinModal);
            } else {
                // DOM already loaded
                if (typeof $ !== 'undefined') {
                    $(document).ready(initPinModal);
                } else {
                    initPinModal();
                }
            }

            // Livewire Support
            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:load', initPinModal);
                document.addEventListener('livewire:navigated', initPinModal);
            }
        })();
    </script>
@stop
