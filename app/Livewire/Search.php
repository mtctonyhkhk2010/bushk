<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Url;

class Search extends Component
{
    #[Url(as: 't'), Session]
    public $selected_tab = 'bus';

    #[Url(as: 's'), Session]
    public $search = '';

    #[Title('Search')]
    public function render()
    {
        $query = Route::query();

        $query->when($this->selected_tab == 'bus', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->whereIn('companies.co', ['kmb', 'ctb', 'nlb', 'lrtfeeder']);
        });

        $query->when($this->selected_tab == 'minibus', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->whereIn('companies.co', ['gmb']);
        });

        $query->when(!empty($this->search), function (Builder $query) {
            $query->where('name', 'like', $this->search.'%');
        });

        $character_query = $query->clone();

        $possible_characters =  Cache::rememberForever('possible_characters_' . $this->selected_tab . '_' . $this->search, function () use ($character_query) {
            return $character_query->selectRaw('SUBSTRING(name , ?, 1) AS possible', [strlen($this->search) + 1])->distinct()->get()->pluck('possible');
        });
        $possible_number = [];
        $possible_alphabet = [];

        foreach ($possible_characters as $character)
        {
            if ($character != 0 && empty($character)) continue;
            if (str_contains('1234567890', $character)) $possible_number[] = $character;
            if (str_contains('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $character)) $possible_alphabet[] = $character;
        }

        sort($possible_alphabet);

        $routes = Cache::rememberForever('search_' . $this->selected_tab . '_' . $this->search, function () use ($query) {
            return $query->with('companies')
                ->orderByRaw('LENGTH(name)')
                ->orderBy('name')
                ->orderBy('companies.id')
                ->orderBy('company_route.bound')
                ->orderBy('service_type')
                ->limit(50)
                ->select('routes.*')
                ->get();
        });

        return view('livewire.search', compact('routes', 'possible_number', 'possible_alphabet'));
    }

    public function addToSearch($character)
    {
        $this->search .= $character;
    }

    public function backspace()
    {
        $this->search = substr($this->search, 0, -1);
    }

    public function clearSearch()
    {
        $this->search = '';
    }
}
