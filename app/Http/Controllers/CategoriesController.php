<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    //

    public function create(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Categories::create([
            'name' => $validated['category'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }
}
