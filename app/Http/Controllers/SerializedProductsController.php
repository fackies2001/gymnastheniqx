<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SkuHelper;
use App\Helpers\TransactionHelper;
use App\Http\Requests\StoreSerializationRequest;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest; 
use App\Models\SerialNumber;
use App\Models\StockMovement;
use App\Models\ConsumableStock;
use App\Models\SerializedProduct;
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

    public function indexTable()
    {
        $query = SupplierProduct::with(['supplier'])
            ->where('is_consumable', false)  // ← DAGDAG LANG ITO
            ->withCount([
                'serializedProducts as available_count' => function ($query) {
                    $query->where('status', 1);
                },
                'serializedProducts as reserved_count' => function ($query) {
                    $query->where('status', 2);
                },
                'serializedProducts as sold_count' => function ($query) {
                    $query->where('status', 3);
                },
                'serializedProducts as damaged_count' => function ($query) {
                    $query->where('status', 4);
                },
                'serializedProducts as lost_count' => function ($query) {
                    $query->where('status', 5);
                }
            ]);

        return $this->datatableServices->get_serialized_products_summary_table($query);
    }

    // =========================================================
    // ✅ PINALITAN — show()
    // DATI: Naglo-load lang ng view at nagpapasa ng product info
    // NGAYON: Dagdag na ang $stock (ConsumableStock) para
    //         ma-display ang current quantity at low stock status
    //         sa left card ng show.blade.php
    // =========================================================
    public function show($id, $product_name)
    {
        $product = SupplierProduct::with('supplier')->findOrFail($id);
        $warehouseId = auth()->user()->assigned_at;

        $stock = ConsumableStock::where('product_id', $id)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->first();

        // ✅ DAGDAG — kung non-consumable, kuha sa serialized_product
        $currentStock = $stock?->current_qty ?? SerializedProduct::where('product_id', $id)
            ->where('status', 1)
            ->count();

        return view('serialized_products.show', [
            'supplier_product_id' => $id,
            'product_name'        => $product->name,
            'product'             => $product,
            'stock'               => $stock,
            'current_stock'       => $currentStock, // ✅ DAGDAG
        ]);
    }

    // =========================================================
    // ✅ PINALITAN — showTable()
    // DATI: Nagbabalik ng SRN (serial number) list
    // NGAYON: Nagbabalik ng stock movement history
    //         Para gamitin ng DataTable sa show.blade.php
    // =========================================================
    public function showTable($id = null)
    {
        // ✅ Kung walang id, ibalik ng empty data
        if (!$id) {
            return response()->json(['data' => []]);
        }

        $warehouseId = auth()->user()->assigned_at;

        $movements = StockMovement::with(['createdBy', 'purchaseOrder', 'retailerOrder'])
            ->where('product_id', $id)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->get()
            ->map(function ($m) {

                // ✅ Color coding per movement type
                $typeColors = [
                    'in'         => 'success',
                    'out'        => 'primary',
                    'damage'     => 'warning',
                    'loss'       => 'danger',
                    'adjustment' => 'secondary',
                ];

                // ✅ Icons per type
                $typeIcons = [
                    'in'         => '➕',
                    'out'        => '➖',
                    'damage'     => '❌',
                    'loss'       => '⚠️',
                    'adjustment' => '🔄',
                ];

                $color = $typeColors[$m->type] ?? 'secondary';
                $icon  = $typeIcons[$m->type] ?? '';

                // ✅ Quantity display — IN at positive ADJUSTMENT ay may + sign
                $isPositive = $m->type === 'in' ||
                    ($m->type === 'adjustment' && $m->quantity >= 0);

                $qtyDisplay = $isPositive
                    ? '<span class="text-success font-weight-bold">+' . abs($m->quantity) . ' pcs</span>'
                    : '<span class="text-danger font-weight-bold">−' . abs($m->quantity) . ' pcs</span>';

                // ✅ Reference — PO or Retailer Order
                $reference = '—';
                if ($m->purchase_order_id) {
                    $reference = '<span class="badge badge-light">PO #' . $m->purchase_order_id . '</span>';
                } elseif ($m->retailer_order_id) {
                    $reference = '<span class="badge badge-light">Order #' . $m->retailer_order_id . '</span>';
                }

                return [
                    'date'        => $m->created_at,
                    'type'        => '<span class="badge badge-' . $color . '">'
                        . $icon . ' ' . strtoupper($m->type)
                        . '</span>',
                    'quantity'    => $qtyDisplay,
                    'reason'      => $m->reason_type
                        ? ucwords(str_replace('_', ' ', $m->reason_type))
                        : '—',
                    'reference'   => $reference,
                    'recorded_by' => $m->createdBy->full_name ?? '—',
                    'remarks'     => $m->remarks ?? '—',
                ];
            });

        return response()->json(['data' => $movements]);
    }

    // =========================================================
    // ✅ HINDI BINAGO — store()
    // Para sa non-consumable / gym equipment serialization
    // =========================================================
    public function store(StoreSerializationRequest $request)
    {
        $validated = $request->validated();
        $Abbrv = 'SRN';

        $warehouseId = $validated['warehouse_id'] ?? auth()->user()->assigned_at;

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'The warehouse id field is required. Please update your profile.'
            ], 422);
        }

        return TransactionHelper::run(function () use ($validated, $Abbrv, $warehouseId) {
            $bulkRecords = [];
            $baseSku = SkuHelper::generateSystemSku($Abbrv);

            $scannedBy = auth()->id();
            $scannedAt = now();

            \Log::info('Product Serialization', [
                'user_id'      => auth()->id(),
                'user_name'    => auth()->user()->full_name,
                'scanned_by'   => $scannedBy,
                'warehouse_id' => $warehouseId,
                'timestamp'    => $scannedAt
            ]);

            foreach ($validated['sku_id'] as $sku) {
                $product = SupplierProduct::find($sku['id']);
                if ($product) {
                    $product->increment('quantity', $sku['qty']);
                }

                for ($i = 0; $i < $sku['qty']; $i++) {
                    $uniqueSerial = $baseSku . '-' . strtoupper(substr(uniqid(), -5)) . '-' . $i;

                    $bulkRecords[] = [
                        'product_id'        => $sku['id'],
                        'serial_number'     => $uniqueSerial,
                        'barcode'           => $uniqueSerial,
                        'purchase_order_id' => $validated['purchase_order_id'],
                        'status'            => $validated['product_status_id'] ?? 1,
                        'warehouse_id'      => $warehouseId,
                        'scanned_by'        => $scannedBy,
                        'scanned_at'        => $scannedAt,
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

    // =========================================================
    // ✅ HINDI BINAGO — overview()
    // Para sa non-consumable SRN overview page
    // =========================================================
    public function overview($serial_number = null)
    {
        if (!$serial_number) {
            return redirect()->route('serialized_products._index')
                ->with('error', 'Paki-scan o i-type ang Serial Number.');
        }

        $serialized_product_details = \App\Models\SerializedProduct::with([
            'supplierProducts.supplier',
            'productStatus',
            'purchaseOrder',
            'scannedBy',
            'warehouse'
        ])
            ->where('serial_number', $serial_number)
            ->first();

        if (!$serialized_product_details) {
            return redirect()->route('serialized_products._index')
                ->with('error', "Serial Number [ $serial_number ] hindi nahanap sa system.");
        }

        $generator = new BarcodeGeneratorPNG();

        $barcodeImage = base64_encode(
            $generator->getBarcode($serial_number, $generator::TYPE_CODE_128, 3, 80)
        );

        $productName  = $serialized_product_details->supplierProducts->name ?? 'Unknown Product';
        $productImage = $serialized_product_details->supplierProducts->image_url ?? null;

        if (!$productImage) {
            $productImage = $this->generateProductPlaceholder($productName);
        }

        $productBarcode      = $serialized_product_details->supplierProducts->barcode ?? null;
        $productBarcodeImage = null;

        if ($productBarcode) {
            $productBarcodeImage = base64_encode(
                $generator->getBarcode($productBarcode, $generator::TYPE_CODE_128, 3, 80)
            );
        }

        return view('serialized_products.overview', compact(
            'serialized_product_details',
            'serial_number',
            'barcodeImage',
            'productImage',
            'productBarcode',
            'productBarcodeImage'
        ));
    }

    // =========================================================
    // ✅ HINDI BINAGO — private helpers
    // =========================================================
    private function generateProductPlaceholder($productName)
    {
        if (env('OPENAI_API_KEY')) {
            return $this->generateAIImage($productName);
        }

        $encodedName = urlencode($productName);
        return "https://ui-avatars.com/api/?name={$encodedName}&size=400&background=random&color=fff&bold=true&format=png";
    }

    private function generateAIImage($productName)
    {
        try {
            $sanitizedName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $productName);
            $filename      = "{$sanitizedName}.png";
            $storagePath   = "products/{$filename}";

            if (Storage::disk('public')->exists($storagePath)) {
                return asset("storage/{$storagePath}");
            }

            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $response = $client->images()->create([
                'model'   => 'dall-e-3',
                'prompt'  => "Professional product photography of {$productName}, clean white background, studio lighting, high quality, centered composition, commercial product shot",
                'n'       => 1,
                'size'    => '1024x1024',
                'quality' => 'standard',
            ]);

            $imageUrl     = $response->data[0]->url;
            $imageContent = file_get_contents($imageUrl);
            Storage::disk('public')->put($storagePath, $imageContent);

            return asset("storage/{$storagePath}");
        } catch (\Exception $e) {
            \Log::error('OpenAI Image Generation Error: ' . $e->getMessage());

            $encodedName = urlencode($productName);
            return "https://ui-avatars.com/api/?name={$encodedName}&size=400&background=random&color=fff&bold=true&format=png";
        }
    }

    // =========================================================
    // ✅ HINDI BINAGO — datatable methods
    // =========================================================
    public function serialized_products_table(Request $request)
    {
        return $this->datatableServices->get_serialized_products_table();
    }

    public function serialized_product_datatable($id = null)
    {
        return $this->datatableServices->get_serialized_product_table($id);
    }

    // =========================================================
    // ✅ HINDI BINAGO — updateStatus()
    // Para sa non-consumable status updates
    // =========================================================
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status_id' => 'required|exists:product_status,id'
            ]);

            $item = \App\Models\SerializedProduct::findOrFail($id);

            \Log::info("Status Update Request", [
                'product_id'     => $id,
                'old_status'     => $item->status,
                'new_status_id'  => $request->status_id,
                'requested_by'   => auth()->user()->full_name ?? 'Unknown'
            ]);

            $statusExists = \App\Models\ProductStatus::find($request->status_id);
            if (!$statusExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status ID'
                ], 400);
            }

            $item->status = $request->status_id;

            if ($request->has('remarks')) {
                $item->remarks = $request->remarks;
            }

            $item->save();
            $item->refresh();

            \Log::info("Status Updated Successfully", [
                'product_id'  => $id,
                'new_status'  => $item->status,
                'status_name' => $item->productStatus->name ?? 'Unknown'
            ]);

            if (in_array($request->status_id, [3, 4, 5])) {
                $product = $item->supplierProducts;
                if ($product && $product->stock > 0) {
                    $product->decrement('stock', 1);
                }
            }

            return response()->json([
                'success'     => true,
                'message'     => 'Status updated successfully!',
                'status_id'   => $item->status,
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
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}
