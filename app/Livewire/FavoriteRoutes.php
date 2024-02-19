<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class FavoriteRoutes extends Component
{
    public $routes = [];

    public function mount()
    {
        $this->routes = Route::whereIn('id', session()->get('favorite_routes', []))->get();
    }

    public function render()
    {
        return view('livewire.favorite-routes');
    }
}
