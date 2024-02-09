<div>
    <x-tabs wire:model.live="selected_tab" class="max-h-[50vh] overflow-y-scroll">
        <x-tab name="all" label="All" icon="o-users">
            @foreach($routes as $route)
                <x-route-search-item :route="$route"/>
            @endforeach
        </x-tab>
        <x-tab name="bus" label="Bus" icon="o-sparkles">
            @foreach($routes as $route)
                <x-route-search-item :route="$route"/>
            @endforeach
        </x-tab>
        <x-tab name="minibus" label="Mini Bus" icon="o-musical-note">
            @foreach($routes as $route)
                <x-route-search-item :route="$route"/>
            @endforeach
        </x-tab>
    </x-tabs>
    <div class="flex flex-row mt-3">
        <div class="basis-3/5 grid grid-cols-3 gap-2">
            @for($x = 1; $x <= 9; $x++)
                <x-button label="{{ $x }}" class="btn-outline" />
            @endfor
                <x-button label="-" class="btn-outline" />
                <x-button label="0" class="btn-outline" />
                <x-button label="<-" class="btn-outline" />
        </div>
    </div>
</div>
