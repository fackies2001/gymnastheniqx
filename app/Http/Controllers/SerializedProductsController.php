<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SkuHelper;
use App\Helpers\TransactionHelper;
use App\Http\Requests\StoreSerializationRequest;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest;
use App\Models\SerialNumber;
use App\Services\DatatableServices;
use App\Services\SupplierProductServices;
use App\Services\SystemProductServices;
use Picqer\Barcode\BarcodeGeneratorPNG;
use OpenAI;
use Illuminate\Support\Facades\Storage;

class SerializedProductsController extends Controller
{
    protected $supplierProductServices;
    protected $datatableServices;
    protected $systemProductServices;

    public function __construct(
        SupplierProductServices $supplierProductServices,
        DatatableServices $datatableServices,
        SystemProductServices $systemProductServices
    ) {
        $this->supplierProductServices = $supplierProductServices;
        $this->datatableServices = $datatableServices;
        $this->systemProductServices = $systemProductServices;
    }

    public function index()
    {
        return view('serialized_products._index');
    }

    public function _index()
    {
        return view('serialized_products._index');
    }


    /**
     * ✅ FIXED: Count items per status
     */
    public function indexTable()
    {
        $query = SupplierProduct::with(['supplier'])
            ->withCount([
                'serializedProducts as available_count' => function ($query) {
                    $query->where('status', 1); // Available only
                },
                'serializedProducts as reserved_count' => function ($query) {
                    $query->where('status', 2); // Reserved
                },
                'serializedProducts as sold_count' => function ($query) {
                    $query->where('status', 3); // Sold
                },
                'serializedProducts as damaged_count' => function ($query) {
                    $query->where('status', 4); // Damaged
                },
                'serializedProducts as lost_count' => function ($query) {
                    $query->where('status', 5); // Lost
                }
            ]);

        return $this->datatableServices->get_serialized_products_summary_table($query);
    }

    public function show($id, $product_name)
    {
        $product = SupplierProduct::findOrFail($id);
        return view('serialized_products.show', [
            'supplier_product_id' => $id,
            'product_name' => $product->name,
            'product' => $product
        ]);
    }

    public function showTable($id = null)
    {
        return $this->datatableServices->get_serialized_product_table($id);
    }

    /**
     * ✅ UPDATED: Store with inventory increment
     */
    public function store(StoreSerializationRequest $request)
    {
        $validated = $request->validated();
        $Abbrv = 'SRN';

        $warehouseId = $validated['warehouse_id'] ?? auth()->user()->employee->assigned_at;

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'The warehouse id field is required. Please update your profile.'
            ], 422);
        }

        return TransactionHelper::run(function () use ($validated, $Abbrv, $warehouseId) {
            $bulkRecords = [];
            $baseSku = SkuHelper::generateSystemSku($Abbrv);

            foreach ($validated['sku_id'] as $sku) {
                // ✅ NEW: Update inventory quantity
                $product = SupplierProduct::find($sku['id']);
                if ($product) {
                    $product->increment('quantity', $sku['qty']); // Add to inventory
                }

                for ($i = 0; $i < $sku['qty']; $i++) {
                    $uniqueSerial = $baseSku . '-' . strtoupper(substr(uniqid(), -5)) . '-' . $i;

                    $bulkRecords[] = [
                        'sku_id'            => $sku['id'],
                        'serial_number'     => $uniqueSerial,
                        'purchase_order_id' => $validated['purchase_order_id'],
                        'product_status_id' => $validated['product_status_id'],
                        'warehouse_id'      => $warehouseId,
                        'scanned_by'        => auth()->user()->employee->id,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            SerialNumber::insert($bulkRecords);

            if ($purchaseRequest = PurchaseRequest::find($validated['purchase_request_id'])) {
                $purchaseRequest->update(['status_id' => 9]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Serial numbers saved successfully!',
            ]);
        });
    }

    /**
     * ✅ UPDATED: Overview with barcode generation and complete relationships
     * Now queries from serialized_product table
     */
    public function overview($serial_number = null)
    {
        if (!$serial_number) {
            return redirect()->route('serialized_products._index')
                ->with('error', 'Paki-scan o i-type ang Serial Number.');
        }

        // ✅ CHANGED: Use SerializedProduct model with ALL relationships
        $serialized_product_details = \App\Models\SerializedProduct::with([
            'supplierProducts.supplier',
            'productStatus',
            'purchaseOrder',
            'scannedBy',      // ✅ ADDED: For "Scanned By" name
            'warehouse'       // ✅ ADDED: For "Location" name
        ])
            ->where('serial_number', $serial_number)
            ->first();

        if (!$serialized_product_details) {
            return redirect()->route('serialized_products._index')
                ->with('error', "Serial Number [ $serial_number ] hindi nahanap sa system.");
        }

        // ✅ NEW: Generate barcode image
        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = base64_encode(
            $generator->getBarcode($serial_number, $generator::TYPE_CODE_128, 3, 80)
        );

        // ✅ SAFE ACCESS: Check if supplierProducts exists before accessing name
        $productName = $serialized_product_details->supplierProducts->name ?? 'Unknown Product';

        // ✅ NEW: Get product image (placeholder or AI-generated)
        $productImage = $serialized_product_details->supplierProducts->image_url ?? null;

        if (!$productImage) {
            $productImage = $this->generateProductPlaceholder($productName);
        }

        return view('serialized_products.overview', compact(
            'serialized_product_details',
            'serial_number',
            'barcodeImage',
            'productImage'
        ));
    }

    /**
     * ✅ UPGRADED: Generate product image (AI or placeholder)
     */
    private function generateProductPlaceholder($productName)
    {
        // Option 1: Use OpenAI DALL-E if API key exists
        if (env('OPENAI_API_KEY')) {
            return $this->generateAIImage($productName);
        }

        // Option 2: Fallback to free placeholder
        $encodedName = urlencode($productName);
        return "https://ui-avatars.com/api/?name={$encodedName}&size=400&background=random&color=fff&bold=true&format=png";
    }

    /**
     * ✅ NEW: Generate real AI image using DALL-E
     */
    private function generateAIImage($productName)
    {
        try {
            // 1. Check if image already exists
            $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $productName);
            $filename = "{$sanitizedName}.png";
            $storagePath = "products/{$filename}";

            if (Storage::disk('public')->exists($storagePath)) {
                return asset("storage/{$storagePath}");
            }

            // 2. Generate image using OpenAI DALL-E
            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $response = $client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => "Professional product photography of {$productName}, clean white background, studio lighting, high quality, centered composition, commercial product shot",
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
            ]);

            $imageUrl = $response->data[0]->url;

            // 3. Download and save image
            $imageContent = file_get_contents($imageUrl);
            Storage::disk('public')->put($storagePath, $imageContent);

            // 4. Return public URL
            return asset("storage/{$storagePath}");
        } catch (\Exception $e) {
            // 5. Fallback to placeholder on error
            \Log::error('OpenAI Image Generation Error: ' . $e->getMessage());

            $encodedName = urlencode($productName);
            return "https://ui-avatars.com/api/?name={$encodedName}&size=400&background=random&color=fff&bold=true&format=png";
        }
    }

    public function serialized_products_table(Request $request)
    {
        return $this->datatableServices->get_serialized_products_table();
    }

    public function serialized_product_datatable($id = null)
    {
        return $this->datatableServices->get_serialized_product_table($id);
    }

    /**
     * ✅ UPDATED: Update status with Sweet Alert response
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // ✅ VALIDATE status_id
            $request->validate([
                'status_id' => 'required|exists:product_status,id'
            ]);

            $item = \App\Models\SerializedProduct::findOrFail($id);

            // ✅ LOG BEFORE UPDATE (para makita mo kung ano ang status before/after)
            \Log::info("Status Update Request", [
                'product_id' => $id,
                'old_status' => $item->status,
                'new_status_id' => $request->status_id,
                'requested_by' => auth()->user()->name ?? 'Unknown'
            ]);

            // ✅ VERIFY STATUS EXISTS
            $statusExists = \App\Models\ProductStatus::find($request->status_id);
            if (!$statusExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status ID'
                ], 400);
            }

            // Update status
            $item->status = $request->status_id;

            if ($request->has('remarks')) {
                $item->remarks = $request->remarks;
            }

            $item->save();

            // ✅ REFRESH MODEL to get updated relationship
            $item->refresh();

            // ✅ LOG AFTER UPDATE
            \Log::info("Status Updated Successfully", [
                'product_id' => $id,
                'new_status' => $item->status,
                'status_name' => $item->productStatus->name ?? 'Unknown'
            ]);

            // Decrease inventory when sold/damaged/lost
            if (in_array($request->status_id, [3, 4, 5])) {
                $product = $item->supplierProducts;
                if ($product && $product->stock > 0) {
                    $product->decrement('stock', 1);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'status_id' => $item->status,
                'status_name' => $item->productStatus->name ?? 'Unknown'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Status Update Error", [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}
