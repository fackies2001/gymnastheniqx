@props(['id', 'type' => 'text', 'name', 'value' => '', 'required' => false, 'placeholder' => ''])

<input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'form-control form-control-sm']) }}>
