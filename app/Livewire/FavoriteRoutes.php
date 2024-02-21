<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class FavoriteRoutes extends Component
{
    public $routes = [];
    public $stops = [];

    public function mount()
    {
        $this->routes = Route::whereIn('id', session()->get('favorite_routes', []))
            ->with('stops.company')
            ->get();

        foreach ($this->routes as $route)
        {
            $stops = $route->stops->groupBy('company.id');

            foreach ($stops as $company_id => $company_stops)
            {
                $company_stops->each(function ($item) {
                    $item->latitude = $item->latitude;
                    $item->longitude = $item->longitude;
                });
            }
            $this->stops[$route->id] = $stops->toArray();
        }
    }

    public function render()
    {
        return view('livewire.favorite-routes');
    }
}
