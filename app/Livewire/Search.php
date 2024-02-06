<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

class Search extends Component
{
    #[Title('Search')]
    public function render()
    {
        $routes = Cache::remember('users', 1000, function () {
            return Http::get('https://data.hkbus.app/routeFareList.json')->collect();
        });

        dd($routes['routeList']['1+1+CHUK YUEN ESTATE+STAR FERRY']);
        $cos = [];
        foreach ($routes['routeList'] as $route)
        {
            foreach ($route['co'] as $co)
            {
                if (!in_array($co, $cos)) $cos[] = $co;
            }
        }
        dd($cos);
        return view('livewire.search');
    }
}
