<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;

class ActivityController extends BaseCrudController
{
    protected $model = Activity::class;
    protected $viewPrefix = 'activities';
    protected $routePrefix = 'activity-list';

    protected function validationRules($id = null): array
    {
        return [
            'name' => 'required|string|max:255|unique:activities,name' . ($id ? ",$id" : ''),
        ];
    }
}
