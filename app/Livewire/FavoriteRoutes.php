<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class FavoriteRoutes extends Component
{
    public $routes = [];

    public function mount()
    {
        $this->routes = Route::whereIn('id', session()->get('favorite_routes', []))
            ->with('stops')
            ->get();

        foreach ($this->routes as $route)
        {
            $route->stops->each(function ($item) {
                $item->latitude = $item->latitude;
                $item->longitude = $item->longitude;
            });
        }

    }

    public function render()
    {
        return view('livewire.favorite-routes');
    }
}
