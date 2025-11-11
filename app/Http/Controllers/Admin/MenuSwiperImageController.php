<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuSwiperImage;
use Illuminate\Support\Facades\Storage;

class MenuSwiperImageController extends Controller
{
    public function index()
    {
        $images = MenuSwiperImage::with('menu')->latest()->get(); // eager load menu
        return view('admin.menu-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'image' => 'required|image|max:20480',
        ]);

        $path = $request->file('image')->store('menus', 'public');

        MenuSwiperImage::create([
            'menu_id' => $request->menu_id,
            'title' => $request->title ?? null,
            'image_path' => $path,
        ]);

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Image uploaded successfully!',
                'image_path' => $path
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', 'Image uploaded successfully!');
    }

    public function destroy(MenuSwiperImage $menu_image)
    {
        \Log::info('Deleting image', $menu_image->toArray());

        $path = $menu_image->image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted file successfully', ['path' => $path]);
        } else {
            \Log::warning('⚠️ File not found or path null', ['path' => $path]);
        }

        $menu_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
