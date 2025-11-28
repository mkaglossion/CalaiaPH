<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseCrudController extends Controller
{
    protected $model;
    protected $viewPrefix;
    protected $routePrefix;

    public function index()
    {
        $items = $this->model::latest()->get();
        $viewName = "admin.{$this->viewPrefix}";
        return view($viewName, compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $this->model::create($validated);

        $message = ucfirst($this->viewPrefix) . ' created successfully.';
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules($id));
        $item->update($validated);

        $message = ucfirst($this->viewPrefix) . ' updated successfully.';
        return response()->json(['message' => $message]);
    }

    public function destroy($id)
    {
        $this->model::findOrFail($id)->delete();
        $message = ucfirst($this->viewPrefix) . ' deleted successfully!';
        return redirect()->back()->with('success', $message);
    }

    abstract protected function validationRules($id = null): array;
}