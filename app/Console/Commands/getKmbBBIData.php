<?php

namespace App\Console\Commands;

use App\Models\Route;
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

        //乘客用八達通卡或電子支付方式繳付第一程車資後，除個別符號之指定時間外，乘客可於150分鐘內以同一張八達通卡或電子支付方式繳付第二程車資，享受巴士轉乘優惠。 ^ 代表“30分鐘內”; #代表“60分鐘內”; *代表“90分鐘內”; @代表“120分鐘內” 及 !代表“適用於塘尾道或以後乘搭2A線之乘客”。
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

            foreach ($data['Records'] as $record)
            {
                if (!str_contains($record['discount_max'], '減') && !str_contains($record['discount_max'], '免費') && !str_contains($record['discount_max'], '付') && !str_contains($record['discount_max'], '兩程合共'))
                {
                    //回贈
                    echo 'route ' . $route_name . ', dest:' . $data['bus_arr'][0]['dest'].PHP_EOL;
                    echo $record['discount_max'].PHP_EOL;
                }


            }

//            $route = Route::where('name', $route_name)->where('dest_tc', 'like', $data['bus_arr'][0]['dest'].'%')->first();
//            if (!isset($route))
//            {
//                //may be 循環線, try to find route by origin
//                $route = Route::where('name', $route_name)->where('orig_tc', 'like', $data['bus_arr'][0]['dest'].'%')->first();
//
//                if (!isset($route))
//                {
//                    echo 'cannot find route ' . $route_name . ', dest:' . $data['bus_arr'][0]['dest'].PHP_EOL;
//                    continue;
//                };
//            }


        }


    }
}
