<?php

namespace App\Livewire;

use App\Models\Interchange;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ShowRoute extends Component
{
    public Route $route;
    public $stops;
    public $stops_position = [];
    public ?Route $reverse_route;
    public $is_mtr = false;

    public $line_color = '#3388ff';

    public function mount()
    {
//        dd(Cache::remember('bbi_f1', 10000, function () {
//            return Http::get('https://www.kmb.hk/storage/BBI_routeF1.js')->collect();
//        })['A31']);
        //dd($this->route->stops()->with('company')->get()->groupBy('company.id'));
        $co = $this->route->companies()->first()->co;
        if ($co == 'mtr')
        {
            $this->is_mtr = true;
            $this->route->load('mtr_info');
        }

        $this->setLineColor($co);

        $this->reverse_route = Route::where('name', $this->route->name)
            ->where('service_type', $this->route->service_type)
            ->where('id', '!=', $this->route->id)
            ->where('dest_tc', $this->route->orig_tc)
            ->first();

        $interchange_stop_ids = DB::table('interchange')
            ->where('from_route_id', $this->route->id)
            ->whereNotNull('from_stop_id')
            ->distinct()
            ->select('from_stop_id')
            ->pluck('from_stop_id');

        $this->stops = $this->route->stops()->with('company')->get()->groupBy('company.id');
        $this->stops->first()->each(function ($item) use ($interchange_stop_ids) {
            $this->stops_position[] = [
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
            ];
            $item->interchangeable = $interchange_stop_ids->contains($item->id);
        });
        $this->stops = $this->stops->toArray();
        //dd($this->stops,$this->route->companies->first()->id);
    }

    public function render()
    {
        return view('livewire.show-route')->title(($this->is_mtr ? $this->route->mtr_info->line_name_tc : $this->route->name) . ' ' . $this->route->dest_tc);
    }

    public function setLineColor($co)
    {
        if ($co == 'mtr')
        {
            $this->line_color = $this->route->mtr_info->line_color;
        }
        if ($co == 'kmb')
        {
            $this->line_color = '#ed301e';
        }
        if ($co == 'ctb')
        {
            $this->line_color = '#fcdc00';
        }
        if ($co == 'nlb')
        {
            $this->line_color = '#00897c';
        }
        if ($co == 'gmb')
        {
            $this->line_color = '#0e835b';
        }
    }
}
