<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::latest()->get();
        return view('admin.menus', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
        ]);

        Menu::create($request->only('name'));

        return response()->json(['message' => 'Menu added successfully.']);

    }

    public function show(Menu $menu_list)
    {
        return response()->json($menu_list);
    }

    public function update(Request $request, Menu $menu_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name,' . $menu_list->id,
        ]);

        $menu_list->update($request->only('name'));

        return response()->json(['message' => 'Menu updated successfully.']);
    }

    public function destroy(Menu $menu_list)
    {
        $menu_list->delete();
        return redirect()->back()->with('success', 'Menu deleted successfully!');
    }
}
