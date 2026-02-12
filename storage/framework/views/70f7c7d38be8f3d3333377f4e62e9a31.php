<?php $__env->startSection('subtitle', 'Suppliers'); ?>
<?php $__env->startSection('content_header_title', 'Suppliers'); ?>
<?php $__env->startSection('content_header_subtitle', 'All Suppliers'); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="row">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-centered">
                        <h5><a href="<?php echo e(route('suppliers.show', $supplier->id)); ?>" title="click to view supplier's products">
                                <?php echo e($supplier->name); ?>

                            </a></h5>
                        <hr>
                        <p><?php echo e($supplier->email); ?></p>
                        <p><?php echo e($supplier->phone); ?></p>
                        <p><?php echo e($supplier->address); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/suppliers/index.blade.php ENDPATH**/ ?>