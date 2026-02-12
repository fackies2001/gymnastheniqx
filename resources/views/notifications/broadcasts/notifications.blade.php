{{-- 📁 resources/views/notifications/broadcasts/notifications.blade.php --}}
{{-- Ito yung dropdown HTML ng notification bell --}}

@if ($notifications->isEmpty())
    <div class="text-center text-muted py-3 px-3">
        <i class="fas fa-bell-slash mb-2" style="font-size:1.5rem; opacity:0.4;"></i>
        <div style="font-size:0.85rem;">No new notifications</div>
    </div>
@else
    @foreach ($notifications as $notif)
        <a href="{{ $notif['url'] ?? '#' }}" class="dropdown-item notif-item d-flex align-items-start py-2 px-3"
            data-notif-id="{{ $notif['id'] }}" style="border-bottom: 1px solid rgba(0,0,0,0.05); white-space: normal;">

            {{-- Icon --}}
            <div class="mr-2 mt-1" style="min-width:28px; text-align:center;">
                <i class="{{ $notif['icon'] ?? 'fas fa-bell text-info' }}" style="font-size:1rem;"></i>
            </div>

            {{-- Text --}}
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.82rem; font-weight:600; line-height:1.3; color:inherit;">
                    {{ $notif['message'] }}
                </div>
                <div style="font-size:0.72rem; color:#999; margin-top:2px;">
                    <i class="far fa-clock mr-1"></i>{{ $notif['time_ago'] }}
                    <span class="ml-1 text-muted" style="font-size:0.68rem;">
                        {{ $notif['time'] }}
                    </span>
                </div>
            </div>

        </a>
    @endforeach

    {{-- Mark All Read --}}
    <div class="dropdown-divider my-0"></div>
    <a href="#" id="markAllReadBtn" class="dropdown-item text-center py-2"
        style="font-size:0.8rem; color:#007bff; font-weight:600;">
        <i class="fas fa-check-double mr-1"></i> Mark all as read
    </a>
@endif
