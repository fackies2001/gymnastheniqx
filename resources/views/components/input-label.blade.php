@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-violet-700 dark:text-violet-300']) }}>
    {{ $value ?? $slot }}
</label>
