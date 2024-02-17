<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class getRouteFareList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-route-fare-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo now()->toDateTimeString();
        $routes = Cache::remember('users', 1000, function () {
            return Http::get('https://data.hkbus.app/routeFareList.json')->collect();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('route_stop')->truncate();
        DB::table('company_route')->truncate();
        DB::table('companies')->truncate();
        DB::table('routes')->truncate();
        DB::table('stops')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Company::create([
            'co' => 'kmb',
            'name_tc' => '九巴',
            'name_en' => 'KMB',
        ]);

        Company::create([
            'co' => 'ctb',
            'name_tc' => '城巴',
            'name_en' => 'CTB',
        ]);

        Company::create([
            'co' => 'gmb',
            'name_tc' => '綠色專線小巴',
            'name_en' => 'GMB',
        ]);

        Company::create([
            'co' => 'nlb',
            'name_tc' => '新大嶼山巴士',
            'name_en' => 'NLB',
        ]);

        Company::create([
            'co' => 'lightRail',
            'name_tc' => '輕鐵',
            'name_en' => 'Light Rail',
        ]);

        Company::create([
            'co' => 'lrtfeeder',
            'name_tc' => '港鐵巴士',
            'name_en' => 'MTR bus',
        ]);

        Company::create([
            'co' => 'mtr',
            'name_tc' => '港鐵',
            'name_en' => 'MTR',
        ]);

        foreach ($routes['stopList'] as $id => $stop)
        {
            Stop::create([
                'stop_code' => $id,
                'name_tc' => $stop['name']['zh'],
                'name_en' => $stop['name']['en'],
                'position' => DB::raw('POINT(' . $stop['location']['lng'] . ', ' . $stop['location']['lat'] . ')'),
            ]);
        }

        foreach ($routes['routeList'] as $route)
        {
            $new_route = Route::create([
                'name' => $route['route'],
                'service_type' => $route['serviceType'],
                'gtfs_id' => $route['gtfsId'],
                'orig_tc' => $route['orig']['zh'],
                'orig_en' => $route['orig']['en'],
                'dest_tc' => $route['dest']['zh'],
                'dest_en' => $route['dest']['en'],
            ]);

            $companies = Company::whereIn('co', $route['co'])->get();
            foreach ($route['co'] as $co)
            {
                if (!isset($route['bound'][$co])) continue;

                $company = $companies->firstWhere('co', $co);
                $new_route->companies()->attach($company->id, ['bound' => $route['bound'][$co]]);

                $target_stops = Stop::whereIn('stop_code', $route['stops'][$co])->get();

                foreach ($route['stops'][$co] as $sequence => $stop)
                {
                    $target_stop = $target_stops->firstWhere('stop_code', $stop);
                    $new_route->stops()->attach($target_stop->id, ['sequence' => $sequence, 'fare' => $route['fares'][$sequence] ?? null]);
                    $target_stop->company_id = $company->id;
                    $target_stop->save();
                }
            }
        }

        echo now()->toDateTimeString();
    }
}
