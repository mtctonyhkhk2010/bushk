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

                $from_dest = trim(self::delete_all_between('(' , ')', $bbi['direction']));
                $from_route = Route::where('name', $ctb_route)->where('dest_tc', 'like', $from_dest.'%')
                    ->whereHas('companies', function ($query) {
                        $query->where('co', 'ctb');
                })->first();

                if (!isset($from_route))
                {
                    //may be 循環線, try to find route by origin
                    $from_route = Route::where('name', $ctb_route)->where('orig_tc', 'like', $from_dest.'%')->first();
                }

                if (!isset($from_route))
                {
                    $from_dest = mb_substr($from_dest, 0, 2);
                    $from_route = Route::where('name', $ctb_route)->where('orig_tc', 'like', $from_dest.'%')->first();
                }

                if (!isset($from_route))
                {
                    echo 'cannot find from route ' . $ctb_route . ', dest:' . $from_dest.PHP_EOL;
                    continue;
                }

                foreach ($bbi['ir'] as $record)
                {
                    $to_route_name = preg_replace("/[^a-zA-Z0-9]+/", "", $record['route']);
                    $to_dest = trim(self::delete_all_between('(' , ')', $record['direction']));
                    $to_dest_between_paraphrase = trim(self::get_string_between($record['direction'],'(' , ')'));
                    if ($to_route_name == '682C') $to_dest = '港運城, 英皇道';
                    $to_route = Route::where('name', $to_route_name)->where('dest_tc', 'like', '%'.$to_dest.'%')->first();
                    if (!isset($to_route))
                    {
                        $to_dest = mb_substr($to_dest, 0, 2);
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
                        $to_route = Route::where('name', $to_route_name)->where('orig_tc', 'like', '%'.$to_dest_between_paraphrase.'%')->first();
                    }

                    if (!isset($to_route))
                    {
                        $to_dest_between_paraphrase = mb_substr($to_dest_between_paraphrase, 0, 2);
                        //try the first 2 char if still not match
                        $to_route = Route::where('name', $to_route_name)->where('orig_tc', 'like', '%'.$to_dest_between_paraphrase.'%')->first();
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
                    if ($xchange == '恒生銀行總行, 干諾道中') $xchange = '恒生銀行總行大廈, 干諾道中';
                    if ($xchange == '恒生銀行總行, 干諾道中') $xchange = '恒生銀行總行大廈, 干諾道中';
                    if ($xchange == '基督教青年會, 窩打老道') $xchange = '青年會, 窩打老道';
                    if ($xchange == '機場 (地面運輸中心)') $xchange = '航天城';
                    if ($xchange == '一號客運大樓, 暢達路') $xchange = '航天城';
                    if ($from_route->name == '73' && $xchange == '香港仔運動場, 黃竹坑道') $xchange = '黃竹坑遊樂場, 黃竹坑道';
                    if ($from_route->name == '73' && $xchange == '香港仔海濱公園, 香港仔海旁道') $xchange = '業漁大廈, 香港仔大道';
                    if ($from_route->name == 'A28' && $xchange == '一號客運大樓, 暢達路') $xchange = '機場 (1 號客運大樓)';
//                    if ($xchange != '任何能接駁第二程路線的巴士站')
//                    {
                        //find by original name first
                        $stop = Stop::where('name_tc', $xchange)->where('company_id', 2)->first();

                        if (!isset($stop))
                        {
                            //then find by original name without the (platform)
                            $xchange = trim(self::delete_all_between('(' , ')', $record['stopName']));
                            $stop = Stop::where('name_tc', 'like', $xchange.'%')->where('company_id', 2)->first();
                        }

                        if (!isset($stop))
                        {
                            $xchange = mb_substr($xchange, 0, 2);
                            $stop = Stop::where('name_tc', 'like', '%'.$xchange.'%')->whereHas('routes', function ($query) use ($to_route) {
                                $query->where('id', $to_route->id);
                            })->where('company_id', 2)->first();
                        }

                        if (!isset($stop))
                        {
                            $xchange = mb_substr($xchange, 0, 3);
                            $stop = Stop::where('name_tc', 'like', $xchange.'%')->whereHas('routes', function ($query) use ($to_route) {
                                $query->where('id', $to_route->id);
                            })->where('company_id', 2)->first();
                        }

                        if (!isset($stop))
                        {
                            echo 'skipping, cannot find stop ' . $xchange . ', route:' . $from_route->name.', to:' . $from_route->dest_tc.PHP_EOL;
                            continue;
                        }

                        $from_stop = Stop::join('route_stop', 'route_stop.stop_id', '=', 'stops.id')
                            ->join('routes', 'route_stop.route_id', '=', 'routes.id')
                            ->select('stops.*')
                            ->selectRaw('ST_Distance_Sphere(position,point(?, ?)) AS distance', [$stop->longitude, $stop->latitude])
                            ->where('routes.id', $from_route->id)
                            ->where('company_id', 2)
                            ->orderBy('distance')
                            //->toSql();
                            ->first();
                        //dd($from_stop);
//                    }

                    //skip if interchange distance is > 300m
                    if ( isset($from_stop) && $stop->id !== $from_stop->id && $from_stop->distance > 300) continue;
                    //dd($from_route,$to_route,$stop,$from_stop);

                    Interchange::create([
                        'from_route_id' => $from_route->id,
                        'to_route_id' => $to_route->id,
                        'validity_minutes' => trim($record['timeLimit']),
                        'discount' => $discount,
                        'discount_mode' => $discount_mode,
                        'detail' => null,
                        'success_cnt' => null,
                        'spec_remark_en' => $record['remark'],
                        'spec_remark_tc' => $record['remark'],
                        'from_stop_id' => isset($stop) ? ($from_stop->id ?? null) : null,
                        'to_stop_id' => $stop->id ?? null,
                    ]);
                }
//                dd($bbi);
            }
        }
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

    protected static function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
