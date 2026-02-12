@props(['for', 'value'])

<label for="{{ $for }}" class="form-label fw-semibold">
    {{ $value ?? $slot }}
</label>
