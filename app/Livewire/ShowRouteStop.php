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

    public function addFavoriteStop($stop_code)
    {
        session()->push('favorite_stops2', $stop_code);
        session()->put('favorite_stops2', array_values(array_unique(session()->get('favorite_stops2'))));
    }

    public function removeFavoriteStop($stop_code)
    {
        $favorite_stops = session()->get('favorite_stops2', []);
        if (($key = array_search($stop_code, $favorite_stops)) !== false) {
            unset($favorite_stops[$key]);
        }
        session()->put('favorite_stops2', array_values(array_unique($favorite_stops)));
    }
}
