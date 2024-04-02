<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Plan extends Component
{
    public function mount()
    {

    }
    public function render()
    {
        return view('livewire.plan');
    }

    public function searchLocation($name)
    {
        if (empty($name)) return [];

        $result = Http::withHeaders([
            'X-Goog-Api-Key' => config('google.API_KEY'),
            'X-Goog-FieldMask' => 'places.displayName,places.formattedAddress,places.location'
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $name,
            'languageCode' => 'zh-HK',
            'locationRestriction' => [
                'rectangle' => [
                    'high' => [
                        'latitude' => '22.560497309421372',
                        'longitude' => '114.41298578594423',
                    ],
                    'low' => [
                        'latitude' => '22.1947741673399',
                        'longitude' => '113.83689021624464',
                    ],
                ]
            ]
        ]);

        return $result->json();
    }
}
