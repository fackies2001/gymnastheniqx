<?php $__env->startSection('subtitle', 'Supplier'); ?>
<?php $__env->startSection('content_header_title', 'Supplier'); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                            <h3 class="my-4">Create Supplier</h3>
                        </div>
                        <!-- Button to trigger the Create Supplier modal -->
                        
                    </div>
                    <form method="POST" action="<?php echo e(route('suppliers.store')); ?>">
                        <div class="card-body row">
                            <?php echo csrf_field(); ?>
                            <div class="col-sm-12 <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('can-create-supplier-api')): ?> col-md-6 <?php endif; ?>">


                                <div class="mb-3">
                                    <?php if (isset($component)) { $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Label::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Label::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'name','value' => 'Supplier Name']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $attributes = $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $component = $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Input::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Input::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'name','name' => 'name','required' => true,'placeholder' => 'Enter supplier name']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $attributes = $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $component = $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalcab76fe1716c5d77e8189449b2e4d420 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420 = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\InputError::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\InputError::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('name'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $attributes = $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $component = $__componentOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <?php if (isset($component)) { $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Label::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Label::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'email','value' => 'Supplier Email']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $attributes = $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $component = $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Input::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Input::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'email','name' => 'email','type' => 'email','required' => true,'placeholder' => 'Enter supplier email']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $attributes = $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $component = $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalcab76fe1716c5d77e8189449b2e4d420 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420 = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\InputError::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\InputError::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('email'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $attributes = $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $component = $__componentOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <?php if (isset($component)) { $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Label::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Label::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'phone','value' => 'Supplier Phone']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $attributes = $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $component = $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Input::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Input::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'phone','name' => 'phone','required' => true,'placeholder' => 'Enter supplier phone']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $attributes = $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $component = $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalcab76fe1716c5d77e8189449b2e4d420 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420 = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\InputError::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\InputError::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('phone'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $attributes = $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $component = $__componentOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <?php if (isset($component)) { $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Label::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Label::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'address','value' => 'Supplier Address']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $attributes = $__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__attributesOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a)): ?>
<?php $component = $__componentOriginalda7250f67bc5c8716f70f727bc7fea7a; ?>
<?php unset($__componentOriginalda7250f67bc5c8716f70f727bc7fea7a); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\Input::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\Input::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'address','name' => 'address','required' => true,'placeholder' => 'Enter supplier address']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $attributes = $__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__attributesOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc)): ?>
<?php $component = $__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc; ?>
<?php unset($__componentOriginal3dcfa03b00c8b3d35e57d34eb87e09dc); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalcab76fe1716c5d77e8189449b2e4d420 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420 = $attributes; } ?>
<?php $component = App\View\Components\Bootstrap\InputError::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bootstrap.input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Bootstrap\InputError::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('address'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $attributes = $__attributesOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__attributesOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420)): ?>
<?php $component = $__componentOriginalcab76fe1716c5d77e8189449b2e4d420; ?>
<?php unset($__componentOriginalcab76fe1716c5d77e8189449b2e4d420); ?>
<?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex">
                            <button type="submit" class="btn btn-success btn-sm ml-auto" id="createSupplierSubmit"> Save
                                Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/suppliers/create.blade.php ENDPATH**/ ?>