<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class ToggleFavoriteRoute extends Component
{
    public $route_id;
    protected $route;

    public function mount()
    {
        $this->route = Route::find($this->route_id);
    }

    public function render()
    {
        $favorite = array_key_exists(md5($this->route->name.$this->route->dest_en), session()->get('favorite_routes_2', []));
        return view('livewire.toggle-favorite-route', compact('favorite'));
    }

    public function addFavoriteRoute()
    {
        $this->route = Route::find($this->route_id);
        $favorite_routes = session()->get('favorite_routes_2', []);
        $favorite_routes[md5($this->route->name.$this->route->dest_en)] = [
            'name' => $this->route->name,
            'dest' => $this->route->dest_en,
        ];
        session()->put('favorite_routes_2', $favorite_routes);
    }

    public function removeFavoriteRoute()
    {
        $this->route = Route::find($this->route_id);
        $favorite_routes = session()->get('favorite_routes_2', []);
        unset($favorite_routes[md5($this->route->name.$this->route->dest_en)]);
        session()->put('favorite_routes_2', $favorite_routes);
    }
}
