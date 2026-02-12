<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSerializationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'purchase_request_id' => 'required|integer|exists:purchase_requests,id',
            'product_status_id' => 'required|integer|exists:product_status,id',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'sku_id' => 'required|array|min:1',
            'sku_id.*.id' => 'required|integer|exists:supplier_products,id',
            'sku_id.*.name' => 'required|string',
            'sku_id.*.price' => 'required|numeric|min:0',
            'sku_id.*.qty' => 'required|integer|min:1',
        ];
    }
}
