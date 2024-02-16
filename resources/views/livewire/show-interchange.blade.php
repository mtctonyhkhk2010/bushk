<div>
    <x-custom-header class="mb-0">
        <x-slot:middle>
            {{ $route->name }} 可轉
        </x-slot:middle>
    </x-custom-header>
    <div class="h-[calc(100svh-112px)]">
        <div x-data="{ active: {{ $interchange_stops->first()->id }} }" class="mx-auto max-w-3xl w-full min-h-[16rem] space-y-4">
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

                <div x-show="expanded" x-collapse class="p-3">
                    @foreach($interchanges[$stop->id] as $route)
                        <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                            class="flex flex-col justify-start gap-4 p-3 hover:bg-base-200/50 cursor-pointer">
                            <h4>
                                {{ $route->name }}
                            </h4>
                            <div>
                                @if($route->pivot->discount_mode == 'minus')
                                    -${{ $route->pivot->discount }}
                                @endif
                                @if($route->pivot->discount_mode == 'free')
                                    免費
                                @endif
                                @if($route->pivot->discount_mode == 'pay')
                                    付${{ $route->pivot->discount }}
                                @endif

                                {{ $route->pivot->validity_minutes }}mins
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
