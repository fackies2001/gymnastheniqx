

{{-- <style>
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

    /* ✅ PREVENT CLOSING MODAL */
    #pincodeModal .close,
    #pincodeModal .modal-header .close {
        display: none !important;
        /* Hide X button */
    }
</style> --}}

{{-- <form id="pincodeForm" method="POST" action="{{ route('user.update.pin') }}">
    @csrf
    @method('PUT')

    {{-- ✅ NO CLOSE BUTTON! backdrop="static" keyboard="false" --}}
    {{-- <x-bootstrap.modal id="pincodeModal" size="md" position="centered" title="Set your pincode" backdrop="static"
        keyboard="false">

        <div class="alert alert-warning">
            <i class="fas fa-lock"></i>
            <strong>Security Required:</strong> Set your 6-digit PIN to access the system.
            <strong>You cannot proceed without setting a PIN.</strong>
        </div> --}} 

        {{-- Hidden input para sa Employee ID --}}
        {{-- <input type="hidden" name="id" id="pincode_employee_id">

        <div class="pin-wrapper d-flex justify-content-center mt-2 mb-4">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" name="pin[]" maxlength="1" class="pin-digit mx-1" pattern="\d"
                    inputmode="numeric" autocomplete="one-time-code" required>
            @endfor
        </div> --}}

        {{-- <div id="pincodeError" class="text-danger small text-center mt-n3 mb-3" style="display:none;"></div>

        <x-slot name="footer">
            {{-- ✅ NO CLOSE BUTTON - Only Save! --}}
            {{-- <button type="submit" class="btn btn-success btn-block" id="savePincodeBtn">
                <i class="fas fa-lock"></i> Save PIN & Continue
            </button>
            <small class="text-muted text-center d-block mt-2">
                <i class="fas fa-info-circle"></i> This modal cannot be closed until you set your PIN.
            </small>
        </x-slot>
    </x-bootstrap.modal>
</form>  --}}
