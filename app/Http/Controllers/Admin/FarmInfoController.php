<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmInfo;

class FarmInfoController extends Controller
{
    public function index()
    {
        $farmInfos = FarmInfo::latest()->get();
        return view('admin.farmInfos', compact('farmInfos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:farm_infos,name',
        ]);

        FarmInfo::create($request->only('name'));

        return response()->json(['message' => 'FarmInfo added successfully.']);

    }

    // public function show(FarmInfo $farmInfo)
    // {
    //     $farmInfo->load(['swiperImages', 'texts']);
    //     return view('admin.activities', compact('farmInfo'));
    // }

    public function update(Request $request, FarmInfo $farmInfo_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:farm_infos,name,' . $farmInfo_list->id,
        ]);

        $farmInfo_list->update($request->only('name'));

        return response()->json(['message' => 'FarmInfo updated successfully.']);
    }

    public function destroy(FarmInfo $farmInfo_list)
    {
        $farmInfo_list->delete();
        return redirect()->back()->with('success', 'Farm Info deleted successfully!');
    }
}
