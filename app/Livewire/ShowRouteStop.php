<?php

namespace App\Livewire;

use Livewire\Component;

class ShowRouteStop extends Component
{
    public $stop;

    public bool $last_stop = false;

    public $route_id;
    public function render()
    {
        return view('livewire.show-route-stop');
    }
}
