<div>
    <x-layouts.navbar title="已收藏路線">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))] divide-y divide-slate-400/25">
        @if($routes->isEmpty())
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
            document.addEventListener('livewire:navigating', () => {
                document.removeEventListener('position-updated', (e) => {
                    this.getNearestStop();
                });
            });
            this.$watch('etas', () => {
                this.etas = this.etas.sort((a, b) => {
                    return a.timestamp - b.timestamp;
                })
            });
        },
        getNearestStop() {
            if(window.coords === undefined) return;
            let stop_distance = [];

            this.stops[this.first_company_id].forEach((stop) => {
                stop_distance.push(window.distance(stop.latitude, stop.longitude, window.coords.latitude, window.coords.longitude));
            });

            this.nearest_stop_index = stop_distance.indexOf(Math.min(...stop_distance));

            if(this.last_update === null || Date.now() - this.last_update > 30*1000) this.getETA();
        },
        getETA() {
            this.etas = [];
            for (let key in this.companies) {
                if (!this.companies.hasOwnProperty(key)) continue;

                const company = this.companies[key];

                const fetchEta = window.fetchEta(company.co, this.stops[company.id][this.nearest_stop_index]['stop_code'], @js($route->name), @js($route->service_type), @js($route->gtfs_id), company.pivot.bound, @js($route->nlb_id));

                fetchEta.then((temp_etas) => {
                    temp_etas.forEach((eta) => {
                        this.etas.push(eta);
                    });
                });
            }
            this.last_update = Date.now();
        },
        formatTime(time) {
            const date = new Date(time);
            return this.padTo2Digits(date.getHours()) + ':' + this.padTo2Digits(date.getMinutes());
        },
        padTo2Digits(num) {
            return String(num).padStart(2, '0');
        },
        remainingTimeInMinutes(time) {
            const date = new Date(time);
            const now = new Date();
            const diffMs = (date - now); // milliseconds between now & Christmas
            const diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
            if (diffMins <= 0) return 0;
            return diffMins;
        },

        nearest_stop_index: null,
        last_update: null,
        etas: [],
        companies: @js($route->companies->keyBy('id')),
        first_company_id: @js(array_key_first($stops[$route->id])),
stops: @js($stops[$route->id])

}"
            >
                <div>
                    <h4 class="min-w-20 font-bold text-lg">
                        {{ $route->name }}
                        @if($route->service_type != 1)
                            <span class="text-xs">特別班</span>
                        @endif
                    </h4>
                    <div class="text-xs">
                        {{ $route->companies->pluck('name_tc')->implode('+') }}
                    </div>
                </div>
                <div class="flex flex-col">
                    <div>
                        <span class="text-xs">往</span> <span class="text-lg">{{ $route->dest_tc }}</span>
                    </div>
                    <template x-if="nearest_stop_index !== null">
                        <div class="text-xs" x-text="stops[first_company_id][nearest_stop_index]['name_tc']"></div>
                    </template>

                </div>
                <div class="flex flex-col ml-auto">
                    <template x-for="eta in etas">
                        <div class="text-xs">
                            <span x-text="formatTime(eta.eta)"></span>
                            <span x-show="remainingTimeInMinutes(eta.eta) > 0">
                                <span x-text="remainingTimeInMinutes(eta.eta)"></span>分鐘
                            </span>
                            <span x-show="remainingTimeInMinutes(eta.eta) == 0">
                                即將到達
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        @endforeach
    </div>
</div>
