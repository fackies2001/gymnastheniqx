    
     @extends('adminlte::page')
   

    {{-- Dito natin kukunin ang title na sine-set natin sa bawat page --}}
    @section('title')
        @yield('title', config('adminlte.title', 'Gymnastheniqx'))
    @stop

    {{-- Dito papasok ang "Header" ng dashboard (yung may Breadcrumbs) --}}
    @section('content_header')
        @yield('content_header')
    @stop

    {{-- Dito papasok ang main body ng system (Reports, Inventory, etc.) --}}
    @section('content')
        <div class="container-fluid">
            @yield('content_body')
        </div>
    @stop

    {{-- Dito papasok ang mga custom CSS na kailangan sa bawat page --}}
    @section('css')
        @stack('css')
    @stop

    {{-- Dito papasok ang mga custom JS/Scripts (DataTables, Swal, etc.) --}}
    {{-- Dito papasok ang mga custom JS/Scripts (DataTables, Swal, etc.) --}}
    @section('js')
        @stack('js')
        <script>
            // 👇 1. IMPORTANT: CSRF TOKEN SETUP (Fix sa Error 419 / Network Error)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Common logic (Toast notifications, etc.)
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000
                });
            @endif
        </script>
    @stop

    25-01-26