<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'image_path',
        'description1',
        'description2',
        'link_path',
        'order'
    ];

    protected $table = 'event_sections';
}