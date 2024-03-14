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
        $this->stops = Stop::whereIn('stop_code', session()->get('favorite_stops2', []))->with(['routes' => function ($query) {
            $query->orderByRaw('LENGTH(name)')
                ->orderBy('routes.name')
                ->orderBy('routes.service_type');
        }, 'routes.companies'])->get();
        $this->selected_stop = "".$this->stops->first()?->id;
    }

    public function render()
    {
        return view('livewire.favorite-stops');
    }
}
