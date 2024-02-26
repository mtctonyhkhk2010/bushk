<?php

namespace App\Console\Commands;

use App\Models\Interchange;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class getCtbBBIDataCommand extends Command
{
    protected $signature = 'app:get-ctb-b-b-i-data';

    protected $description = 'Command description';

    public function handle(): void
    {
        $ctb_bus = Route::whereHas('companies', function ($query) {
            return $query->where('co', 'ctb');
        })->get()->pluck('name')->unique()->values();

        foreach ($ctb_bus as $ctb_route)
        {
            $bbi_data = Cache::remember('ctb_bbi_'.$ctb_route, 80000, function () use ($ctb_route) {
                return Http::get('https://www.citybus.com.hk/concessionApi/public/bbi/api/v1/route/tc/' . $ctb_route)->collect();
            });

            foreach ($bbi_data as $bbi)
            {
                if ($bbi['legType'] != 1) continue;

                $from_dest = trim(self::delete_all_between('(' , ')', trim($bbi['direction'])));
                $from_route = Route::where('name', $ctb_route)->where('dest_tc', 'like', $from_dest.'%')->first();

                if (!isset($from_route))
                {
                    //may be 循環線, try to find route by origin
                    $from_route = Route::where('name', $ctb_route)->where('orig_tc', 'like', $from_dest.'%')->first();

                    if (!isset($from_route))
                    {
                        echo 'cannot find from route ' . $ctb_route . ', dest:' . $from_dest.PHP_EOL;
                        continue;
                    }
                }

                foreach ($bbi['ir'] as $record)
                {
                    $to_route_name = preg_replace("/[^a-zA-Z0-9]+/", "", $record['route']);
                    $to_dest = trim(self::delete_all_between('(' , ')', $record['direction']));
                    $to_route = Route::where('name', $to_route_name)->where('dest_tc', 'like', '%'.$to_dest.'%')->first();
                    if (!isset($to_route))
                    {
                        $to_dest = mb_substr($to_dest, 0, 3);
                        //try the first 3 char if still not match
                        $to_route = Route::where('name', $to_route_name)->where('orig_tc', 'like', '%'.$to_dest.'%')->first();
                    }
                    if (!isset($to_route))
                    {
                        //may be 循環線, try to find route by origin
                        $to_route = Route::where('name', $to_route_name)->where('orig_tc', 'like', '%'.$to_dest.'%')->first();
                    }



                    if (!isset($to_route))
                    {
                        echo 'cannot find to route ' . $to_route_name . ', dest:' . $to_dest.PHP_EOL;
                        continue;
                    }
                    //L1 = 回贈首程車費
                    //L2 = 次程免費
                    //FR = 減收
                    //TF = 兩程優惠車資合共

                    $discount_mode = 'minus';
                    $discount = 0;
                    if ($record['discount'] == 'FR') {
                        $discount_mode = 'minus';
                        $discount = preg_replace("/[^.0-9]+/", "", $record['discountAmount']['adult']);
                    }
                    if ($record['discount'] == 'L2') {
                        $discount_mode = 'free';
                    }
                    if ($record['discount'] == 'TF') {
                        $discount_mode = 'total';
                        $discount = preg_replace("/[^.0-9]+/", "", $record['totalFare']['adult']);
                    }
                    if ($record['discount'] == 'L1') $discount_mode = 'reward';


                    $stop = null;

                    $xchange = $record['stopName'];
                    if ($xchange == '匯豐總行大廈, 皇后大道中') $xchange = '滙豐總行大廈, 皇后大道中';
                    if ($xchange != '任何能接駁第二程路線的巴士站')
                    {
                        //find by original name first
                        $stop = Stop::where('name_tc', $xchange)->first();
                        if (!isset($stop))
                        {
                            //then find by original name without the (platform)
                            $xchange = trim(self::delete_all_between('(' , ')', $record['stopName']));
                            $stop = Stop::where('name_tc', 'like', $xchange.'%')->first();
                        }

                        if (!isset($stop))
                        {
                            echo 'cannot find stop'.PHP_EOL;
                            dd($from_route->name,$to_route->name,$stop->name);
                        }

                        $from_stop = Stop::join('route_stop', 'route_stop.stop_id', '=', 'stops.id')
                            ->join('routes', 'route_stop.route_id', '=', 'routes.id')
                            ->select('stops.*')
                            ->selectRaw('ST_Distance_Sphere(position,point(?, ?)) AS distance', [$stop->longitude, $stop->latitude])
                            ->where('routes.id', $from_route->id)
                            ->orderBy('distance')
                            //->toSql();
                            ->first();
                        //dd($from_stop);
                    }

                    if (!isset($stop) && $xchange != '任何能接駁第二程路線的巴士站')
                    {
                        echo 'skipping, cannot find stop ' . $xchange . ', route:' . $from_route->name.', to:' . $from_route->dest_tc.PHP_EOL;
                        continue;
                    }

                    //skip if interchange distance is > 300m
                    if (isset($stop) && isset($from_stop) && $stop->id !== $from_stop->id && $from_stop->distance > 300) continue;
                    //dd($from_route,$to_route,$stop,$from_stop);

//                    Interchange::create([
//                        'from_route_id' => $from_route->id,
//                        'to_route_id' => $to_route->id,
//                        'validity_minutes' => trim($record['timeLimit']),
//                        'discount' => $discount,
//                        'discount_mode' => $discount_mode,
//                        'detail' => null,
//                        'success_cnt' => null,
//                        'spec_remark_en' => $record['remark'],
//                        'spec_remark_tc' => $record['remark'],
//                        'from_stop_id' => isset($stop) ? ($from_stop->id ?? null) : null,
//                        'to_stop_id' => $stop->id ?? null,
//                    ]);
                }
//                dd($bbi);
            }
        }

        dd($ctb_bus);
    }

    protected static function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        return self::delete_all_between($beginning, $end, str_replace($textToDelete, '', $string)); // recursion to ensure all occurrences are replaced
    }
}
