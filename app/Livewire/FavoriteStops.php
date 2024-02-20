<?php

namespace App\Livewire;

use App\Models\Stop;
use Livewire\Component;

class FavoriteStops extends Component
{
    public $stops = [];

    public function mount()
    {
        $this->stops = Stop::whereIn('id', session()->get('favorite_stops', []))->get();
    }

    public function render()
    {
        return view('livewire.favorite-stops');
    }
}
