<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodPackageSetting;
use Illuminate\Http\Request;

class FoodPackageSettingController extends Controller
{
    public function index()
    {
        $settings = FoodPackageSetting::all();
        
        return view('admin.food.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'required|string|max:100',
            'show' => 'required|boolean',
        ]);

        FoodPackageSetting::where('key', 'title')->update(['value' => $validated['title']]);
        FoodPackageSetting::where('key', 'description')->update(['value' => $validated['description'] ?? '']);
        FoodPackageSetting::where('key', 'icon')->update(['value' => $validated['icon']]);
        FoodPackageSetting::where('key', 'show')->update(['value' => $validated['show'] ? '1' : '0']);

        return redirect()->route('admin.food.settings.index')
            ->with('success', 'Food packages settings updated successfully!');
    }
}
