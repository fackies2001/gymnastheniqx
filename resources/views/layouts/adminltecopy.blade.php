

@extends('adminlte::page')

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
    <x-bootstrap.pincode />
@stop --}}

{{-- =========================
|   CSS
|========================= --}}
{{-- @section('css')
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    @stack('css')
@stop --}}

{{-- =========================
|   JAVASCRIPT
|========================= --}}
@section('js')
    {{-- ✅ 1. STACK JS --}}
    @stack('js')

    {{-- ✅ 2. PINCODE JS --}}
    @vite('resources/js/pincode_LOCKED.js')

    {{-- ✅ ✅ ✅ IMPROVED PIN MODAL LOGIC WITH EXTENSIVE DEBUGGING --}}
    <script>
        (function() {
            'use strict';

            console.log('='.repeat(60));
            console.log('🔐 PIN MODAL INITIALIZATION STARTED');
            console.log('='.repeat(60));

            function initPinModal() {
                // ✅ Check if we should show PIN modal
                const shouldShowPinModal = {{ session('show_pin_modal') ? 'true' : 'false' }};
                const pinMode = '{{ session('pin_mode', 'verify') }}';
                const hasPinInDatabase = {{ auth()->user() && !empty(auth()->user()->pin) ? 'true' : 'false' }};

                // 🔍 EXTENSIVE DEBUG LOGGING
                console.log('📋 Session Data:', {
                    shouldShow: shouldShowPinModal,
                    pinMode: pinMode,
                    hasPinInDB: hasPinInDatabase,
                    hasUser: {{ auth()->check() ? 'true' : 'false' }},
                    userId: {{ auth()->id() ?? 'null' }},
                    userName: '{{ auth()->user()->full_name ?? 'Unknown' }}'
                });

                console.log('🔍 Environment Check:', {
                    jQueryAvailable: typeof $ !== 'undefined',
                    jQueryVersion: typeof $ !== 'undefined' ? ($.fn ? $.fn.jquery : 'NO VERSION') :
                        'NOT LOADED',
                    bootstrapModalAvailable: typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined',
                });

                // Check DOM elements
                const form = document.getElementById('pincodeForm');
                const modalElement = document.getElementById('pincodeModal');

                console.log('🔍 DOM Elements:', {
                    formExists: form !== null,
                    modalExists: modalElement !== null,
                    formAction: form ? form.action : 'N/A',
                    modalId: modalElement ? modalElement.id : 'N/A'
                });

                // ❌ Exit early if shouldn't show
                if (!shouldShowPinModal) {
                    console.log('✅ PIN Modal: Not needed (session flag is false)');
                    console.log('='.repeat(60));
                    return;
                }

                console.log('⚠️  PIN Modal SHOULD SHOW - Proceeding...');

                // ❌ Exit if elements not found
                if (!form || !modalElement) {
                    console.error('❌ PIN modal elements not found!');
                    console.error('Missing:', {
                        form: !form,
                        modal: !modalElement
                    });
                    console.log('='.repeat(60));
                    return;
                }

                const modalTitle = modalElement.querySelector('.modal-title');
                const modalAlert = modalElement.querySelector('.alert');
                const methodInput = form.querySelector('input[name="_method"]');

                console.log('🔍 Modal Sub-Elements:', {
                    titleExists: modalTitle !== null,
                    alertExists: modalAlert !== null,
                    methodInputExists: methodInput !== null
                });

                // ✅ Configure modal based on mode
                if (pinMode === 'set' || !hasPinInDatabase) {
                    // 🆕 NEW USER MODE: Set PIN for first time
                    if (modalTitle) modalTitle.innerText = "Set New PIN Code";
                    if (modalAlert) modalAlert.innerHTML =
                        "Welcome! Please set your 6-digit PIN. Do not share it with anyone.";
                    form.action = "{{ route('user.update.pin') }}";
                    if (methodInput) methodInput.value = "PUT";

                    console.log('✅ PIN Modal Mode: SET (New User)');
                    console.log('   - Title: "Set New PIN Code"');
                    console.log('   - Action: ' + form.action);
                    console.log('   - Method: PUT');
                } else {
                    // 🔐 EXISTING USER MODE: Verify PIN
                    if (modalTitle) modalTitle.innerText = "Enter PIN Code";
                    if (modalAlert) modalAlert.innerHTML = "Please enter your 6-digit PIN to continue.";
                    form.action = "{{ route('user.verify.pin') }}";
                    if (methodInput) methodInput.value = "POST";

                    console.log('✅ PIN Modal Mode: VERIFY (Existing User)');
                    console.log('   - Title: "Enter PIN Code"');
                    console.log('   - Action: ' + form.action);
                    console.log('   - Method: POST');
                }

                // ✅ Show modal using jQuery
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    console.log('🚀 Attempting to show modal via jQuery...');

                    try {
                        $('#pincodeModal').modal({
                            backdrop: 'static',
                            keyboard: false,
                            show: true
                        });

                        console.log('✅ Modal.show() called successfully!');

                        // Double-check if modal is visible after 500ms
                        setTimeout(() => {
                            const isVisible = $('#pincodeModal').hasClass('show');
                            console.log('🔍 Modal Visibility Check (after 500ms):', {
                                hasShowClass: isVisible,
                                display: $('#pincodeModal').css('display'),
                                opacity: $('#pincodeModal').css('opacity')
                            });

                            if (!isVisible) {
                                console.error('❌ Modal did NOT become visible!');
                                console.error('   Trying manual show...');
                                $('#pincodeModal').show().addClass('show').css({
                                    display: 'block',
                                    opacity: 1
                                });
                            }
                        }, 500);

                    } catch (err) {
                        console.error('❌ Error showing modal:', err);
                    }
                } else {
                    console.error('❌ jQuery or Bootstrap modal not available!');
                    console.error('   typeof $:', typeof $);
                    console.error('   $.fn.modal:', typeof $ !== 'undefined' ? typeof $.fn.modal : 'N/A');
                }

                console.log('='.repeat(60));
            }

            // ✅ Initialize on DOM ready
            console.log('⏳ Waiting for DOM ready...');

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    console.log('✅ DOM Ready Event Fired');
                    initPinModal();
                });
            } else {
                console.log('✅ DOM Already Ready');
                if (typeof $ !== 'undefined') {
                    $(document).ready(() => {
                        console.log('✅ jQuery Ready Event Fired');
                        initPinModal();
                    });
                } else {
                    initPinModal();
                }
            }

            // ✅ Handle Livewire (if used)
            if (typeof Livewire !== 'undefined') {
                console.log('⚠️  Livewire detected - adding listeners');
                document.addEventListener('livewire:load', initPinModal);
                document.addEventListener('livewire:navigated', initPinModal);
            }
        })();
    </script>

    {{-- ✅ 3. REMOVE UNWANTED DROPDOWN ITEMS --}}
    {{-- <script>
        $(document).ready(function() {
            // Remove unwanted menu items from dropdown
            $('.dropdown-menu-right .dropdown-item').filter(function() {
                let text = $(this).text().trim();
                return text === 'NO ROLE - GYMNASTHIENIQX' ||
                    text === 'NO ROLE' ||
                    text === 'GYMNASTHIENIQX' ||
                    text === 'Inventory';
            }).remove();

            // Also remove any li that's not user-header, user-body, or user-footer
            $('.dropdown-menu-right > li').not('.user-header, .user-body, .user-footer').remove();
        });
    </script>
@stop --}}


feb 11