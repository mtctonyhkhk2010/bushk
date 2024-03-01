<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class ShowServiceTime extends Component
{
    public Route $route;

    public function render()
    {
        $service_times = $this->route->serviceTimes()->get()->groupBy('weekday_id');

        return view('livewire.show-service-time', compact('service_times'));
    }
}
