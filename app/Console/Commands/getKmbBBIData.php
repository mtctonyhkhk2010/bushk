<?php

namespace App\Console\Commands;

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
        $bbi_f1 = Cache::remember('bbi_f1', 1000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeF1.js')->collect();
        });
        $bbi_f2 = Cache::remember('bbi_f2', 1000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeF2.js')->collect();
        });
        $bbi_b1 = Cache::remember('bbi_b1', 1000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeB1.js')->collect();
        });
        $bbi_b2 = Cache::remember('bbi_b2', 1000, function () {
            return Http::get('https://www.kmb.hk/storage/BBI_routeB2.js')->collect();
        });


    }
}
