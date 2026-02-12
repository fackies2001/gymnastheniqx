<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_number' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'products' => 'required|array|min:1',  // Min:1 para sa "at least one product"
            'products.*.supplier_product_id' => 'required|exists:supplier_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
            'products.*.barcode' => 'nullable|string|max:255',
            'products.*.subtotal' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'products.min' => 'Please add at least one product.',
            // ... other messages
        ];
    }
}
