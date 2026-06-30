<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:shop_texts,label',
            'content' => 'nullable|string',
        ]);

        ShopText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function update(Request $request, ShopText $shopText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:shop_texts,label,' . $shopText->id,
            'content' => 'nullable|string',
        ]);

        $shopText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function destroy(ShopText $shopText)
    {
        $shopText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }

    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            ShopText::updateOrCreate(
                ['label' => 'shop_page_title'],
                ['text' => $validated['page_title'] ?? '']
            );

            ShopText::updateOrCreate(
                ['label' => 'shop_page_intro'],
                ['text' => $validated['page_intro'] ?? '']
            );
        });

        return response()->json([
            'message' => 'Shop page copy saved successfully.',
        ]);
    }
}
