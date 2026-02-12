<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-2">
        @csrf
        @method('patch')

        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted text-uppercase">Full Name</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-id-card text-primary"></i></span>
                </div>
                <input type="text" name="name" class="form-control border-0 shadow-none"
                    value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            </div>
            @if ($errors->has('name'))
                <span class="text-danger small">{{ $errors->first('name') }}</span>
            @endif
        </div>

        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted text-uppercase">Contact Number</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-phone text-primary"></i></span>
                </div>
                <input type="text" name="contact_number" class="form-control border-0 shadow-none"
                    value="{{ old('contact_number', $user->employee->contact_number ?? '') }}">
            </div>
        </div>

        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted text-uppercase">Email Address</label>
            <div class="input-group border rounded-lg overflow-hidden bg-light shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-envelope text-muted"></i></span>
                </div>
                <input type="email" class="form-control border-0 shadow-none" value="{{ $user->email }}" readonly>
            </div>
            <p class="text-xs text-muted mt-1 ml-1"><i class="fas fa-info-circle mr-1"></i> Email cannot be changed here
                (Linked to Google).</p>
        </div>

        <input type="file" name="profile_photo" id="profile_photo" class="d-none" accept="image/*">

        <div class="mt-4 pt-2 border-top">
            <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold rounded-pill">
                <i class="fas fa-save mr-2"></i> Save Profile Details
            </button>

            @if (session('status') === 'profile-updated')
                <span class="ml-3 text-success font-weight-bold animated fadeIn">
                    <i class="fas fa-check-circle mr-1"></i> Successfully Saved!
                </span>
            @endif
        </div>
    </form>
</section>
