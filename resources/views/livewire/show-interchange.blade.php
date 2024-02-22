<div>
    <x-layouts.navbar>
        <x-slot:start>
            <div class="flex divide-x" wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}">
                <div class="flex flex-col justify-items-center">
                    <x-heroicon-o-arrow-left class="h-5 w-full"/>
                    <div>返回</div>
                </div>
            </div>
        </x-slot:start>
        <x-slot:title>
            <span wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}">{{ $route->name }} {{ $route->dest_tc }}</span> 轉乘優惠
        </x-slot:title>
    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px)] overflow-y-scroll">
        @if($interchanges->isEmpty())
            此路線未有轉乘優惠
        @endif
        @if($interchange_stops->isNotEmpty())
        <div x-data="{ active: {{ $target_stop ?? $interchange_stops->first()->id }} }" class="mx-auto max-w-3xl w-full min-h-[16rem] space-y-4">
            @foreach($interchange_stops as $stop)
            <div x-data="{
        id: {{ $stop->id }},
        get expanded() {
            return this.active === this.id
        },
        set expanded(value) {
            this.active = value ? this.id : null
        },
    }" role="region" class="rounded-lg">
                <h2>
                    <button
                        x-on:click="expanded = !expanded"
                        :aria-expanded="expanded"
                        class="flex w-full items-center justify-between p-3 text-xl font-bold"
                    >
                        <span>{{ $stop->name_tc }}</span>
                        <span x-show="expanded" aria-hidden="true" class="ml-4">&minus;</span>
                        <span x-show="!expanded" aria-hidden="true" class="ml-4">&plus;</span>
                    </button>
                </h2>

                <div x-show="expanded" x-collapse class="px-3 bg-slate-100 dark:bg-black">
                    @foreach($interchanges[$stop->id] as $route)
                        <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                            class="flex items-center justify-start gap-4 py-3 cursor-pointer">
                            <h4 class="min-w-14 font-bold text-lg">
                                {{ $route->name }}
                            </h4>
                            <div class="flex flex-col">
                                <div class="">
                                    往 {{ $route->dest_tc }}
                                </div>
                                <div class>
                                    {{ $route->pivot->validity_minutes }}分鐘內轉乘
                                    @if($route->pivot->discount_mode == 'minus')
                                        -${{ $route->pivot->discount }}
                                    @endif
                                    @if($route->pivot->discount_mode == 'free')
                                        免費
                                    @endif
                                    @if($route->pivot->discount_mode == 'pay')
                                        付${{ $route->pivot->discount }}
                                    @endif
                                    @if($route->pivot->discount_mode == 'total')
                                        兩程合共${{ $route->pivot->discount }}
                                    @endif
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
