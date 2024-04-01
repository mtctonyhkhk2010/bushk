<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
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
        $query = $this->buildQuery();

        $possible_characters =  Cache::remember('6possible_characters_' . $this->selected_tab . '_' . $this->search, 60*60*12, function () use ($query) {
            return $query->selectRaw('SUBSTRING(name , ?, 1) AS possible', [strlen($this->search) + 1])->distinct()->get()->pluck('possible');
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

        return view('livewire.search', compact( 'possible_number', 'possible_alphabet'));
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

    public function buildQuery()
    {
        $query = Route::query();

        $query->when($this->selected_tab == 'bus', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->whereIn('companies.co', ['kmb', 'ctb', 'nlb', 'lrtfeeder']);
        });

        $query->when($this->selected_tab == 'gmb', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->whereIn('companies.co', ['gmb']);
        });

        $query->when($this->selected_tab == 'lightrail', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->whereIn('companies.co', ['lightRail']);
        });

        $query->when($this->selected_tab == 'mtr', function (Builder $query) {
            $query->join('company_route', 'routes.id', '=', 'company_route.route_id')
                ->join('companies', 'companies.id', '=', 'company_route.company_id')
                ->join('mtr_info', 'mtr_info.line_id', '=', 'routes.name')
                ->whereIn('companies.co', ['mtr']);
        });

        $query->when(!empty($this->search), function (Builder $query) {
            $query->where('name', 'like', $this->search . '%');
        });

        return $query;
    }

    #[Computed]
    public function routes()
    {
        $query = $this->buildQuery();

        return $query->with('companies')
            ->orderByRaw('LENGTH(name)')
            ->orderBy('name')
            ->orderBy('companies.id')
            ->orderBy('company_route.bound')
            ->orderBy('service_type')
            ->limit(50)
            ->select('routes.*')
            ->when($this->selected_tab == 'mtr', function (Builder $query) {
                $query->addSelect(['mtr_info.line_name_tc', 'mtr_info.line_name_en', 'mtr_info.line_color']);
            })
            ->get();
    }

    #[Computed]
    public function tabs()
    {
        return [
            [
                'name' => 'bus',
                'label' => '巴士',
            ],
            [
                'name' => 'gmb',
                'label' => '綠van',
            ],
            [
                'name' => 'lightrail',
                'label' => '輕鐵',
            ],
            [
                'name' => 'mtr',
                'label' => '港鐵',
            ],
        ];
    }
}
