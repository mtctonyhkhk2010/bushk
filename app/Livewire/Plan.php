<?php

namespace App\Livewire;

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
        $this->from_location = $this->from_suggestion['places'][$key];
        $this->from = $this->from_suggestion['places'][$key]['displayName']['text'];
        $this->show_from_suggestion = false;

    }

    public function saveToPosition($key)
    {
        $this->to_location = $this->to_suggestion['places'][$key];
        $this->to = $this->to_suggestion['places'][$key]['displayName']['text'];
        $this->show_to_suggestion = false;
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
        return Cache::remember('xsearchLocation_' . $name, 86400, function () use ($name) {
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
}
