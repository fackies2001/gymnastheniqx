<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid p-4">
        <div class="row">
            
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-4">
                        <h3 class="card-title font-weight-bold text-primary">
                            <i class="fas fa-user-circle mr-2"></i>Edit Profile Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="profileUpdateForm" action="<?php echo e(route('profile.update')); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>

                            
                            <div class="row align-items-center mb-5 bg-light p-3 rounded mx-1">
                                <div class="col-md-auto text-center">
                                    <div class="position-relative d-inline-block">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->profile_photo): ?>
                                            <img id="profilePreview" src="<?php echo e(asset('storage/' . $user->profile_photo)); ?>"
                                                class="rounded-circle border shadow-sm" width="130" height="130"
                                                style="object-fit: cover; border: 5px solid #fff !important;">
                                        <?php else: ?>
                                            <?php
                                                $nameParts = explode(' ', $user->name);
                                                $initials =
                                                    count($nameParts) >= 2
                                                        ? strtoupper(
                                                            substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1),
                                                        )
                                                        : strtoupper(substr($user->name, 0, 2));
                                            ?>
                                            <div id="profilePreviewPlaceholder"
                                                class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white shadow-sm"
                                                style="width:130px; height:130px; font-size: 45px; border: 5px solid #fff !important;">
                                                <?php echo e($initials); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <label for="profile_photo"
                                            class="btn btn-sm btn-primary rounded-circle position-absolute shadow"
                                            style="bottom: 5px; right: 5px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input type="file" name="profile_photo" id="profile_photo" class="d-none"
                                            accept="image/*">
                                    </div>
                                </div>
                                <div class="col mt-3 mt-md-0">
                                    <h4 class="mb-0 font-weight-bold text-dark"><?php echo e($user->name); ?></h4>
                                    <p class="text-muted mb-0 small"><?php echo e($user->email); ?></p>
                                    <span class="badge badge-pill badge-info px-3 mt-2">Active Account</span>
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-muted text-uppercase">Full Name</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i
                                                    class="fas fa-id-card text-primary"></i></span>
                                        </div>
                                        <input type="text" name="name" class="form-control border-left-0 bg-white"
                                            value="<?php echo e(old('name', $user->name)); ?>" required>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <small class="text-danger"><?php echo e($message); ?></small>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-muted text-uppercase">Contact Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i
                                                    class="fas fa-phone text-primary"></i></span>
                                        </div>
                                        <input type="text" name="contact_number"
                                            class="form-control border-left-0 bg-white"
                                            value="<?php echo e(old('contact_number', $user->contact_number)); ?>">
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <small class="text-danger"><?php echo e($message); ?></small>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-muted text-uppercase">Email Address</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0"><i
                                                    class="fas fa-envelope text-muted"></i></span>
                                        </div>
                                        <input type="email" class="form-control border-left-0 bg-light"
                                            value="<?php echo e($user->email); ?>" readonly>
                                    </div>
                                    <small class="text-xs text-muted ml-1">Linked to Google Account</small>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-muted text-uppercase">Username</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0"><i
                                                    class="fas fa-at text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control border-left-0 bg-light"
                                            value="<?php echo e($user->username ?? 'admin'); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex align-items-center">
                                <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold rounded-pill">
                                    <i class="fas fa-save mr-2"></i> Save Profile Details
                                </button>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status') === 'profile-updated'): ?>
                                    <span class="ml-3 text-success font-weight-bold animated fadeIn" id="statusMessage">
                                        <i class="fas fa-check-circle mr-1"></i> Changes Saved!
                                    </span>
                                    <script>
                                        setTimeout(() => {
                                            document.getElementById('statusMessage').style.display = 'none';
                                        }, 3000);
                                    </script>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-4 text-center">
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-lock mr-2"></i>Security Settings</h6>
                    </div>
                    <div class="card-body">
                        <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-light p-3"
                    style="border-radius: 12px; border-left: 5px solid #17a2b8;">
                    <div class="card-body p-2">
                        <h6 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-2"></i>Admin Help</h6>
                        <p class="small text-muted mb-0">
                            Deletion of account and role modifications are restricted to the <strong>User
                                Management</strong> module.
                            If you need to close this account, please coordinate with the HR or IT Department.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        document.getElementById('profile_photo').addEventListener('change', function(evt) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    let preview = document.getElementById('profilePreview');
                    let placeholder = document.getElementById('profilePreviewPlaceholder');

                    if (preview) {
                        preview.src = e.target.result;
                    } else if (placeholder) {
                        const img = document.createElement('img');
                        img.id = 'profilePreview';
                        img.src = e.target.result;
                        img.className = 'rounded-circle border shadow-sm';
                        img.width = 130;
                        img.height = 130;
                        img.style.cssText = 'object-fit: cover; border: 5px solid #fff !important;';

                        placeholder.parentNode.replaceChild(img, placeholder);
                    }
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/profile/edit.blade.php ENDPATH**/ ?>