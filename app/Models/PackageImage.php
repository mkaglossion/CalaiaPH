<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageImage extends Model
{
    use HasFactory;

    protected $table = 'package_images';

    protected $fillable = [
        'package_id',
        'image_path',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
