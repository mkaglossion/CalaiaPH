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

    public function updateCategoryLabel(Request $request)
    {
        $categoryDefinitions = FoodPackageSetting::getPackageCategoryLabelDefinitions();
        $sectionDefinitions = FoodPackageSetting::getFoodBookingSectionDefinitions();

        $rules = [];
        foreach ($categoryDefinitions as $definition) {
            $rules[$definition['setting_key']] = 'required|string|max:255';
        }
        foreach ($sectionDefinitions as $definition) {
            $rules[$definition['heading_key']] = $definition['heading_key'] === 'messhall_section_heading'
                ? 'nullable|string|max:255'
                : 'required|string|max:255';
            $rules[$definition['description_key']] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        foreach ($categoryDefinitions as $definition) {
            FoodPackageSetting::updateOrCreate(
                ['key' => $definition['setting_key']],
                [
                    'label' => $definition['label'],
                    'value' => trim($validated[$definition['setting_key']]),
                    'description' => $definition['description'],
                ]
            );
        }

        foreach ($sectionDefinitions as $definition) {
            FoodPackageSetting::updateOrCreate(
                ['key' => $definition['heading_key']],
                [
                    'label' => $definition['label'] . ' Heading',
                    'value' => trim($validated[$definition['heading_key']]),
                    'description' => 'Booking section heading.',
                ]
            );

            FoodPackageSetting::updateOrCreate(
                ['key' => $definition['description_key']],
                [
                    'label' => $definition['label'] . ' Description',
                    'value' => trim($validated[$definition['description_key']]),
                    'description' => 'Booking section description.',
                ]
            );
        }

        return redirect()->back()->with('success', 'Food package display settings updated successfully!');
    }
}
