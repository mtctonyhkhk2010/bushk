<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

class Search extends Component
{
    #[Title('Search')]
    public function render()
    {
        $routes = Http::get('https://data.etabus.gov.hk/v1/transport/kmb/route/')->collect()['data'];

        dd($routes);
        foreach ($routes as $route)
        {
            dd($route);
        }
        return view('livewire.search');
    }
}
