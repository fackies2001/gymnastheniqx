
 
{{-- @extends('adminlte::page') --}}

{{-- =========================
|   HEAD
|========================= --}}
{{-- @section('adminlte_head')
    @if (session()->has('sanctum_token'))
        <meta name="api-token" content="{{ session('sanctum_token') }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection --}}

{{-- =========================
|   TITLE
|========================= --}}
{{-- @section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle')
        - @yield('subtitle')
    @endif
@stop --}}

{{-- =========================
|   CONTENT HEADER
|========================= --}}
{{-- @section('content_header')
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
@stop --}}

{{-- =========================
|   MAIN CONTENT
|========================= --}}
    {{-- @section('content')
        @yield('content_body')

        {{-- ✅ Shared PIN Modal Component --}}
        {{-- <x-bootstrap.pincode />
    @stop --}}

{{-- ========================= --}}
|   CSS


{{-- =========================
|   JAVASCRIPT
|========================= --}}
{{-- @section('js')
    {{-- ✅ 1. STACK JS --}}
    {{-- @stack('js') --}}

    {{-- ✅ 2. PINCODE JS --}}
    {{-- @vite('resources/js/pincode.js') --}} --}}

    {{-- ✅ ✅ ✅ LOCKED PIN MODAL LOGIC --}}
    {{-- <script>
        (function() {
            'use strict';

            console.log('='.repeat(60));
            console.log('🔒 LOCKED PIN MODAL INITIALIZATION');
            console.log('='.repeat(60));

            function initPinModal() {
                // ✅ Check if we should show PIN modal
                const shouldShowPinModal = {{ session('show_pin_modal') ? 'true' : 'false' }};
                const pinMode = '{{ session('pin_mode', 'verify') }}';
                const hasPinInDatabase = {{ auth()->user() && !empty(auth()->user()->pin) ? 'true' : 'false' }};

                console.log('📋 Session Data:', {
                    shouldShow: shouldShowPinModal,
                    pinMode: pinMode,
                    hasPinInDB: hasPinInDatabase,
                });

                // ❌ Exit early if shouldn't show
                if (!shouldShowPinModal) {
                    console.log('✅ PIN Modal: Not needed');
                    console.log('='.repeat(60));
                    return;
                }

                console.log('🔒 PIN Modal REQUIRED - Locking UI...');

                const form = document.getElementById('pincodeForm');
                const modalElement = document.getElementById('pincodeModal');

                if (!form || !modalElement) {
                    console.error('❌ PIN modal elements not found!');
                    return;
                }

                const modalTitle = modalElement.querySelector('.modal-title');
                const modalAlert = modalElement.querySelector('.alert');
                const methodInput = form.querySelector('input[name="_method"]');

                // ✅ Configure modal based on mode
                if (pinMode === 'set' || !hasPinInDatabase) {
                    if (modalTitle) modalTitle.innerText = "Set New PIN Code";
                    if (modalAlert) {
                        modalAlert.className = "alert alert-warning";
                        modalAlert.innerHTML =
                            '<i class="fas fa-lock"></i> <strong>Security Required:</strong> Set your 6-digit PIN to access the system. <strong>You cannot proceed without setting a PIN.</strong>';
                    }
                    form.action = "{{ route('user.update.pin') }}";
                    if (methodInput) methodInput.value = "PUT";

                    console.log('✅ PIN Modal Mode: SET (New User)');
                } else {
                    if (modalTitle) modalTitle.innerText = "Enter PIN Code";
                    if (modalAlert) {
                        modalAlert.className = "alert alert-info";
                        modalAlert.innerHTML =
                            '<i class="fas fa-lock"></i> <strong>Security Check:</strong> Enter your 6-digit PIN to continue. <strong>This modal cannot be closed.</strong>';
                    }
                    form.action = "{{ route('user.verify.pin') }}";
                    if (methodInput) methodInput.value = "POST";

                    console.log('✅ PIN Modal Mode: VERIFY (Existing User)');
                }

                // ✅ Add body class to blur background
                document.body.classList.add('pin-modal-active');

                // ✅ Show modal using jQuery with LOCKED settings
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    console.log('🔒 Showing LOCKED modal...');

                    $('#pincodeModal').modal({
                        backdrop: 'static', // Cannot click outside
                        keyboard: false, // Cannot press ESC
                        show: true
                    });

                    // ✅ FORCE REMOVE CLOSE BUTTON if it exists
                    $('#pincodeModal .close').remove();
                    $('#pincodeModal .modal-header .close').remove();

                    // ✅ PREVENT ALL ATTEMPTS TO CLOSE
                    $('#pincodeModal').on('hide.bs.modal', function(e) {
                        // Only allow closing if pin_verified session is true
                        const pinVerified = {{ session('pin_verified') ? 'true' : 'false' }};
                        if (!pinVerified) {
                            console.warn('⚠️  Attempt to close PIN modal blocked!');
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    });

                    // ✅ DISABLE ESC KEY GLOBALLY while modal is open
                    $(document).on('keydown.pinmodal', function(e) {
                        if (e.key === 'Escape' || e.keyCode === 27) {
                            console.warn('⚠️  ESC key blocked!');
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    });

                    // ✅ DISABLE BROWSER BACK BUTTON
                    history.pushState(null, null, location.href);
                    window.onpopstate = function() {
                        console.warn('⚠️  Back button blocked!');
                        history.pushState(null, null, location.href);
                    };

                    console.log('✅ Modal locked successfully!');
                    console.log('🔒 UI is now LOCKED until PIN is entered');

                } else {
                    console.error('❌ jQuery or Bootstrap modal not available!');
                }

                console.log('='.repeat(60));
            }

            // ✅ Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPinModal);
            } else {
                if (typeof $ !== 'undefined') {
                    $(document).ready(initPinModal);
                } else {
                    initPinModal();
                }
            }
        })();
    </script>
@stop --}}
