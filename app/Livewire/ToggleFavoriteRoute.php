<?php

namespace App\Livewire;

use Livewire\Component;

class ToggleFavoriteRoute extends Component
{
    public $route_id;

    public function render()
    {
        return view('livewire.toggle-favorite-route');
    }

    public function addFavoriteRoute()
    {
        session()->push('favorite_routes', $this->route_id);
        session()->put('favorite_routes', array_values(array_unique(session()->get('favorite_routes'))));
    }

    public function removeFavoriteRoute()
    {
        $favorite_routes = session()->get('favorite_routes', []);
        if (($key = array_search($this->route_id, $favorite_routes)) !== false) {
            unset($favorite_routes[$key]);
        }
        session()->put('favorite_routes', array_values(array_unique($favorite_routes)));
    }
}
