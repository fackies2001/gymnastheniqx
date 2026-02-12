// ✅ jQuery is already global from app.js
console.log('✅ Pincode_LOCKED.js loaded');

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.pin-digit');
    const form = document.getElementById('pincodeForm');

    if (!form) {
        console.warn('PIN form not found');
        return;
    }

    console.log('✅ PIN form found, inputs count:', inputs.length);

    // 🟢 1. AUTO-FOCUS (Cursor Movement) - 6 DIGITS
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            input.value = input.value.replace(/[^0-9]/g, '').slice(0, 1);
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // 🟢 2. AJAX SUBMISSION WITH LOCKED MODAL LOGIC
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const saveBtn = document.getElementById('savePincodeBtn');
        
        if (!saveBtn) return;

        // ✅ Validate 6 digits filled
        let allFilled = true;
        inputs.forEach(input => {
            if (!input.value) allFilled = false;
        });

        if (!allFilled) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Please enter all 6 digits', 'error');
            } else {
                alert('Please enter all 6 digits');
            }
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(async response => {
            const data = await response.json();
            
            if (response.ok && data.success) {
                console.log('✅ PIN saved/verified successfully!');
                
                // ✅ REMOVE LOCK from body
                document.body.classList.remove('pin-modal-active');
                
                // ✅ RE-ENABLE ESC key
                if (typeof $ !== 'undefined') {
                    $(document).off('keydown.pinmodal');
                }
                
                // ✅ RE-ENABLE back button
                window.onpopstate = null;
                
                // ✅ Close modal using jQuery
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    // Temporarily allow closing
                    $('#pincodeModal').off('hide.bs.modal');
                    $('#pincodeModal').modal('hide');
                }
                
                // ✅ Show success message
                setTimeout(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || 'PIN saved successfully',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // ✅ Reload page to refresh session
                            window.location.reload();
                        });
                    } else {
                        alert(data.message || 'PIN saved successfully');
                        window.location.reload();
                    }
                }, 300);
                
            } else {
                // ❌ Error handling
                const errorMsg = data.message || 'Validation failed';
                console.error('❌ PIN error:', errorMsg);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
                
                // Reset button
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-lock"></i> Save PIN & Continue';
                
                // Clear inputs for retry
                inputs.forEach(input => input.value = '');
                inputs[0].focus();
            }
        })
        .catch(err => {
            console.error('PIN submission error:', err);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Server Connection Error.', 'error');
            } else {
                alert('Server Connection Error');
            }
            
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-lock"></i> Save PIN & Continue';
        });
    });
});