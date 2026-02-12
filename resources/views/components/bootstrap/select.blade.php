{{-- resources/views/components/bootstrap/select.blade.php --}}
@props([
    'id',
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => '-- Select --',
    'required' => false,
    'multiple' => false,
])

<select id="{{ $id }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}"
    {{ $required && !$multiple ? 'required' : '' }} {{ $multiple ? 'multiple' : '' }}
    {{ $attributes->merge(['class' => 'form-control form-control-sm ']) }}>
    @if (!$multiple)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $key => $label)
        @if ($multiple && is_array($value))
            <option value="{{ $key }}" {{ in_array((string) $key, $value) ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @else
            <option value="{{ $key }}" {{ (string) $value === (string) $key ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endif
    @endforeach
</select>
