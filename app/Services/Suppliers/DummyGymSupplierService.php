<?php

namespace App\Services\Suppliers;

use Illuminate\Support\Facades\Http;

class DummyGymSupplierService implements SupplierApiInterface
{
    protected $apiUrl;
    protected $headers;
    public function __construct(string $apiUrl, array $headers = null)
    {
        $this->apiUrl = $apiUrl;
        $this->headers = $headers ?? []; // use empty array if null
    }

    public function fetchProducts(): array
    {
        $response = Http::withHeaders($this->headers)->get($this->apiUrl);

        if ($response->successful()) {
            return collect($response['products'])->map(function ($item) {
                return [
                    // 🗂 Categories Table
                    'category' => $item['category'],

                    // 📦 Products Table
                    'name' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'cost_price' => $item['price'],
                    'discount' => $item['discountPercentage'] ?? null,
                    'rating' => $item['rating'] ?? null,
                    'stock' => $item['stock'] ?? null,
                    'minimum_order_quantity' => $item['minimumOrderQuantity'] ?? null,
                    'warranty_information' => $item['warrantyInformation'] ?? null,
                    'return_policy' => $item['returnPolicy'] ?? null,
                    'shipping_information' => $item['shippingInformation'] ?? null,
                    'availability_status' => $item['availabilityStatus'] ?? null,

                    // 🆔 SKU Table
                    'sku' => $item['sku'],
                    'weight' => $item['weight'] ?? null,
                    'dimensions_width' => $item['dimensions']['width'] ?? null,
                    'dimensions_height' => $item['dimensions']['height'] ?? null,
                    'dimensions_depth' => $item['dimensions']['depth'] ?? null,

                    // 📊 Supplier Barcodes Table
                    'barcode' => $item['meta']['barcode'] ?? null,

                    // 🖼 Images (could be linked to separate product_images table)
                    'thumbnail' => $item['thumbnail'] ?? null,
                    'images' => json_encode($item['images'] ?? []), // optional: save as JSON

                    // 🧾 Meta Information (Optional)
                    'created_at_api' => $item['meta']['createdAt'] ?? null,
                    'updated_at_api' => $item['meta']['updatedAt'] ?? null,
                ];
            })->toArray();
        }

        return [];
    }
}
