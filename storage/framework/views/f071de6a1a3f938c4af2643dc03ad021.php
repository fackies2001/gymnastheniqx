

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


<form id="pincodeForm" method="POST"
    action="<?php echo e(session('pin_mode') === 'set' ? route('user.update.pin') : route('user.verify.pin')); ?>">
    <?php echo csrf_field(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('pin_mode') === 'set'): ?>
        <?php echo method_field('PUT'); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal9faa3ada5633bba128f4864e196b44e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9faa3ada5633bba128f4864e196b44e1 = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Modal::resolve(['id' => 'pincodeModal','size' => 'md','position' => 'centered','title' => ''.e(session('pin_mode') === 'set' ? 'Set Your PIN Code' : 'Enter Your PIN Code').'','backdrop' => 'static','keyboard' => 'false'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Modal::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

        <div class="alert <?php echo e(session('pin_mode') === 'set' ? 'alert-primary' : 'alert-warning'); ?>">
            <i class="fas fa-lock"></i>
            <strong>Security Required:</strong>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('pin_mode') === 'set'): ?>
                Set your 6-digit PIN to access the system. You cannot proceed without setting a PIN.
            <?php else: ?>
                Enter your 6-digit PIN to continue.
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="pin-wrapper d-flex justify-content-center mt-2 mb-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 6; $i++): ?>
                <input type="password" name="pin[]" maxlength="1" class="pin-digit mx-1" pattern="\d"
                    inputmode="numeric" autocomplete="one-time-code" required>
            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div id="pincodeError" class="text-danger small text-center mt-n3 mb-3" style="display:none;"></div>

         <?php $__env->slot('footer', null, []); ?> 
            <button type="submit" class="btn btn-success btn-block" id="savePincodeBtn">
                <i class="fas fa-lock"></i>
                <?php echo e(session('pin_mode') === 'set' ? 'Save PIN & Continue' : 'Verify PIN'); ?>

            </button>
            <small class="text-muted text-center d-block mt-2">
                <i class="fas fa-info-circle"></i> This modal cannot be closed until you complete the action.
            </small>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9faa3ada5633bba128f4864e196b44e1)): ?>
<?php $attributes = $__attributesOriginal9faa3ada5633bba128f4864e196b44e1; ?>
<?php unset($__attributesOriginal9faa3ada5633bba128f4864e196b44e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9faa3ada5633bba128f4864e196b44e1)): ?>
<?php $component = $__componentOriginal9faa3ada5633bba128f4864e196b44e1; ?>
<?php unset($__componentOriginal9faa3ada5633bba128f4864e196b44e1); ?>
<?php endif; ?>
</form>
<?php /**PATH D:\inventoryyy\resources\views/components/bootstrap/pincode.blade.php ENDPATH**/ ?>