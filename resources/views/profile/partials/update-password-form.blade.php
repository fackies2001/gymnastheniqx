<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group mb-3">
            <label class="small font-weight-bold text-muted">CURRENT PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-lock-open text-muted"></i></span>
                </div>
                <input type="password" name="current_password" class="form-control border-0 shadow-none"
                    placeholder="Required to save changes">
            </div>
            @if ($errors->updatePassword->has('current_password'))
                <span class="text-danger extra-small">{{ $errors->updatePassword->first('current_password') }}</span>
            @endif
        </div>

        <div class="form-group mb-3">
            <label class="small font-weight-bold text-muted">NEW PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-key text-primary"></i></span>
                </div>
                <input type="password" name="password" class="form-control border-0 shadow-none"
                    placeholder="At least 8 characters">
            </div>
            @if ($errors->updatePassword->has('password'))
                <span class="text-danger extra-small">{{ $errors->updatePassword->first('password') }}</span>
            @endif
        </div>

        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted">CONFIRM NEW PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="fas fa-check-double text-primary"></i></span>
                </div>
                <input type="password" name="password_confirmation" class="form-control border-0 shadow-none"
                    placeholder="Repeat new password">
            </div>
        </div>

        <button type="submit" class="btn btn-dark btn-block shadow-sm font-weight-bold rounded-pill">
            <i class="fas fa-shield-alt mr-2"></i> Update Security
        </button>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success mt-3 small py-2 animated fadeIn">
                <i class="fas fa-check-circle mr-1"></i> Password changed!
            </div>
        @endif
    </form>
</section>

<style>
    .rounded-lg {
        border-radius: 10px !important;
    }

    .extra-small {
        font-size: 0.75rem;
    }
</style>
