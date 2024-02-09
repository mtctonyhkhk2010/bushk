<?php

namespace App\Livewire;

use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

class Search extends Component
{
    public $selected_tab = 'all';

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

        $routes = $query->with('companies')->limit(50)->get();


        return view('livewire.search', compact('routes'));
    }
}
