<?php

namespace App\View\Components\Bootstrap;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class statusButton extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bootstrap.status-button');
    }
}
