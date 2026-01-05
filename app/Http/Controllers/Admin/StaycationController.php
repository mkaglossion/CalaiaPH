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
            'quantity' => 'required|integer|min:1',
            'priceConfigs' => 'required|json',
        ]);

        Staycation::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price_configs' => json_decode($request->priceConfigs, true),
        ]);

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
            'quantity' => 'required|integer|min:1',
            'priceConfigs' => 'required|json',
        ]);

        $staycation_list->update([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price_configs' => json_decode($request->priceConfigs, true),
        ]);

        return response()->json(['message' => 'Staycation updated successfully.']);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Staycation deleted successfully!');
    }
}
