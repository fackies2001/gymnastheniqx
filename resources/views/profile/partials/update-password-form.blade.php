<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        {{-- CURRENT PASSWORD --}}
        <div class="form-group mb-3">
            <label class="small font-weight-bold text-muted">CURRENT PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0">
                        <i class="fas fa-lock-open text-muted"></i>
                    </span>
                </div>
                <input type="password" name="current_password" class="form-control border-0 shadow-none"
                    placeholder="Required to save changes">
            </div>
            @if ($errors->updatePassword->has('current_password'))
                <span class="text-danger extra-small">
                    {{ $errors->updatePassword->first('current_password') }}
                </span>
            @endif
        </div>

        {{-- NEW PASSWORD --}}
        <div class="form-group mb-3">
            <label class="small font-weight-bold text-muted">NEW PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0">
                        <i class="fas fa-key text-primary"></i>
                    </span>
                </div>
                <input type="password" name="password" id="newPasswordInput" class="form-control border-0 shadow-none"
                    placeholder="At least 8 characters" oninput="checkPasswordStrength(this.value)">
            </div>

            {{-- ✅ Live Password Requirements Checker --}}
            <div id="passwordRequirements" class="mt-2 p-2 rounded"
                style="background:#fff8e1; border:1px solid #ffe082; display:none;">
                <small class="font-weight-bold text-muted d-block mb-1">
                    <i class="fas fa-exclamation-circle text-warning mr-1"></i> Password Requirements:
                </small>
                <small id="req-length" class="d-block text-danger">
                    <i class="fas fa-times-circle mr-1"></i> At least 8 characters
                </small>
                <small id="req-upper" class="d-block text-danger">
                    <i class="fas fa-times-circle mr-1"></i> At least 1 uppercase letter (A-Z)
                </small>
                <small id="req-lower" class="d-block text-danger">
                    <i class="fas fa-times-circle mr-1"></i> At least 1 lowercase letter (a-z)
                </small>
                <small id="req-number" class="d-block text-danger">
                    <i class="fas fa-times-circle mr-1"></i> At least 1 number (0-9)
                </small>
                <small id="req-special" class="d-block text-danger">
                    <i class="fas fa-times-circle mr-1"></i> At least 1 special character (!@#$%^&*)
                </small>
            </div>

            @if ($errors->updatePassword->has('password'))
                <span class="text-danger extra-small">
                    {{ $errors->updatePassword->first('password') }}
                </span>
            @endif
        </div>

        {{-- CONFIRM NEW PASSWORD --}}
        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted">CONFIRM NEW PASSWORD</label>
            <div class="input-group border rounded-lg overflow-hidden bg-white shadow-none">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0">
                        <i class="fas fa-check-double text-primary"></i>
                    </span>
                </div>
                <input type="password" name="password_confirmation" class="form-control border-0 shadow-none"
                    placeholder="Repeat new password">
            </div>
        </div>

        {{-- SUBMIT BUTTON --}}
        <button type="submit" class="btn btn-dark btn-block shadow-sm font-weight-bold rounded-pill">
            <i class="fas fa-shield-alt mr-2"></i> Update Security
        </button>

        {{-- SUCCESS MESSAGE --}}
        @if (session('status') === 'password-updated')
            <div class="alert alert-success mt-3 small py-2 animated fadeIn">
                <i class="fas fa-check-circle mr-1"></i> Password changed successfully!
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

<script>
    function checkPasswordStrength(value) {
        const box = document.getElementById('passwordRequirements');

        if (value.length === 0) {
            box.style.display = 'none';
            return;
        }

        box.style.display = 'block';

        const checks = {
            'req-length': value.length >= 8,
            'req-upper': /[A-Z]/.test(value),
            'req-lower': /[a-z]/.test(value),
            'req-number': /[0-9]/.test(value),
            'req-special': /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value),
        };

        for (const [id, passed] of Object.entries(checks)) {
            const el = document.getElementById(id);
            const icon = el.querySelector('i');
            if (passed) {
                el.className = 'd-block text-success';
                icon.className = 'fas fa-check-circle mr-1';
            } else {
                el.className = 'd-block text-danger';
                icon.className = 'fas fa-times-circle mr-1';
            }
        }
    }
</script>
