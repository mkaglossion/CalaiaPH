<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaycationPageText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaycationPageTextController extends Controller
{
    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_label' => 'nullable|string|max:255',
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $fields = [
                'staycation_page_label' => $validated['page_label'] ?? '',
                'staycation_page_title' => $validated['page_title'] ?? '',
                'staycation_page_intro' => $validated['page_intro'] ?? '',
            ];

            foreach ($fields as $label => $text) {
                StaycationPageText::updateOrCreate(
                    ['label' => $label],
                    ['text' => trim((string) $text)]
                );
            }
        });

        return response()->json([
            'message' => 'Staycation page copy saved successfully.',
        ]);
    }
}
