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
        $this->stops = Stop::whereIn('id', session()->get('favorite_stops', []))->with(['routes' => function ($query) {
            $query->orderByRaw('LENGTH(name)')
                ->orderBy('name')
                ->orderBy('service_type');
        }])->get();
        $this->selected_stop = "".$this->stops->first()?->id;
    }

    public function render()
    {
        return view('livewire.favorite-stops');
    }
}
