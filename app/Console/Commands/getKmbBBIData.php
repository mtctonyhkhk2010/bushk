<?php

namespace App\Console\Commands;

use App\Models\Interchange;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class getKmbBBIData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-kmb-b-b-i-data';

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
        //if bus route is 1A
        //f1 = 1A bound is I, from 1A to other route
        //f2 = 1A bound is I, from other route to 1A
        //b1 = 1A bound is O, from 1A to other route
        //b2 = 1A bound is O, from other route to 1A

        //乘客用八達通卡或電子支付方式繳付第一程車資後，除個別符號之指定時間外，乘客可於150分鐘內以同一張八達通卡或電子支付方式繳付第二程車資，享受巴士轉乘優惠。
        // ^ 代表“30分鐘內”; #代表“60分鐘內”; *代表“90分鐘內”; @代表“120分鐘內” 及 !代表“適用於塘尾道或以後乘搭2A線之乘客”。

        $bbi_f1 = Cache::remember('bbi_f1', 10000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeF1.js')->collect();
        });
        $bbi_f2 = Cache::remember('bbi_f2', 10000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeF2.js')->collect();
        });
        $bbi_b1 = Cache::remember('bbi_b1', 10000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeB1.js')->collect();
        });
        $bbi_b2 = Cache::remember('bbi_b2', 10000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeB2.js')->collect();
        });

        foreach ($bbi_f1 as $route_name => $data)
        {
            //no bbi record
            if (is_string($data['Records'])) continue;

            $from_route = Route::where('name', $route_name)->where('dest_tc', 'like', $data['bus_arr'][0]['dest'].'%')->first();
            if (!isset($from_route))
            {
                //may be 循環線, try to find route by origin
                $from_route = Route::where('name', $route_name)->where('orig_tc', 'like', $data['bus_arr'][0]['dest'].'%')->first();

                if (!isset($from_route))
                {
                    echo 'cannot find from route ' . $route_name . ', dest:' . $data['bus_arr'][0]['dest'].PHP_EOL;
                    continue;
                }
            }

            foreach ($data['Records'] as $record)
            {
                $to_route_name = preg_replace("/[^a-zA-Z0-9]+/", "", $record['sec_routeno']);
                $to_dest = trim(self::delete_all_between('(' , ')', $record['sec_dest']));
                $to_route = Route::where('name', $to_route_name)->where('dest_tc', 'like', $to_dest.'%')->first();
                if (!isset($to_route))
                {
                    //may be 循環線, try to find route by origin
                    $to_route = Route::where('name', $to_route_name)->where('orig_tc', 'like', $to_dest.'%')->first();
                }
                if (!isset($to_route))
                {
                    //it appears many exchange to CTB is not up-to-date, but they are all O bound
                    $to_route = Route::where('name', $to_route_name)->where('service_type', 1)
                        ->join('company_route', 'routes.id', '=', 'company_route.route_id')
                        ->where('bound', 'O')
                        ->select('routes.*')
                        ->first();
                }

                if (!isset($to_route))
                {
                    echo 'cannot find to route ' . $to_route_name . ', dest:' . $to_dest.PHP_EOL;
                    continue;
                }

                $discount_mode = 'minus';
                if (str_contains($record['discount_max'], '減')) $discount_mode = 'minus';
                if (str_contains($record['discount_max'], '免費')) $discount_mode = 'free';
                if (str_contains($record['discount_max'], '付')) $discount_mode = 'pay';
                if (str_contains($record['discount_max'], '兩程合共')) $discount_mode = 'total';
                if (str_contains($record['discount_max'], '回贈')) $discount_mode = 'reward';

                if (str_contains($record['discount_max'], '免費'))
                {
                    $discount = 0;
                }
                else
                {
                    $discount = preg_replace("/[^.0-9]+/", "", $record['discount_max']);
                }


                $stop = null;

                $xchange = trim(self::delete_all_between('(' , ')', $record['xchange']));
                //handle special case
                if ($xchange == '葵芳鐵路站') $xchange = '葵芳站';
                if ($xchange != '任何能接駁第二程路線的巴士站')
                {
                    $stop = Stop::where('name_tc', 'like', $xchange.'%')->first();
                }

                if (!isset($stop) && $xchange != '任何能接駁第二程路線的巴士站')
                {
                    echo 'skipping, cannot find stop ' . $xchange . ', route_name:' . $route_name.PHP_EOL;
                    continue;
                }

                Interchange::create([
                    'from_route_id' => $from_route->id,
                    'to_route_id' => $to_route->id,
                    'validity_minutes' => match ($record['validity']) {
                        '^' => 30,
                        '#' => 60,
                        '*' => 90,
                        '@' => 120,
                        '!' => 150,//適用於塘尾道或以後乘搭2A線之乘客
                        default => 150,
                    },
                    'discount' => $discount,
                    'discount_mode' => $discount_mode,
                    'detail' => $record['detail'],
                    'success_cnt' => $record['success_cnt'],
                    'spec_remark_en' => $record['spec_remark_eng'],
                    'spec_remark_tc' => $record['spec_remark_chi'],
                    'stop_id' => $stop->id ?? null,
                ]);
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
}
