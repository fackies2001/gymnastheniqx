@php
    $images = json_decode($product, true);
    // Step 2: decode JSON array
    // $images = json_decode($firstDecode, true);

    // Step 3: fallback to default if decode fails
@endphp

@foreach ($images as $img)
    <img src="{{ $img }}" alt="" width="50">
@endforeach
