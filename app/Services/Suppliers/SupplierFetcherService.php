<?php

namespace App\Services\Suppliers;

use App\Helpers\SkuHelper;
use App\Models\Products;
use App\Models\SerialNumbers;
use App\Models\SupplierApis;
use App\Models\Categories;
use App\Models\SupplierProducts;
use Illuminate\Support\Facades\Log;

class SupplierFetcherService
{
    public function fetchAllSuppliers()
    {
        $supplierApis = SupplierApis::first();

        if (!$supplierApis) {
            // Log::error('❌ No supplier API found in database.');
            return;
        }

        $class = $supplierApis->service_class;

        if (!class_exists($class)) {
            // Log::error("❌ Service class {$class} does not exist.");
            return;
        }

        try {
            $service = new $class($supplierApis->api_url, json_decode($supplierApis->headers, true));
            $products_api = $service->fetchProducts();


            // Log::info('✅ Successfully fetched products:', $products_api);

            // ✅ Now process only the first product to ensure structure is correct
            // $firstProduct = $products[0] ?? null;



            // if (!$firstProduct) {
            //     Log::warning('⚠️ No products returned from supplier API.');
            //     return;
            // }

            // // Just log the first product to inspect its structure
            // Log::info('🔍 First Product Structure:', $firstProduct);

            foreach ($products_api as $products_api_data) {
                // ✅ Automatically updates if exists or creates if not
                $category = Categories::updateOrCreate(
                    attributes: ['name' => $products_api_data['category']], // condition to check existing category
                    values: ['description' => $products_api_data['description'] ?? null]
                );


                SupplierProducts::updateOrCreate(
                    attributes: [
                        'supplier_id' => $supplierApis->id,
                        'category_id' => $category->id,
                        'name' => $products_api_data['name'] ?? null,
                        'supplier_sku' => $products_api_data['sku'] ?? null
                    ],
                    values: [
                        'system_sku' => SkuHelper::generateSystemSku('SYS'),
                        'cost_price' => $products_api_data['cost_price'] ?? null,
                        'discount' => $products_api_data['discount'] ?? null,
                        'stock' => $products_api_data['stock'] ?? null,
                        'availability_status' => $products_api_data['availability_status'] ?? null,
                        'shipping_information' => $products_api_data['shipping_information'] ?? null,
                        'warranty_information' => $products_api_data['warranty_information'] ?? null,
                        'return_policy' => $products_api_data['return_policy'] ?? null,
                        'dimensions' => json_encode(value: [
                            'weight' => $products_api_data['weight'] ?? null,
                            'width' => $products_api_data['dimensions_width'] ?? null,
                            'height' => $products_api_data['dimensions_height'] ?? null,
                            'depth' => $products_api_data['dimensions_depth'] ?? null,
                        ]),

                        'barcode' => $products_api_data['barcode'] ?? null,
                        'thumbnail' => $products_api_data['thumbnail'] ?? null,
                        'images' => json_encode(value: $products_api_data['images']) ?? null,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Log::error('❌ Error in SupplierFetcherService: ' . $e->getMessage());
        }
        // foreach ($supplierApis as $supplierApi) {
        //     $class = $supplierApi->service_class;

        //     if (class_exists($class)) {
        //         $service = new $class($supplierApi->api_url);
        //         $products = $service->fetchProducts(); // This returns an array of product data
        //         Log::info(message: $products);
        //         foreach ($products as $productData) {
        //             // ✅ Automatically updates if exists or creates if not
        //             $supplierApi->supplier()->updateOrCreate(
        //                 ['id' => $productData['id']], // condition to check existing supplier
        //                 [
        //                     'name' => $productData['name'],
        //                     'phone' => $productData['phone'] ?? null,
        //                     'email' => $productData['email'] ?? null,
        //                     'address' => $productData['address'] ?? null,
        //                 ]
        //             );
        //         }
        //     }
        // }
    }
}
