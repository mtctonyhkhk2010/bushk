<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;
use MatanYadaev\EloquentSpatial\Objects\Point;
use function Laravel\Prompts\search;

class Plan extends Component
{
    public $from;

    public $to;

    public $show_from_suggestion = false;

    public $show_to_suggestion = false;
    public $current_location;
    public $from_location;

    public $to_location;

    public $from_suggestion = [];
    public $to_suggestion = [];

    public $suggested_routes = [];

    public function mount()
    {

    }
    public function render()
    {
        return view('livewire.plan');
    }

    public function saveFromPosition($key)
    {
        $this->from_location = $this->from_suggestion['places'][$key]['location'];
        $this->from = $this->from_suggestion['places'][$key]['displayName']['text'];
        $this->show_from_suggestion = false;

    }

    public function saveToPosition($key)
    {
        $this->to_location = $this->to_suggestion['places'][$key]['location'];
        $this->to = $this->to_suggestion['places'][$key]['displayName']['text'];
        $this->show_to_suggestion = false;

        $this->searchRoute();
    }

    public function updatedFrom()
    {
        $this->show_from_suggestion = true;
        $this->from_location = [];
        $this->from_suggestion = $this->searchLocation($this->from);
    }

    public function updatedTo()
    {
        $this->show_to_suggestion = true;
        $this->to_location = [];
        $this->to_suggestion = $this->searchLocation($this->to);
    }

    public function searchLocation($name)
    {
        if (empty($name)) return [];

        //cache google result for a day
        return Cache::remember('searchLocation_' . $name, 86400, function () use ($name) {
            return Http::withHeaders([
                'X-Goog-Api-Key'   => config('google.API_KEY'),
                'X-Goog-FieldMask' => 'places.displayName,places.formattedAddress,places.location'
            ])->post('https://places.googleapis.com/v1/places:searchText', [
                'textQuery'           => $name,
                'languageCode'        => 'zh-HK',
                'locationRestriction' => [
                    'rectangle' => [
                        'high' => [
                            'latitude'  => '22.560497309421372',
                            'longitude' => '114.41298578594423',
                        ],
                        'low'  => [
                            'latitude'  => '22.1947741673399',
                            'longitude' => '113.83689021624464',
                        ],
                    ]
                ]
            ])->json();
        });
    }

    public function searchRoute()
    {
        if (empty($this->to_location)) return;

        if (empty($this->from_location) && empty($this->current_location)) return;

        if (empty($this->from_location))
        {
            $this->from_location = $this->current_location;
        }

        $distance = 500;

        $routes = Route::whereHas('stops', function (Builder $query) use ($distance) {
            $query->whereDistanceSphere('position', new Point($this->from_location['latitude'], $this->from_location['longitude']), '<', $distance);
        })->whereHas('stops', function (Builder $query) use ($distance) {
            $query->whereDistanceSphere('position', new Point($this->to_location['latitude'], $this->to_location['longitude']), '<', $distance);
        })
        ->with(['from_stops' => function (\Illuminate\Contracts\Database\Eloquent\Builder $query) use ($distance) {
            $query
                ->withDistanceSphere('position', new Point($this->from_location['latitude'], $this->from_location['longitude']), 'from_distance')
                ->having('from_distance', '<', $distance)
                ->orderBy('from_distance');
        }, 'to_stops' => function (\Illuminate\Contracts\Database\Eloquent\Builder $query) use ($distance) {
            $query
                ->withDistanceSphere('position', new Point($this->to_location['latitude'], $this->to_location['longitude']), 'to_distance')
                ->having('to_distance', '<', $distance)
                ->orderBy('to_distance');
        }])
        ->get();

        foreach ($routes as $route)
        {
            if (str_contains($route->dest_tc, '循環線'))
            {
                foreach ($route->to_stops as $key => $to_stop)
                {
                    if ($route->from_stops->first()->sequence > $to_stop->sequence) $route->to_stops->forget($key);
                }
            }
        }

        $routes = $routes
            ->filter(function ($route) use ($routes) {
            return $route->from_stops->first()->pivot->sequence < $route->to_stops->first()->pivot->sequence;
        })
            ->sortBy(function ($route) {
            return $route->from_stops->first()->from_distance + $route->to_stops->first()->to_distance +
                ($route->to_stops->first()->pivot->sequence - $route->from_stops->first()->pivot->sequence) * 30;
        });

        $this->suggested_routes = $routes;
    }
}
