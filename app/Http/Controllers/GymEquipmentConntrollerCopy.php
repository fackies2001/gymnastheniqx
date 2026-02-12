<?php
/*
namespace App\Http\Controllers;

use App\Models\GymEquipment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class GymEquipmentController extends Controller
{
    public function index()
    {
        return view('gym_equipments.index');
    }

    // DataTables with Date Filtering
    public function getEquipments(Request $request)
    {
        $query = GymEquipment::query();

        // Apply date filters if present
        if ($request->has('filter')) {
            $filter = $request->filter;

            if (isset($filter['type'])) {
                switch ($filter['type']) {
                    case 'date':
                        if (isset($filter['value'])) {
                            $query->whereDate('created_at', $filter['value']);
                        }
                        break;

                    case 'month':
                        if (isset($filter['month']) && isset($filter['year'])) {
                            $query->whereMonth('created_at', $filter['month'])
                                ->whereYear('created_at', $filter['year']);
                        }
                        break;

                    case 'year':
                        if (isset($filter['year'])) {
                            $query->whereYear('created_at', $filter['year']);
                        }
                        break;
                }
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                $badges = [
                    'Available' => 'badge-success',
                    'Under Maintenance' => 'badge-warning',
                    'Out of Order' => 'badge-danger'
                ];
                $class = $badges[$row->status] ?? 'badge-secondary';
                return '<span class="badge ' . $class . '">' . $row->status . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('M d, Y');
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-info edit-btn" data-id="' . $row->id . '" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    // Store - Auto-generate item code
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Under Maintenance,Out of Order'
        ]);

        // Auto-generate item code (GYM-001, GYM-002, etc.)
        $lastEquipment = GymEquipment::orderBy('id', 'desc')->first();
        $nextNumber = $lastEquipment ? (intval(substr($lastEquipment->item_code, 4)) + 1) : 1;
        $validated['item_code'] = 'GYM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        GymEquipment::create($validated);

        return response()->json(['message' => 'Gym equipment successfully added!']);
    }

    // Edit - Get single equipment
    public function edit($id)
    {
        $equipment = GymEquipment::findOrFail($id);
        return response()->json($equipment);
    }

    // Update
    public function update(Request $request, $id)
    {
        $equipment = GymEquipment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Under Maintenance,Out of Order'
        ]);

        $equipment->update($validated);

        return response()->json(['message' => 'Gym equipment successfully updated!']);
    }

    // Delete
    public function destroy($id)
    {
        $equipment = GymEquipment::findOrFail($id);
        $equipment->delete();

        return response()->json(['message' => 'Gym equipment successfully deleted!']);
    }

    // Print/Export with Filters
    public function print(Request $request)
    {
        $query = GymEquipment::query();
        $filterLabel = 'All Records';

        // Apply filters
        if ($request->has('type')) {
            switch ($request->type) {
                case 'date':
                    if ($request->has('value')) {
                        $query->whereDate('created_at', $request->value);
                        $filterLabel = 'Date: ' . Carbon::parse($request->value)->format('F d, Y');
                    }
                    break;

                case 'month':
                    if ($request->has('month') && $request->has('year')) {
                        $query->whereMonth('created_at', $request->month)
                            ->whereYear('created_at', $request->year);
                        $filterLabel = Carbon::create($request->year, $request->month)->format('F Y');
                    }
                    break;

                case 'year':
                    if ($request->has('year')) {
                        $query->whereYear('created_at', $request->year);
                        $filterLabel = 'Year: ' . $request->year;
                    }
                    break;
            }
        }

        $equipments = $query->orderBy('created_at', 'desc')->get();

        return view('gym_equipments.report', compact('equipments', 'filterLabel'));
    }
}

feb 11
