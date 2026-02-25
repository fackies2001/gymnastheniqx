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
                    {{-- ✅ FIXED: Check for actual profile photo first, fallback to initials --}}
                    @php
                        $user = Auth::user();
                        $hasProfilePhoto =
                            $user->profile_photo && Storage::disk('public')->exists($user->profile_photo);

                        if (!$hasProfilePhoto) {
                            // Generate initials avatar if no photo
                            $userName = $user->full_name ?? 'User';
                            $initials = collect(explode(' ', $userName))
                                ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                ->take(2)
                                ->implode('');

                            // Generate unique color based on user ID
                            $colors = [
                                '6777ef', // Purple-blue (default)
                                'fc544b', // Red
                                'ffa426', // Orange
                                '3abaf4', // Light blue
                                '6c757d', // Gray
                                '47c363', // Green
                                'f3ba2f', // Yellow
                                'e83e8c', // Pink
                                '20c997', // Teal
                                '17a2b8', // Cyan
                            ];

                            $colorIndex = $user->id % count($colors);
                            $bgColor = $colors[$colorIndex];
                            $avatarUrl =
                                'https://ui-avatars.com/api/?name=' .
                                urlencode($initials) .
                                "&background={$bgColor}&color=fff&size=128";
                        } else {
                            // Use actual profile photo
                            $avatarUrl = asset('storage/' . $user->profile_photo);
                        }
                    @endphp
                    <img src="{{ $avatarUrl }}" class="img-circle elevation-2" alt="User Image"
                        onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name ?? 'User') }}&background=6777ef&color=fff&size=128';">
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
