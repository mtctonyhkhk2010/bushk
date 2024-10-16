<div>
    <x-layouts.navbar title="已收藏車站">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))] divide-y divide-slate-400/25">
        @if($stops->isEmpty())
            <div class="p-3">
                未有收藏車站
            </div>
        @endif
        @if($stops->isNotEmpty())
        <div role="tablist" class="tabs tabs-bordered">
            <x-stop-tabs wire:model.live="selected_stop" class="h-[calc(100%-2.5rem)] overflow-y-scroll">
                @foreach($stops as $stop)
                    <x-search-tab name="{{ $stop->id }}" label="{{ $stop->name_tc }}" class="divide-y divide-slate-400/25">
                        @foreach($stop->routes as $route)
                            <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                                 class="flex items-center justify-start gap-4 p-3 cursor-pointer"
                                 x-data="favorite_stop_route(@js($stop), @js($route), @js($route->companies->keyBy('id')))"
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
                    </x-search-tab>
                @endforeach
            </x-stop-tabs>
        </div>
        @endif
    </div>
</div>

@script
<script>
    Alpine.data('favorite_stop_route', (stop, route, companies) => ({
        last_update: null,
        etas: [],
        stop: stop,
        route: route,
        companies: companies,
        init() {
            this.$watch('etas', () => {
                this.etas = this.etas.sort((a, b) => {
                    return a.timestamp - b.timestamp;
                })
            });

            this.getETA();

            const fav_stops_geteta = setInterval(() => {
                this.getETA();
            }, 60000);

            document.addEventListener('livewire:navigating', () => {
                clearInterval(fav_stops_geteta);
            }, { once: true });
        },
        getETA() {

            this.etas = [];
            for (let key in companies) {
                if (!companies.hasOwnProperty(key)) continue;

                const company = companies[key];

                const fetchEta = window.fetchEta(company.co, this.stop.stop_code, this.route.name, this.route.service_type, this.route.gtfs_id, company.pivot.bound, this.route.nlb_id);

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
    }));
</script>
@endscript
