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
            @forelse($suggested_routes as $route)
                <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                     class="flex items-center justify-start gap-4 p-3 cursor-pointer"
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
                        <div class="text-xs">{{ $route->from_stops->first()->name_tc }} -> {{ $route->to_stops->first()->name_tc }}</div>

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
            @empty
                未找到建議路線
            @endforelse
        </div>
    </div>
    <x-offline/>
</div>

@script
<script>
    document.addEventListener('position-updated', (e) => {
        $wire.set('current_location', {
            'latitude': window.coords.latitude,
            'longitude': window.coords.longitude,
        });
    }, { once: true });
</script>
@endscript
