<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if (config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        @if (Auth::check())
            {{-- ✅ FIXED: Hide user panel on mobile, show only on desktop --}}
            <div class="user-panel mt-3 pb-3 mb-3 d-none d-md-flex">

                <div class="image">
                    {{-- ✅ FIXED: Dynamic avatar initials based on user's full name --}}
                    @php
                        $userName = Auth::user()->full_name ?? 'User';
                        $initials = collect(explode(' ', $userName))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($initials) }}&background=6777ef&color=fff&size=128"
                        class="img-circle elevation-2" alt="User Image">
                </div>


                <div class="info">
                    <a href="{{ route('profile.edit') }}" class="d-block">
                        {{ Auth::user()->full_name }}
                    </a>
                    <small class="d-block text-muted">
                        {{ Auth::user()->role?->role_name ?? 'No Role' }}
                    </small>
                </div>
            </div>
        @endif

        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if (config('adminlte.sidebar_nav_animation_speed') != 300) data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}" @endif
                @if (!config('adminlte.sidebar_nav_accordion')) data-accordion="false" @endif>
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>
