<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuText extends Model
{
    protected $fillable = ['menu_id', 'section', 'title', 'content'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
