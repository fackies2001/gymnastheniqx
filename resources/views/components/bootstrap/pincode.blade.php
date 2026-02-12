{{-- 📁 resources/views/components/bootstrap/pincode.blade.php --}}

<style>
    .pin-digit {
        width: 50px;
        height: 60px;
        font-size: 24px;
        font-weight: bold;
        border: 2px solid #ced4da;
        border-radius: 8px;
        text-align: center;
        transition: border-color 0.2s;
    }

    .pin-digit:focus {
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.25);
        outline: none;
    }

    .pin-wrapper {
        gap: 5px;
    }

    /* ✅ PREVENT CLOSING */
    #pincodeModal .close,
    #pincodeModal .modal-header .close {
        display: none !important;
    }
</style>

{{-- ✅ DYNAMIC FORM ACTION based on pin_mode --}}
<form id="pincodeForm" method="POST"
    action="{{ session('pin_mode') === 'set' ? route('user.update.pin') : route('user.verify.pin') }}">
    @csrf
    @if (session('pin_mode') === 'set')
        @method('PUT')
    @endif

    <x-bootstrap.modal id="pincodeModal" size="md" position="centered"
        title="{{ session('pin_mode') === 'set' ? 'Set Your PIN Code' : 'Enter Your PIN Code' }}" backdrop="static"
        keyboard="false">

        <div class="alert {{ session('pin_mode') === 'set' ? 'alert-primary' : 'alert-warning' }}">
            <i class="fas fa-lock"></i>
            <strong>Security Required:</strong>
            @if (session('pin_mode') === 'set')
                Set your 6-digit PIN to access the system. You cannot proceed without setting a PIN.
            @else
                Enter your 6-digit PIN to continue.
            @endif
        </div>

        <div class="pin-wrapper d-flex justify-content-center mt-2 mb-4">
            @for ($i = 0; $i < 6; $i++)
                <input type="password" name="pin[]" maxlength="1" class="pin-digit mx-1" pattern="\d"
                    inputmode="numeric" autocomplete="one-time-code" required>
            @endfor
        </div>

        <div id="pincodeError" class="text-danger small text-center mt-n3 mb-3" style="display:none;"></div>

        <x-slot name="footer">
            <button type="submit" class="btn btn-success btn-block" id="savePincodeBtn">
                <i class="fas fa-lock"></i>
                {{ session('pin_mode') === 'set' ? 'Save PIN & Continue' : 'Verify PIN' }}
            </button>
            <small class="text-muted text-center d-block mt-2">
                <i class="fas fa-info-circle"></i> This modal cannot be closed until you complete the action.
            </small>
        </x-slot>
    </x-bootstrap.modal>
</form>
