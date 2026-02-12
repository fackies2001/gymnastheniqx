<?php

namespace App\View\Components\Bootstrap;

use Illuminate\View\Component;

class Carousel extends Component
{
    public $images;

    /**
     * Create a new component instance.
     *
     * @param array|string $images
     */
    public function __construct($images)
    {
        // Decode if it's a JSON string
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        $this->images = $images;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.bootstrap.carousel');
    }
}
