<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayTourSwiperImage extends Model
{
    use HasFactory, HasSortableOrder;

    protected $fillable = ['title', 'image_path', 'public_id', 'day_tour_id', 'package_id', 'daytour_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function dayTour()
    {
        return $this->belongsTo(DayTour::class, 'daytour_id');
    }
}
