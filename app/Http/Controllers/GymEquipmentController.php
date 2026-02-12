<?php

namespace App\Http\Controllers;

use App\Models\GymEquipment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class GymEquipmentController extends Controller
{
    public function index()
    {
        return view('gym-equipments.index');
    }

    public function getEquipments(Request $request)
    {
        $query = GymEquipment::query();

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
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary edit-btn" data-id="' . $row->id . '" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Under Maintenance,Out of Order'
        ]);

        $lastEquipment = GymEquipment::orderBy('id', 'desc')->first();
        $nextNumber = $lastEquipment ? (intval(substr($lastEquipment->item_code, 4)) + 1) : 1;
        $validated['item_code'] = 'GYM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        GymEquipment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gym equipment successfully added!'
        ]);
    }

    public function edit($id)
    {
        $equipment = GymEquipment::findOrFail($id);
        return response()->json($equipment);
    }

    public function update(Request $request, $id)
    {
        $equipment = GymEquipment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:Available,Under Maintenance,Out of Order'
        ]);

        $equipment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gym equipment successfully updated!'
        ]);
    }

    public function destroy($id)
    {
        $equipment = GymEquipment::findOrFail($id);
        $equipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gym equipment successfully deleted!'
        ]);
    }

    public function print(Request $request)
    {
        $query = GymEquipment::query();
        $filterLabel = 'All Records';

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

        return view('gym-equipments.print-report', compact('equipments', 'filterLabel'));
    }
}
