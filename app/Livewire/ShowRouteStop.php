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

    public function addFavoriteStop($stop_id)
    {
        session()->push('favorite_stops', $stop_id);
        session()->put('favorite_stops', array_values(array_unique(session()->get('favorite_stops'))));
    }

    public function removeFavoriteStop($stop_id)
    {
        $favorite_stops = session()->get('favorite_stops', []);
        if (($key = array_search($stop_id, $favorite_stops)) !== false) {
            unset($favorite_stops[$key]);
        }
        session()->put('favorite_stops', array_values(array_unique($favorite_stops)));
    }
}
