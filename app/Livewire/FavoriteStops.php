<?php

namespace App\Livewire;

use App\Models\Stop;
use Livewire\Component;

class FavoriteStops extends Component
{
    public $stops = [];
    public $selected_stop;

    public function mount()
    {
        $this->stops = Stop::whereIn('id', session()->get('favorite_stops', []))->with('routes')->get();
        $this->selected_stop = $this->stops->first()?->id;
    }

    public function render()
    {
        return view('livewire.favorite-stops');
    }
}
