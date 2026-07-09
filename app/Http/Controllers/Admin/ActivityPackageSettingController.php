<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityPackageSetting;
use Illuminate\Http\Request;

class ActivityPackageSettingController extends Controller
{
    public function updateCategoryLabel(Request $request)
    {
        $categoryDefinitions = ActivityPackageSetting::getCategoryLabelDefinitions();
        $sectionDefinitions = ActivityPackageSetting::getBookingSectionDefinitions();

        $rules = [];

        foreach ($categoryDefinitions as $definition) {
            $rules[$definition['setting_key']] = 'required|string|max:255';
        }

        foreach ($sectionDefinitions as $definition) {
            $rules[$definition['heading_key']] = 'required|string|max:255';
            $rules[$definition['description_key']] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        foreach ($categoryDefinitions as $definition) {
            ActivityPackageSetting::updateOrCreate(
                ['key' => $definition['setting_key']],
                [
                    'label' => $definition['label'],
                    'value' => trim($validated[$definition['setting_key']]),
                    'description' => $definition['description'],
                ]
            );
        }

        foreach ($sectionDefinitions as $definition) {
            ActivityPackageSetting::updateOrCreate(
                ['key' => $definition['heading_key']],
                [
                    'label' => $definition['label'] . ' Heading',
                    'value' => trim($validated[$definition['heading_key']]),
                    'description' => 'Booking section heading.',
                ]
            );

            ActivityPackageSetting::updateOrCreate(
                ['key' => $definition['description_key']],
                [
                    'label' => $definition['label'] . ' Description',
                    'value' => trim($validated[$definition['description_key']]),
                    'description' => 'Booking section description.',
                ]
            );
        }

        return redirect()->back()->with('success', 'Activity display settings updated successfully!');
    }
}
