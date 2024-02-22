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

        $interchange_stops = Stop::leftjoin('route_stop', 'stops.id', '=', 'route_stop.stop_id')
            ->leftjoin('routes', 'routes.id', '=', 'route_stop.route_id')
            ->whereIn('stops.id', $interchanges->keys()->reject(function ($value) {
                return $value == '';
            }))
            ->where('routes.id', $this->route->id)
            ->orderBy('route_stop.sequence')
            ->select('stops.*')
            ->get();

        $has_any_stop = $interchanges->keys()->contains('');

//        dd($interchanges[2930],$interchange_stops);
        return view('livewire.show-interchange', compact('interchanges', 'interchange_stops', 'has_any_stop'));
    }
}
