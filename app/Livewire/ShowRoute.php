<?php

namespace App\Livewire;

use App\Models\Route;
use Livewire\Component;

class ShowRoute extends Component
{
    public Route $route;
    public $stops;
    public $stops_position = [];
    public ?Route $reverse_route;

    public function mount()
    {
        //dd($this->route->stops()->with('company')->get()->groupBy('company.id'));
        $this->reverse_route = Route::where('name', $this->route->name)
            ->where('service_type', $this->route->service_type)
            ->where('id', '!=', $this->route->id)
            ->where('dest_tc', $this->route->orig_tc)
            ->first();

        $this->stops = $this->route->stops()->with('company')->get()->groupBy('company.id');
        $this->stops->first()->each(function ($item) {
            $this->stops_position[] = [
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
            ];
        });
        $this->stops = $this->stops->toArray();
        //dd($this->route);
    }

    public function render()
    {
        return view('livewire.show-route');
    }
}
