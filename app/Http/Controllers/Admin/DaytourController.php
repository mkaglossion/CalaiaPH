<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\DayTour;

class DayTourController extends BaseCrudController
{
    protected $model = DayTour::class;
    protected $viewPrefix = 'days';
    protected $routePrefix = 'day-tours';

    protected function validationRules($id = null): array
    {
        return [
            'name' => 'required|string|max:255|unique:day_tours,name' . ($id ? ",$id" : ''),
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'activity_ids' => 'nullable|array',
        ];
    }

    public function index()
    {
        $tours = $this->model::with('activities')->get();
        return view('admin.days', compact('tours'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $tour = $this->model::create(collect($validated)->except('activity_ids')->toArray());
        $tour->activities()->sync($request->activity_ids ?? []);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Day Tour created!',
                'tour' => $tour->load('activities')
            ]);
        }

        return back()->with('success', 'Day Tour created!');
    }

    public function update(Request $request, $id)
    {
        $tour = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules($id));
        $tour->update(collect($validated)->except('activity_ids')->toArray());
        $tour->activities()->sync($request->activity_ids ?? []);

        return back()->with('success', 'Day Tour updated!');
    }
}

