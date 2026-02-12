<?php

namespace App\View\Components\Bootstrap;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $title;
    public $size;

    public $position;
    public $modalVisible;

    public $backdrop;
    public $keyboard;

    public function __construct($id, $title = null, $size = 'modal-md', $position = null, $modalVisible = false, $backdrop = 'true', $keyboard = 'true')
    {
        $this->id = $id;
        $this->title = $title;
        $this->size = $size;
        $this->modalVisible = $modalVisible;
        $this->position = $position;
        $this->backdrop = $backdrop;
        $this->keyboard = $keyboard;
    }

    public function render()
    {
        return view('components.bootstrap.modal');
    }
}
