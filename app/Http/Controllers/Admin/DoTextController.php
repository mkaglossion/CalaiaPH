<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoTextController extends Controller
{
    public function index()
    {
        $texts = DoText::latest()->get();
        return view('admin.do_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:do_texts,label',
            'content' => 'nullable|string',
        ]);

        DoText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function update(Request $request, DoText $DoText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:do_texts,label,' . $DoText->id,
            'content' => 'nullable|string',
        ]);

        $DoText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function destroy(DoText $DoText)
    {
        $DoText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }

    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $titleText = DoText::updateOrCreate(
                ['label' => 'packages_title'],
                ['text' => trim((string) ($validated['page_title'] ?? ''))]
            );
            DoText::where('label', 'packages_title')
                ->where('id', '!=', $titleText->id)
                ->delete();

            $introText = DoText::updateOrCreate(
                ['label' => 'packages_intro'],
                ['text' => trim((string) ($validated['page_intro'] ?? ''))]
            );
            DoText::where('label', 'packages_intro')
                ->where('id', '!=', $introText->id)
                ->delete();
        });

        return response()->json([
            'message' => 'Activities page copy saved successfully.',
        ]);
    }
}
