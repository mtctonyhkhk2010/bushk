<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class getRedMiniBusData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-red-mini-bus-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $rmb_routes=[];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frontpage = Cache::remember('16seat_frontpage', 10000, function () {
            return Http::get('https://www.16seats.net/chi/index.html')->body();
        });
        $frontpage_crawler = new Crawler($frontpage);
        foreach ($frontpage_crawler->filter('li a') as $domElement) {
            $area_link = $domElement->getAttribute('href');
            if (!str_contains($area_link, 'rmb')) continue;
            $area_content = Cache::remember('16seat_link'.$area_link, 10000, function () use ($area_link) {
                return Http::get('https://www.16seats.net/chi/'.$area_link)->body();
            });

            $area_crawler = (new Crawler($area_content))->filter('#SvcList a');
            foreach ($area_crawler as $area)
            {
                $route_link = $area->getAttribute('href');
                //dd($route_link);
                $route_content = Cache::remember('16seat_route_'.$route_link, 10000, function () use ($route_link) {
                    return Http::get('https://www.16seats.net/chi/rmb/'.$route_link)->body();
                });


                $route_crawler = (new Crawler($route_content));
                try {
                    $route_name = $route_crawler->filter('.svcname-rmb-entry')->innerText();
                }
                catch(\Exception $e)
                {
                    continue;
                }

                $tags = $route_crawler->filter('.svc-entry-tag')->children();
                foreach ($tags as $tag)
                {
                    if ($tag->className == 'tag-rsvn')
                    {
                        $this->rmb_routes[$route_name]['rsvn'] = $tag->nodeValue;
                        continue;
                    }
                    $this->rmb_routes[$route_name]['tags'][] = $tag->nodeValue;
                    $this->rmb_routes[$route_name]['tags'] = array_unique($this->rmb_routes[$route_name]['tags']);
                }

                $route_crawler->filter('.svc-detail-frame')->each(function (Crawler $direction_crawler) use ($route_name) {
                    // DO THIS: specify the parent tag too
                    $origin = $direction_crawler->filter('.svc-detail-frame .intro-origin')->innerText();

                    $direction_crawler->filter('.svc-detail-frame .time-table-row')->each(function ($time_row_crawler) use ($route_name, $origin) {
                        try {
                            $weekday = $time_row_crawler->children('.time-table-cell1')->innerText();
                            $time = $time_row_crawler->children('.time-table-cell2')->innerText();
                            $this->rmb_routes[$route_name][$origin]['time'][$weekday] = $time;
                        }
                        catch(\Exception $e)
                        {
                            $time = $time_row_crawler->children('.time-table-cell2')->innerText();
                            $this->rmb_routes[$route_name][$origin]['time'][] = $time;
                        }
                        //dd($rmb_routes);
                    });

                    $direction_crawler->filter('.svc-detail-frame .fare-table-row')->each(function ($time_row_crawler) use ($route_name, $origin) {
                        $desc = $time_row_crawler->children('.fare-table-cell1')->innerText();
                        $price = $time_row_crawler->children('.fare-table-cell2')->innerText();
                        $this->rmb_routes[$route_name][$origin]['fare'][$desc] = $price;
                        //dd($rmb_routes);
                    });

                    try {
                        $map = $direction_crawler->filter('.svc-detail-frame iframe')->attr('src');
                        $this->rmb_routes[$route_name][$origin]['map'] = $map;
                    }
                    catch(\Exception $e)
                    {

                    }

                });

            }

        }

        dd($this->rmb_routes);
    }
}
