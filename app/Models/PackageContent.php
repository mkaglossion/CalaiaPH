<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageContent extends Model
{
    use HasFactory;

    protected $table = 'package_contents';

    protected $fillable = [
        'package_id',
        'title',
        'content',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
