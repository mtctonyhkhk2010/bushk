<?php

namespace App\Console\Commands;

use App\Models\Route;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class getKmbRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-kmb-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get kmb routes and stops';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Http::get('https://data.etabus.gov.hk/v1/transport/kmb/route/')->collect()['data'];

        $route_stop_data = collect(Http::get('https://data.etabus.gov.hk/v1/transport/kmb/route-stop/')->collect()['data']);

        $stop_data = collect(Http::get('https://data.etabus.gov.hk/v1/transport/kmb/stop')->collect()['data']);

        Route::where('company', 'kmb')->delete();

        foreach ($routes as $route)
        {
            $new_route = Route::create([
                'company' => 'kmb',
                'name' => $route['route'],
                'bound' => $route['bound'] == 'I' ? 'inbound' : 'outbound',
                'service_type' => $route['service_type'],
                'orig_tc' => $route['orig_tc'],
                'orig_sc' => $route['orig_sc'],
                'orig_en' => $route['orig_en'],
                'dest_tc' => $route['dest_tc'],
                'dest_sc' => $route['dest_sc'],
                'dest_en' => $route['dest_en'],
            ]);

            $stops_of_the_route = $route_stop_data->where('route', $new_route->name)
                ->where('bound', $new_route->bound == 'inbound' ? 'I' : 'O')
                ->where('service_type', $new_route->service_type);


            foreach ($stops_of_the_route as $stop)
            {
                $stop_info = $stop_data->where('stop', $stop['stop'])->first();
                if (!isset($stop_info['name_tc'])) dd($stop);
                $new_route->stops()->create([
                    'stop_id' => $stop['stop'],
                    'sequence' => $stop['seq'],
                    'name_tc' => $stop_info['name_tc'],
                    'name_en' => $stop_info['name_en'],
                    'name_sc' => $stop_info['name_sc'],
                    'position' => DB::raw('POINT(' . $stop_info['lat'] . ', ' . $stop_info['long'] . ')'),
                ]);
            }
        }
    }
}
