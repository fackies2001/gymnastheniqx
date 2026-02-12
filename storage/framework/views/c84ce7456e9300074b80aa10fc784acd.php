
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id',
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => '-- Select --',
    'required' => false,
    'multiple' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'id',
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => '-- Select --',
    'required' => false,
    'multiple' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<select id="<?php echo e($id); ?>" name="<?php echo e($name); ?><?php echo e($multiple ? '[]' : ''); ?>"
    <?php echo e($required && !$multiple ? 'required' : ''); ?> <?php echo e($multiple ? 'multiple' : ''); ?>

    <?php echo e($attributes->merge(['class' => 'form-control form-control-sm '])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$multiple): ?>
        <option value=""><?php echo e($placeholder); ?></option>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($multiple && is_array($value)): ?>
            <option value="<?php echo e($key); ?>" <?php echo e(in_array((string) $key, $value) ? 'selected' : ''); ?>>
                <?php echo e($label); ?>

            </option>
        <?php else: ?>
            <option value="<?php echo e($key); ?>" <?php echo e((string) $value === (string) $key ? 'selected' : ''); ?>>
                <?php echo e($label); ?>

            </option>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</select>
<?php /**PATH D:\inventoryyy\resources\views/components/bootstrap/select.blade.php ENDPATH**/ ?>