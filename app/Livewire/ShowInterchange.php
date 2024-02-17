<?php

namespace App\Livewire;

use App\Models\Route;
use App\Models\Stop;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowInterchange extends Component
{
    public Route $route;
    #[Url(as: 'stop')]
    public $target_stop;

    public function mount()
    {
        //dd($this->route->interchanges()->get());
    }

    public function render()
    {
        $interchanges = $this->route->interchanges()->get()->groupBy('pivot.from_stop_id');

        $interchange_stops = Stop::whereIn('id', $interchanges->keys()->reject(function ($value) {
            return $value == '';
        }))->get();
//        dd($interchanges[2930],$interchange_stops);
        return view('livewire.show-interchange', compact('interchanges', 'interchange_stops'));
    }
}
