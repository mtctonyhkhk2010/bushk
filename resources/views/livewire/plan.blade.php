<div>
    <x-layouts.navbar :title="'規劃'">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))] px-3">
        <label class="input input-bordered flex items-center gap-2 mb-3 relative
        @if(!empty($from) && empty($this->from_location)) input-warning @endif
                       @if(isset($current_location) || (!empty($from) && !empty($this->from_location))) input-success @endif">
            <span>由</span>
            <input type="text"
                   placeholder="你的位置"
                   class="grow"
                   wire:model.live="from"/>
            @if(!empty($this->from_suggestion) && $show_from_suggestion)
                <ul tabindex="0" class="z-[1] menu bg-base-200 w-full rounded-box absolute left-0 top-12">
                    @foreach($this->from_suggestion['places'] as $key => $suggestion)
                        <li><a wire:click="saveFromPosition({{ $key }})">{{ $suggestion['displayName']['text'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </label>
        <label class="input input-bordered flex items-center gap-2 relative
        @if(!empty($to) && empty($this->to_location)) input-warning @endif
               @if(!empty($to) && !empty($this->to_location)) input-success @endif">
            <span>至</span>
            <input type="text"
               placeholder="地點"
               class="grow"
               wire:model.live="to"/>
            @if(!empty($this->to_suggestion) && $show_to_suggestion)
                <ul tabindex="0" class="z-[1] menu bg-base-200 w-full rounded-box absolute left-0 top-12">
                    @foreach($this->to_suggestion['places'] as $key => $suggestion)
                        <li><a wire:click="saveToPosition({{ $key }})">{{ $suggestion['displayName']['text'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </label>

        <div>
            <h5 class="mt-3">建議路線</h5>
            @forelse($suggested_routes as $key => $route)
                <div wire:navigate href="/route/{{ $route['steps'][0]['system_route']->id }}/{{ $route['steps'][0]['system_route']->name }}"
                     class="flex items-center justify-start gap-4 py-3 cursor-pointer"
                     x-data="plan(@js($route['steps'][0]['system_from_stop']->stop_code), @js($route['steps'][0]['system_route']), @js($route['steps'][0]['system_route']->companies->keyBy('id')))"
                     id="route_{{ $key }}_{{ $route['steps'][0]['system_route']->id }}"
                >
                    <div>
                        <div class="min-w-20 font-bold text-lg flex">
                            @foreach($route['steps'] as $step)
                                <div class="badge badge-outline">{{ $step['name'] }}</div>
                                @if(!$loop->last)
                                    <x-heroicon-o-chevron-right class="h-5 w-5"></x-heroicon-o-chevron-right>
                                @endif
                            @endforeach
                        </div>
                        <div class="flex mt-1 items-center">
                            <div class="badge badge-outline"><x-heroicon-o-clock class="h-5 w-5"></x-heroicon-o-clock>{{ $route['duration'] }}分鐘</div> |
                            @foreach($route['steps'] as $step)
                                @if($loop->iteration == 1)
                                    <div class="badge badge-outline">{{ $step['from']['name'] }}</div>
                                    <x-heroicon-o-chevron-right class="h-5 w-5"></x-heroicon-o-chevron-right>
                                    <div class="badge badge-outline">{{ $step['to']['name'] }}</div>
                                    @php
                                        $last_stop = $step['to']['name']
                                    @endphp
                                @endif
                                @if($loop->iteration > 1)
                                    <x-heroicon-o-chevron-right class="h-5 w-5"></x-heroicon-o-chevron-right>
                                    @if($last_stop != $step['from']['name'])
                                        <div class="badge badge-outline">{{ $step['from']['name'] }}</div>
                                        <x-heroicon-o-chevron-right class="h-5 w-5"></x-heroicon-o-chevron-right>
                                    @endif
                                    <div class="badge badge-outline">{{ $step['to']['name'] }}</div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col ml-auto">
                        <template x-if="loading">
                            <span>loading..</span>
                        </template>
                        <template x-if="!loading">
                            <div>
                                <span x-show="etas.length === 0">未有班次</span>
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
                        </template>
                    </div>
                </div>
            @empty
                未找到建議路線
            @endforelse
        </div>
    </div>
    <x-offline/>
</div>

@script
<script>
    navigator.geolocation.getCurrentPosition((position) => {
        $wire.set('current_location', {
            'latitude': position.coords.latitude,
            'longitude': position.coords.longitude,
        });
    });
    document.addEventListener('position-updated', () => {
        $wire.set('current_location', {
            'latitude': window.coords.latitude,
            'longitude': window.coords.longitude,
        });
    }, { once: true });

    Alpine.data('plan', (start_stop_code, route, companies) => ({
        start_stop_code: start_stop_code,
        last_update: null,
        etas: [],
        companies: companies,
        route: route,
        loading: true,
        init() {
            this.etas = [];
            this.$watch('etas', () => {
                this.etas = this.etas.sort((a, b) => {
                    return a.timestamp - b.timestamp;
                })
            });
            this.getETA();
        },

        getETA() {
            this.etas = [];
            for (let key in this.companies) {
                if (!this.companies.hasOwnProperty(key)) continue;

                const company = this.companies[key];

                const fetchEta = window.fetchEta(company.co, this.start_stop_code, this.route.name, this.route.service_type, this.route.gtfs_id, company.pivot.bound, this.route.nlb_id);

                fetchEta.then((temp_etas) => {
                    temp_etas.forEach((eta) => {
                        this.etas.push(eta);
                    });
                    this.loading = false;
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
        }
    }));
</script>
@endscript
