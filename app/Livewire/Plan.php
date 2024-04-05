<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;
use function Laravel\Prompts\search;

class Plan extends Component
{
    public $from;

    public $to;

    public $show_from_suggestion = false;

    public $show_to_suggestion = false;
    public $from_location;

    public $to_location;

    public $from_suggestion = [];
    public $to_suggestion = [];

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
        if (empty($this->from_location) || empty($this->to_location)) return;

        $routes = Route::whereHas('stops', function (Builder $query) {
            $query->whereRaw('ST_Distance_Sphere(position,point(?, ?)) < 300', [$this->from_location['longitude'], $this->from_location['latitude']]);
        })->whereHas('stops', function (Builder $query) {
            $query->whereRaw('ST_Distance_Sphere(position,point(?, ?)) < 300', [$this->to_location['longitude'], $this->to_location['latitude']]);
        })
        ->with(['from_stops' => function (\Illuminate\Contracts\Database\Eloquent\Builder $query) {
            $query->selectRaw('ST_Distance_Sphere(position,point(?, ?)) as from_distance', [$this->from_location['longitude'], $this->from_location['latitude']])
                ->having('from_distance', '<', '300')
                ->orderBy('from_distance');
        }, 'to_stops' => function (\Illuminate\Contracts\Database\Eloquent\Builder $query) {
            $query->selectRaw('ST_Distance_Sphere(position,point(?, ?)) as to_distance', [$this->to_location['longitude'], $this->to_location['latitude']])
                ->having('to_distance', '<', '300')
                ->orderBy('to_distance');
        }])
        ->get();

        dump($routes);
    }
}
