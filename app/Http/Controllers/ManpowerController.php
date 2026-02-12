<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class ManpowerController extends Controller
{
    public function index()
    {
        return view('manpower.index');
    }

    public function get_coaches_data()
    {
        $coaches = Coach::select([
            'id',
            'full_name',
            'contact_no',
            'email',
            'address',
            'position',
            'date_hired',
            'status'
        ])->orderBy('created_at', 'desc');

        return DataTables::of($coaches)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                // ✅ EXACT SAME SA USER MANAGEMENT STYLE
                $editBtn = '<button class="btn btn-sm btn-success edit-coach" data-id="' . $row->id . '">
                                <i class="fas fa-edit"></i> Edit
                            </button>';

                $deleteBtn = '<button class="btn btn-sm btn-danger delete-coach" data-id="' . $row->id . '">
                                <i class="fas fa-trash"></i> Delete
                              </button>';

                return $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'  => 'required|string|max:255',
            'birth_date' => 'required|date',
            'contact_no' => 'required|string',
            'email'      => 'required|email|unique:coaches,email',
            'address'    => 'required|string',
            'position'   => 'required|string',
            'date_hired' => 'required|date',
            'status'     => 'required|string',
        ]);

        try {
            Coach::create($request->all());

            return response()->json([
                'success' => 'New coach added successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'error' => 'Something went wrong.'
            ], 500);
        }
    }

    public function edit($id)
    {
        $coach = Coach::findOrFail($id);
        return response()->json($coach);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name'  => 'required',
            'email'      => 'required|email|unique:coaches,email,' . $id,
        ]);

        $coach = Coach::findOrFail($id);
        $coach->update($request->all());

        if ($request->ajax()) {
            return response()->json(['success' => 'Coach updated successfully!']);
        }

        return redirect()->route('manpower.index')->with('success', 'Coach updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $coach = Coach::findOrFail($id);
            $coach->delete();

            return response()->json([
                'success' => 'Coach record has been deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error("Delete Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete coach. Please try again.'
            ], 500);
        }
    }
}
