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
            <span wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}">{{ $route->name }} {{ $route->dest_tc }}</span> 行車時間
        </x-slot:title>
    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))] overflow-y-scroll px-3">
        @if($service_times->isEmpty())
            未有此路線的行車時間資訊
        @endif
        @if($service_times->isNotEmpty())
            @foreach($service_times as $service_time)
                <div class="font-bold mt-3">{{ $service_time[0]['weekday_tc'] }}</div>
                @foreach($service_time as $time_period)
                    <div>{{ $time_period['start'] }} - {{ $time_period['end'] }} {{ $time_period['frequency_min'] }} 分鐘</div>
                @endforeach
            @endforeach
        @endif
    </div>
</div>
