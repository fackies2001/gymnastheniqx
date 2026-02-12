<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreSupplierProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ✅ ADDED: Prepare data for validation
     * This runs BEFORE validation, so we can transform the data
     */
    protected function prepareForValidation()
    {
        // Convert category_id to category for the Service layer
        if ($this->has('category_id')) {
            $this->merge([
                'category' => $this->category_id,
            ]);
        }
    }

    // Validation rules
    public function rules(): array
    {
        if (auth()->user()->is_student) {
            return [
                'supplier_id' => 'required|integer|exists:supplier,id',
                'category_id' => 'required|integer|exists:category,id',
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255', // ✅ REMOVED: unique constraint
                'cost_price' => 'required|numeric|min:0',
                'barcode' => 'required|string|max:255',
                'is_consumable' => 'nullable|boolean',
            ];
        } else {
            return [
                'category_id' => 'required|integer|exists:category,id',
                'description' => 'nullable|string|max:1000',

                // Supplier
                'supplier_id' => 'required|integer|exists:supplier,id',

                // Product
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255', // ✅ REMOVED: unique constraint
                'cost_price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'availability_status' => 'nullable|string|in:in_stock,out_of_stock,pre_order',

                // Shipping / Warranty / Return
                'shipping_information' => 'nullable|string|max:1000',
                'warranty_information' => 'nullable|string|max:1000',
                'return_policy' => 'nullable|string|max:1000',

                // Dimensions
                'weight' => 'nullable|string|max:255',
                'dimensions_width' => 'nullable|string|max:255',
                'dimensions_height' => 'nullable|string|max:255',
                'dimensions_depth' => 'nullable|string|max:255',

                // Other
                'barcode' => 'required|string|max:255',
                'thumbnail' => 'nullable|url|max:1000',
                'images' => 'nullable|array',
                'images.*' => 'nullable|url|max:1000',
                'is_consumable' => 'nullable|boolean',
            ];
        }
    }

    /**
     * ✅ ADDED: Custom error messages
     */
    public function messages()
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'name.required' => 'Product name is required.',
            'cost_price.required' => 'Cost price is required.',
            'barcode.required' => 'Barcode is required.',
        ];
    }

    public function forbiddenResponse()
    {
        abort(403, 'You are not allowed to store supplier products.');
    }
}
