<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\SystemProductServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public $systemProductServices;

    public function __construct(SystemProductServices $systemProductServices)
    {
        $this->systemProductServices = $systemProductServices;
    }

    public function index()
    {
        $warehouses = Warehouse::all();
        return view('warehouse.index', compact('warehouses'));
    }

    public function display()
    {
        $warehouses = $this->systemProductServices->get_warehouse_pluck_name_id();
        return response()->json($warehouses);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'assignee' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $warehouse = Warehouse::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'assignee' => $request->assignee,
                'location' => $request->address,
                'description' => 'New warehouse record',
                'is_active' => 1
            ]);

            Log::info('Warehouse created successfully', ['id' => $warehouse->id]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse saved successfully!',
                'data' => $warehouse
            ], 201);
        } catch (\Exception $e) {
            Log::error('Warehouse creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to save warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:warehouse,id',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'assignee' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $warehouse = Warehouse::findOrFail($request->id);

            $warehouse->update([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'assignee' => $request->assignee,
                'location' => $request->address,
            ]);

            Log::info('Warehouse updated successfully', ['id' => $warehouse->id]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully!',
                'data' => $warehouse
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Warehouse not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to update warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:warehouse,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $warehouse = Warehouse::findOrFail($request->id);
            $warehouseName = $warehouse->name;

            $warehouse->delete();

            Log::info('Warehouse deleted successfully', [
                'id' => $request->id,
                'name' => $warehouseName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Warehouse not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Warehouse deletion failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to delete warehouse: ' . $e->getMessage()
            ], 500);
        }
    }
}
