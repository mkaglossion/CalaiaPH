<?php

namespace App\Models;

class MenuText extends BaseRelatedText
{
    protected $fillable = ['menu_id', 'section', 'title', 'content'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    protected function getParentModelClass(): string
    {
        return Menu::class;
    }
}
