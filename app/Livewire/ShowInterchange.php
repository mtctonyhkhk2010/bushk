<?php

namespace App\Livewire;

use App\Models\Route;
use App\Models\Stop;
use Livewire\Component;

class ShowInterchange extends Component
{
    public Route $route;
    public $stop;
    public $selected_tab;

    public function mount()
    {
        //dd($this->route->interchanges()->get());
    }

    public function render()
    {
        $interchanges = $this->route->interchanges()->get()->groupBy('pivot.stop_id');

        $interchange_stops = Stop::whereIn('id', $interchanges->keys()->reject(function ($value) {
            return $value == '';
        }))->get();
//        dd($interchanges[2930],$interchange_stops);
        return view('livewire.show-interchange', compact('interchanges', 'interchange_stops'));
    }
}
