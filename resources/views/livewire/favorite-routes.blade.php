<div>
    <x-layouts.navbar title="已收藏路線">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px)] divide-y divide-slate-400/25">
        @if(empty($routes))
            <div class="p-3">
                未有收藏路線
            </div>
        @endif
        @foreach($routes as $route)
            <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                 class="flex items-center justify-start gap-4 p-3 cursor-pointer"
                 x-data="{
init() {
this.getNearestStop();
document.addEventListener('position-updated', (e) => {
this.getNearestStop();
});
        },
        getNearestStop() {
            if(window.coords === undefined) return;
            let stop_distance = [];

            this.stops.forEach((stop) => {
                stop_distance.push(this.distance(stop.latitude, stop.longitude, window.coords.latitude, window.coords.longitude));
            });

            this.nearest_stop_index = stop_distance.indexOf(Math.min(...stop_distance));
        },
        distance(lat1, lon1, lat2, lon2) {
            if ((lat1 === lat2) && (lon1 === lon2)) {
                return 0;
            }
            else {
                let radlat1 = Math.PI * lat1/180;
                let radlat2 = Math.PI * lat2/180;
                let theta = lon1-lon2;
                let radtheta = Math.PI * theta/180;
                let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180/Math.PI;
                dist = dist * 60 * 1.1515;
                return dist * 1.609344;
            }
        },
        nearest_stop_index: null,
stops: @js($route->stops)

}"
            >
                <h4 class="min-w-20 font-bold text-lg">
                    {{ $route->name }}
                </h4>
                <div class="flex flex-col">
                    <div>
                        <span class="text-xs">往</span> <span class="text-lg">{{ $route->dest_tc }}</span>
                    </div>
                    <div class="text-xs" x-text="stops[nearest_stop_index].name_tc">

                    </div>
                </div>
                <div>

                </div>
            </div>
        @endforeach
    </div>
</div>
