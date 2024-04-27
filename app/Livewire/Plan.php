<?php

namespace App\Livewire;

use App\Models\MtrInfo;
use App\Models\Route;
use App\Models\Stop;
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

        $results = Http::withHeaders([
            'X-Goog-Api-Key' => config('google.API_KEY'),
            'X-Goog-FieldMask' => 'routes.legs.steps.transitDetails,routes.duration.*'
        ])->post('https://routes.googleapis.com/directions/v2:computeRoutes', [
            'origin' => [
                'location' => [
                    'latLng' => $this->from_location
                ],
            ],
            'destination' => [
                'location' => [
                    'latLng' => $this->to_location
                ],
            ],
            'languageCode'        => 'zh-HK',
            'travelMode' => 'TRANSIT',
            'computeAlternativeRoutes' => true,
        ])->json();

        $this->suggested_routes = [];

        foreach ($results['routes'] as $key => $route)
        {
            foreach ($route['legs'] as $leg)
            {
                foreach ($leg['steps'] as $step)
                {
                    if (!empty($step))
                    {
                        $this->suggested_routes[$key]['duration'] = round((int) filter_var($route['duration'], FILTER_SANITIZE_NUMBER_INT) / 60);

                        $this->suggested_routes[$key]['steps'][] = [
                            'name' => $step['transitDetails']['transitLine']['nameShort'] ?? $step['transitDetails']['transitLine']['name'],
                            'from' => $step['transitDetails']['stopDetails']['departureStop'],
                            'to' => $step['transitDetails']['stopDetails']['arrivalStop'],
                            'headsign' => $step['transitDetails']['headsign'],
                            'type' => $step['transitDetails']['transitLine']['vehicle']['type'],
                        ];
                    }
                }
            }
        }

        foreach ($this->suggested_routes as $key => &$route)
        {
            foreach ($route['steps'] as &$step)
            {
                $system_route = Route::where('name', $step['name'])->where('desc_tc', 'like', '%'.mb_substr($step['headsign'], 0, 2).'%')->first();
                if ($step['type'] == 'SUBWAY')
                {
                    $mtr = MtrInfo::where('line_name_tc', $step['name'])->first();
                    $system_route = Route::where('name', $mtr['line_id'])->first();
                }

                $system_from_stop = Stop::orderByDistance('position', new Point($step['from']['location']['latLng']['latitude'], $step['from']['location']['latLng']['longitude']))
                    ->whereHas('routes', function ($query) use ($system_route) {
                        $query->where('id', $system_route->id);
                    })->first();
                $step['system_route'] = $system_route;
                $step['system_from_stop'] = $system_from_stop;
            }

        }
    }
}
