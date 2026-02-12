<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'po_number'     => 'required|string|max:50|unique:purchase_orders,po_number',
            'order_date'    => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'remarks'       => 'nullable|string|max:1000',

            // DAGDAG ITO PARA SA PR LINK
            'request_id'    => 'nullable|integer|exists:purchase_requests,id',  // o 'sometimes|nullable|exists:purchase_requests,id'

            // Required items
            'received_items'               => 'required|array|min:1',
            'received_items.*.supplier_product_id' => 'required|exists:supplier_products,id',
            'received_items.*.received_qty'        => 'required|integer|min:0',
            'received_items.*.unit_cost'           => 'required|numeric|min:0',
            'received_items.*.ordered_qty'         => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'received_items.required' => 'Please add at least one received item.',
            'received_items.min'      => 'Please add at least one received item.',
        ];
    }
}
