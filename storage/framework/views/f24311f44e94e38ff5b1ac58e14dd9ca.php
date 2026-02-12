<aside class="main-sidebar <?php echo e(config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4')); ?>">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('adminlte.logo_img_xl')): ?>
        <?php echo $__env->make('adminlte::partials.common.brand-logo-xl', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('adminlte::partials.common.brand-logo-xs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="sidebar">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::check()): ?>
            
            <div class="user-panel mt-3 pb-3 mb-3 d-none d-md-flex">

                <div class="image">
                    
                    <?php
                        $userName = Auth::user()->full_name ?? 'User';
                        $initials = collect(explode(' ', $userName))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');

                        // Generate unique color based on user ID
                        $colors = [
                            '6777ef', // Purple-blue (default)
                            'fc544b', // Red
                            'ffa426', // Orange
                            '3abaf4', // Light blue
                            '6c757d', // Gray
                            '47c363', // Green
                            'f3ba2f', // Yellow
                            'e83e8c', // Pink
                            '20c997', // Teal
                            '17a2b8', // Cyan
                        ];

                        $colorIndex = Auth::user()->id % count($colors);
                        $bgColor = $colors[$colorIndex];
                    ?>
                    <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($initials)); ?>&background=<?php echo e($bgColor); ?>&color=fff&size=128"
                        class="img-circle elevation-2" alt="User Image">
                </div>


                <div class="info">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="d-block">
                        <?php echo e(Auth::user()->full_name); ?>

                    </a>
                    <small class="d-block text-muted">
                        <?php echo e(Auth::user()->role?->role_name ?? 'No Role'); ?>

                    </small>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column <?php echo e(config('adminlte.classes_sidebar_nav', '')); ?>"
                data-widget="treeview" role="menu"
                <?php if(config('adminlte.sidebar_nav_animation_speed') != 300): ?> data-animation-speed="<?php echo e(config('adminlte.sidebar_nav_animation_speed')); ?>" <?php endif; ?>
                <?php if(!config('adminlte.sidebar_nav_accordion')): ?> data-accordion="false" <?php endif; ?>>
                <?php echo $__env->renderEach('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item'); ?>
            </ul>
        </nav>
    </div>

</aside>
<?php /**PATH D:\inventoryyy\resources\views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php ENDPATH**/ ?>