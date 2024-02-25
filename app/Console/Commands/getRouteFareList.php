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
        DB::table('service_times')->truncate();
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

        $service_time_map = [
            "31" => "星期一至五",
            "287" => "星期一至五",
            "415" => "星期一至五",
            "63" => "星期一至六",
            "319" => "星期一至六",
            "447" => "星期一至六",
            "416" => "星期六至日",
            "480" => "星期六至日",
            "266" => "星期二至四",
            "271" => "星期一至四",
            "272" => "星期五",
            "288" => "星期六",
            "320" => "星期日及公眾假期",
            "448" => "星期日及公眾假期",
            "511" => "所有日子",
            "111" => "除星期三外",
        ];

        foreach ($routes['routeList'] as $route)
        {
            $new_route = Route::create([
                'name' => $route['route'],
                'service_type' => $route['serviceType'],
                'gtfs_id' => $route['gtfsId'],
                'nlb_id' => $route['nlbId'] ?? null,
                'journey_time' => $route['jt'] ?? null,
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
                    $new_route->stops()->attach($target_stop->id, [
                        'sequence' => $sequence,
                        'fare' => $route['fares'][$sequence] ?? null,
                        'fare_holiday' => $route['fare_holiday'][$sequence] ?? null
                    ]);
                    $target_stop->company_id = $company->id;
                    $target_stop->save();
                }
            }

//            foreach ($route['freq'] as $weekday_id => $periods)
//            {
//                foreach ($periods as $start => $period)
//                {
//                    $new_route->serviceTimes()->create([
//                        'weekday_id' => $weekday_id,
//                        'weekday' => $service_time_map[$weekday_id],
//                        'start' => $start,
//                        'end' => $period[0] ?? null,
//                        'frequency_min' => $period[1] ?? null,
//                    ]);
//                }
//            }
        }
        Cache::flush();

        echo now()->toDateTimeString();
    }
}
