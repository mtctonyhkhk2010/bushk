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
        $routes = Cache::remember('routeFareList', 1000, function () {
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

        $service_time_map_tc = [
            31 => "星期一至五",
            287 => "星期一至五",
            415 => "星期一至五",
            63 => "星期一至六",
            319 => "星期一至六",
            447 => "星期一至六",
            416 => "星期六至日",
            480 => "星期六至日",
            266 => "星期二至四",
            271 => "星期一至四",
            272 => "星期五",
            288 => "星期六",
            96 => "星期六、日及公眾假期",
            320 => "星期日及公眾假期",
            448 => "星期日及公眾假期",
            127 => "所有日子",
            511 => "所有日子",
            111 => "除星期三外",
            1 => "星期一",
            2 => "星期二",
            4 => "星期三",
            8 => "星期四",
            16 => "星期五",
            32 => "星期六",
            64 => "星期日",
            257 => "星期一",
            258 => "星期二",
            260 => "星期三",
            264 => "星期四",
            999 => "未知日子",
        ];

        $service_time_map_en = [
            31 => "Monday to Friday",
            287 => "Monday to Friday",
            415 => "Monday to Friday",
            63 => "Monday to Saturday",
            319 => "Monday to Saturday",
            447 => "Monday to Saturday",
            416 => "Saturday to Sunday",
            480 => "Saturday to Sunday",
            266 => "Tuesday to Thursday",
            271 => "Monday to Thursday",
            272 => "Friday",
            288 => "Saturday",
            96 => "Saturday, Sunday and PHs",
            320 => "Sundays and PHs",
            448 => "Sundays and PHs",
            127 => "All days",
            511 => "All days",
            111 => "Except Wednesday",
            1 => "Monday",
            2 => "Tuesday",
            4 => "Wednesday",
            8 => "Thursday",
            16 => "Friday",
            32 => "Saturday",
            64 => "Sunday",
            257 => "Monday",
            258 => "Tuesday",
            260 => "Wednesday",
            264 => "Thursday",
            999 => "Unknown day",
        ];

        foreach ($routes['routeList'] as $route_key => $route)
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
                        'fare_holiday' => $route['faresHoliday'][$sequence] ?? null
                    ]);
                    $target_stop->company_id = $company->id;
                    $target_stop->save();
                }
            }

            if (!isset($route['freq'])) echo $route_key . ' does not has service time data.' . PHP_EOL;

            foreach ($route['freq'] ?? [] as $weekday_id => $periods)
            {
                foreach ($periods as $start => $period)
                {
                    $new_route->serviceTimes()->create([
                        'weekday_id' => $weekday_id,
                        'weekday_tc' => $service_time_map_tc[$weekday_id],
                        'weekday_en' => $service_time_map_en[$weekday_id],
                        'start' => $start,
                        'end' => $period[0] ?? null,
                        'frequency_min' => isset($period[1]) ? intval($period[1]) / 60 : null,
                    ]);
                }
            }
        }
        Cache::flush();

        echo now()->toDateTimeString();
    }
}
