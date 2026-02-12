@props([
    'id' => '',
    'name' => '',
    'value' => '',
    'rows' => 3,
    'placeholder' => '',
])

<textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
