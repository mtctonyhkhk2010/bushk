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
        $rmb_routes = Cache::remember('rmb_routes', 50000, function () {
            $frontpage = Cache::remember('16seat_frontpage', 100000, function () {
                return Http::get('https://www.16seats.net/chi/index.html')->body();
            });
            $frontpage_crawler = new Crawler($frontpage);
            foreach ($frontpage_crawler->filter('li a') as $domElement) {
                $area_link = $domElement->getAttribute('href');
                if (!str_contains($area_link, 'rmb')) continue;
                $area_content = Cache::remember('16seat_link'.$area_link, 100000, function () use ($area_link) {
                    return Http::get('https://www.16seats.net/chi/'.$area_link)->body();
                });

                $area_crawler = (new Crawler($area_content))->filter('#SvcList a');
                foreach ($area_crawler as $area)
                {
                    $route_link = $area->getAttribute('href');
                    //dd($route_link);
                    $route_content = Cache::remember('16seat_route_'.$route_link, 100000, function () use ($route_link) {
                        return Http::get('https://www.16seats.net/chi/rmb/'.$route_link)->body();
                    });


                    $route_crawler = (new Crawler($route_content));
                    try {
                        //dd($route_crawler->filter('.svcname-rmb-entry')->count());
                        $route_name = $route_crawler->filter('.svcname-rmb-entry')->innerText();
                    }
                    catch(\Exception $e)
                    {
                        continue;
                    }
                    $route_crawler->filter('.svcname-rmb-entry')->each(function (Crawler $variation_crawler, $variation_index) use ($route_name, $route_crawler) {

                        $route_variation = $route_crawler->filter('.svc-entry-table-r-head1')->getNode($variation_index)->nodeValue . '-' .
                            $route_crawler->filter('.svc-entry-table-r-head3')->getNode($variation_index)->nodeValue;

                        $tags = $route_crawler->filter('.svc-entry-tag')->getNode($variation_index)->childNodes;

                        foreach ($tags as $tag)
                        {
                            //dd($tag->nodeValue);
                            if (get_class($tag) == 'DOMText') continue;
                            if ($tag->getAttribute('class') == 'tag-rsvn')
                            {
                                $this->rmb_routes[$route_name][$route_variation]['rsvn'] = $tag->nodeValue;
                                continue;
                            }
                            $this->rmb_routes[$route_name][$route_variation]['tags'][] = $tag->nodeValue;
                            $this->rmb_routes[$route_name][$route_variation]['tags'] = array_unique($this->rmb_routes[$route_name][$route_variation]['tags']);
                        }

                        $route_crawler->filter('.svc-detail-frame')->each(function (Crawler $direction_crawler) use ($route_variation, $route_name) {
                            // DO THIS: specify the parent tag too
                            $origin = $direction_crawler->filter('.svc-detail-frame .intro-origin')->innerText();

                            $direction_crawler->filter('.svc-detail-frame .time-table-row')->each(function ($time_row_crawler) use ($route_variation, $route_name, $origin) {
                                try {
                                    $weekday = $time_row_crawler->children('.time-table-cell1')->innerText();
                                    $time = $time_row_crawler->children('.time-table-cell2')->innerText();
                                    $this->rmb_routes[$route_name][$route_variation]['direction'][$origin]['time'][$weekday] = $time;
                                }
                                catch(\Exception $e)
                                {
                                    $time = $time_row_crawler->children('.time-table-cell2')->innerText();
                                    $this->rmb_routes[$route_name][$route_variation]['direction'][$origin]['time'][] = $time;
                                }
                                //dd($rmb_routes);
                            });

                            $direction_crawler->filter('.svc-detail-frame .fare-table-row')->each(function ($time_row_crawler) use ($route_variation, $route_name, $origin) {
                                $desc = $time_row_crawler->children('.fare-table-cell1')->innerText();
                                $price = $time_row_crawler->children('.fare-table-cell2')->innerText();
                                $this->rmb_routes[$route_name][$route_variation]['direction'][$origin]['fare'][$desc] = $price;
                                //dd($rmb_routes);
                            });

                            try {
                                $map = $direction_crawler->filter('.svc-detail-frame iframe')->attr('src');
                                $this->rmb_routes[$route_name][$route_variation]['direction'][$origin]['map'] = substr($map, strpos($map, 'mid=') + strlen('mid='));
                            }
                            catch(\Exception $e)
                            {

                            }

                        });


                        //dd($this->rmb_routes);
                    });

                }

            }
            return $this->rmb_routes;
        });


        foreach ($rmb_routes as $route)
        {
            foreach ($route as $variation)
            {
                foreach ($variation['direction'] as $direction)
                {
                    $map = Http::get("https://www.google.com/maps/d/u/0/kml?mid={$direction['map']}&forcekml=1")->body();
                    $map_data = simplexml_load_string($map);
                    dd($map_data);
                }
            }
        }
    }
}
