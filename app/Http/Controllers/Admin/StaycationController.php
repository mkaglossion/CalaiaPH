<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;

class StaycationController extends Controller
{
    public function index()
    {
        $staycations = Staycation::latest()->get();
        return view('admin.staycations', compact('staycations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name',
        ]);

        Staycation::create($request->only('name'));

        return response()->json(['message' => 'Staycation added successfully.']);

    }

    // public function show(Staycation $staycation)
    // {
    //     $staycation->load(['swiperImages', 'texts']);
    //     return view('admin.activities', compact('staycation'));
    // }

    public function update(Request $request, Staycation $staycation_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name,' . $staycation_list->id,
        ]);

        $staycation_list->update($request->only('name'));

        return response()->json(['message' => 'Staycation updated successfully.']);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Staycation deleted successfully!');
    }
}
