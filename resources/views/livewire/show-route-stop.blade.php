<div class="dark:bg-base-200"
     id="stop_{{ $stop['pivot']['sequence'] }}"
     wire:key="stop_{{ $stop['pivot']['sequence'] }}"
     x-data="{
                            sequence: {{ $stop['pivot']['sequence'] }},
                            get expanded() {
                                return this.active === this.sequence
                            },
                            set expanded(value) {
                                this.active = value ? this.sequence : null
                            },
                        }">
    <div class="text-xl font-medium p-3 cursor-pointer"
         x-on:click="getETA({{ $stop['pivot']['sequence'] }})"
    >
        <h6>{{ $stop['pivot']['sequence'] + 1 }}. {{ $stop['name_tc'] }}</h6>
        @if($stop['pivot']['fare'] > 0)
            <span>@if(isset($stop['pivot']['fare_holiday']) && $stop['pivot']['fare_holiday'] > 0)平日: @endif${{ $stop['pivot']['fare'] }}</span>
        @endif
        @if(isset($stop['pivot']['fare_holiday']) && $stop['pivot']['fare_holiday'] > 0)
            <span>假日: ${{ $stop['pivot']['fare_holiday'] }}</span>
        @endif
        @if($last_stop)
            <span>終點站</span>
        @endif
    </div>
    <div class="p-3 bg-slate-100 dark:bg-black flex justify-between items-center" x-show="expanded" x-collapse >
        <div>
            <div class="loader" x-show="loading && active === sequence"></div>
            <div x-show="!loading && etas.length === 0">
                未有預定班次
            </div>
            <template x-for="eta in etas" >
                <div x-show="!loading">
                    <span x-text="formatTime(eta.eta)"></span>
                    (<span x-show="remainingTimeInMinutes(eta.eta) > 0">
                                    <span x-text="remainingTimeInMinutes(eta.eta)"></span>分鐘
                                </span>
                    <span x-show="remainingTimeInMinutes(eta.eta) == 0">
                                    即將到達
                                </span>)
                    <span x-show="Object.keys(companies).length > 1" x-text="'- ' + eta.co"></span> <span x-show="eta.remark !== null && eta.remark.length > 1" x-text="'- ' + eta.remark"></span>
                </div>
            </template>
        </div>
        <div class="flex">
            <button class="p-0">
                <x-heroicon-o-information-circle class="h-5 w-5 mr-2 cursor-pointer"
                                                wire:navigate
                                                href="/stop/{{ $stop['id'] }}"/>
            </button>
            @if($stop['interchangeable'])
                <button class="p-0">
                <x-heroicon-o-arrows-right-left class="h-5 w-5 mr-2 cursor-pointer"
                                                wire:navigate
                                                href="/interchange/{{ $route_id }}?stop={{ $stop['id'] }}"/>
                </button>
            @endif

            <livewire:toggle-favorite-stop :stop_code="$stop['stop_code']"/>
        </div>
    </div>
</div>
