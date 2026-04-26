<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public string $title;
    public string $description;
    public string $keywords;
    public string $bodyClass;

    public function __construct(
        string $title = 'CalaiaPH Admin',
        string $description = 'Administrative dashboard for CalaiaPH.',
        string $keywords = 'CalaiaPH admin, dashboard, management',
        string $bodyClass = 'bg-slate-100 text-gray-900'
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->bodyClass = $bodyClass;
    }

    public function render(): View
    {
        return view('components.admin-layout');
    }
}
