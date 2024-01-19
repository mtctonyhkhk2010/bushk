<?php

namespace App\Console\Commands;

use App\Models\Route;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class getCtbRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-ctb-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get citybus routes and stops';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Http::get('https://rt.data.gov.hk/v2/transport/citybus/route/ctb')->collect()['data'];

        Route::where('company', 'ctb')->delete();

        foreach ($routes as $route)
        {
            foreach (['inbound', 'outbound'] as $direction)
            {
                $new_route = Route::create([
                    'company' => 'ctb',
                    'name' => $route['route'],
                    'bound' => $direction,
                    'service_type' => 0,
                    'orig_tc' => $direction == 'outbound' ? $route['orig_tc'] : $route['dest_tc'],
                    'orig_sc' => $direction == 'outbound' ? $route['orig_sc'] : $route['dest_sc'],
                    'orig_en' => $direction == 'outbound' ? $route['orig_en'] : $route['dest_en'],
                    'dest_tc' => $direction == 'outbound' ? $route['dest_tc'] : $route['orig_tc'],
                    'dest_sc' => $direction == 'outbound' ? $route['dest_sc'] : $route['orig_sc'],
                    'dest_en' => $direction == 'outbound' ? $route['dest_en'] : $route['orig_en'],
                ]);

                $stops_of_the_route = collect(Http::get('https://rt.data.gov.hk/v2/transport/citybus/route-stop/CTB/' . $route['name'] . '/' . $direction)->collect()['data']);

                foreach ($stops_of_the_route as $stop)
                {
                    $stop_data = collect(Http::get('https://rt.data.gov.hk/v2/transport/citybus/stop/' . $stop['stop'])->collect()['data']);

                    $new_route->stops()->create([
                        'stop_id' => $stop['stop'],
                        'sequence' => $stop['seq'],
                        'name_tc' => $stop_data['name_tc'],
                        'name_en' => $stop_data['name_en'],
                        'name_sc' => $stop_data['name_sc'],
                        'position' => DB::raw('(' . $stop_data['lat'] . ', ' . $stop_data['long'] . ')'),
                    ]);
                }
            }

        }
    }
}
