{{-- 📁 resources/views/vendor/adminlte/partials/navbar/menu-item-dropdown-user-menu.blade.php --}}

@php($logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout'))
@php($profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'profile.edit'))

@if (config('adminlte.usermenu_profile_url', false))
    @php($profile_url = Auth::user()->adminlte_profile_url())
@endif

@if (config('adminlte.use_route_url', false))
    @php($profile_url = $profile_url ? route($profile_url) : '')
    @php($logout_url = $logout_url ? route($logout_url) : '')
@else
    @php($profile_url = $profile_url ? url($profile_url) : '')
    @php($logout_url = $logout_url ? url($logout_url) : '')
@endif

{{-- ✅ NOTIFICATION BELL --}}
<li class="nav-item" id="notificationBellWrapper">
    <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="far fa-bell" style="font-size:1.15rem;"></i>
        <span class="badge badge-danger navbar-badge" id="notifBadge" style="display:none; font-size:0.65rem;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-sm" id="notifDropdown"
        style="min-width:340px; max-width:400px; border-radius:8px; border:1px solid rgba(0,0,0,0.08);">
        <div class="px-3 py-2 border-bottom" style="background:#f8f9fa;">
            <small class="text-muted font-weight-bold">
                <i class="fas fa-bell mr-1"></i>
                <span id="notifCountLabel">0 unread</span>
            </small>
        </div>
        <div id="notifItemsContainer" style="max-height:380px; overflow-y:auto;">
            <div class="text-center text-muted py-4 px-3">
                <i class="fas fa-bell-slash mb-2" style="font-size:1.8rem; opacity:0.35; display:block;"></i>
                <span style="font-size:0.85rem;">No new notifications</span>
            </div>
        </div>
    </div>
</li>

{{-- ✅ USER DROPDOWN MENU --}}
<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if (config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}" class="user-image img-circle elevation-2"
                alt="{{ Auth::user()->name }}">
        @endif
        <span @if (config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        {{-- User info body (Role & Department) --}}
        <li class="user-body">
            <div class="row">
                <div class="col-12 text-center py-2">
                    <small class="text-muted d-block">
                        <i class="fas fa-user-tag mr-1"></i>
                        <strong>Role:</strong> {{ Auth::user()->role->role_name ?? 'No Role' }}
                    </small>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-building mr-1"></i>
                        <strong>Department:</strong> {{ Auth::user()->department->name ?? 'N/A' }}
                    </small>
                </div>
            </div>
        </li>

        {{-- User menu footer (Profile & Logout buttons) --}}
        <li class="user-footer">
            @if ($profile_url)
                <a href="{{ $profile_url }}" class="btn btn-default btn-flat">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    {{ __('adminlte::menu.profile') }}
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if (!$profile_url) btn-block @endif"
                href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                {{ __('adminlte::adminlte.log_out') }}
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if (config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>

    </ul>

</li>
