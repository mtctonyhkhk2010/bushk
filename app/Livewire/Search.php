<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
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
            $query->whereHas('companies', function (Builder $query) {
                $query->whereIn('co', ['kmb', 'ctb', 'nlb', 'lrtfeeder']);
            });
        });

        $query->when($this->selected_tab == 'minibus', function (Builder $query) {
            $query->whereHas('companies', function (Builder $query) {
                $query->whereIn('co', ['gmb']);
            });
        });

        $query->when(!empty($this->search), function (Builder $query) {
            $query->where('name', 'like', $this->search.'%');
        });

        $character_query = $query->clone();

        $possible_characters = $character_query->selectRaw('SUBSTRING(name , ?, 1) AS possible', [strlen($this->search) + 1])->distinct()->get()->pluck('possible');
        $possible_number = [];
        $possible_alphabet = [];

        foreach ($possible_characters as $character)
        {
            if ($character != 0 && empty($character)) continue;
            if (str_contains('1234567890', $character)) $possible_number[] = $character;
            if (str_contains('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $character)) $possible_alphabet[] = $character;
        }

        $routes = $query->with('companies')->limit(50)->get();

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
