<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Events\ProductStockUpdated;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::paginate(10);
        return view('admin.ShopStore.shopitems', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'category' => 'required|in:frozen,fresh',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ]
            ]);
            $result = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'calaia/products',
                'transformation' => [
                    ['width' => 800, 'height' => 800, 'crop' => 'fill', 'quality' => 'auto'],
                ]
            ]);
            $imagePath = $result['secure_url'];
        }

        $product = Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
            'stock_quantity' => $request->integer('stock_quantity', 0),
            'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Product created successfully.', 'product' => $product]);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'category' => 'required|in:frozen,fresh',
            'is_active' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_inventory_managed' => 'nullable|boolean',
        ]);

        $product = Product::findOrFail($id);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Upload new image to Cloudinary
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ]
            ]);
            $result = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'calaia/products',
                'transformation' => [
                    ['width' => 800, 'height' => 800, 'crop' => 'fill', 'quality' => 'auto'],
                ]
            ]);
            $imagePath = $result['secure_url'];
        }

        $product->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
            'stock_quantity' => $request->integer('stock_quantity', 0),
            'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
        ]);

        // Broadcast stock update if inventory is managed
        if ($product->is_inventory_managed) {
            broadcast(new ProductStockUpdated($product))->toOthers();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Product updated successfully.', 'product' => $product]);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product already deleted.',
                ]);
            }

            return redirect()->route('admin.shop.shopitems')->with('success', 'Product already deleted.');
        }

        $product->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product deleted successfully.');
    }
}
