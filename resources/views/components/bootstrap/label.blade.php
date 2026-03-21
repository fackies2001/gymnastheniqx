@props(['for', 'value', 'required' => false])

<label for="{{ $for }}" class="form-label fw-semibold">
    {{ $value ?? $slot }}
    @if ($required)
        <span style="color: red;">*</span>
    @endif
</label>
