<?php

namespace App\Livewire;

use Livewire\Component;

class ToggleFavoriteStop extends Component
{
    public $stop_code;

    public function render()
    {
        return view('livewire.toggle-favorite-stop');
    }

    public function addFavoriteStop()
    {
        session()->push('favorite_stops2', $this->stop_code);
        session()->put('favorite_stops2', array_values(array_unique(session()->get('favorite_stops2'))));
    }

    public function removeFavoriteStop()
    {
        $favorite_stops = session()->get('favorite_stops2', []);
        if (($key = array_search($this->stop_code, $favorite_stops)) !== false) {
            unset($favorite_stops[$key]);
        }
        session()->put('favorite_stops2', array_values(array_unique($favorite_stops)));
    }
}
