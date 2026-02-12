<div class="d-flex flex-column align-items-center border-start border-secondary ps-2">
    <img src="{{ asset($user?->profile_photo ?? 'default-profile.png') }}" alt="Profile Photo"
        class="mb-2 rounded-circle me-2" style="width:35px;height:35px;object-fit:cover;">
    <span>{{ $user?->full_name ?? 'N/A' }}</span>
</div>
