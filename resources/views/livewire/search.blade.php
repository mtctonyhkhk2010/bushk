<div>
    <x-header class="!mb-0" title="{{ empty($search) ? 'Search' : $search }}" separator/>
    <div class="h-[69vh]">
        <x-search-tabs wire:model.live="selected_tab" class="h-[calc(100%-2rem)] overflow-y-scroll">
            <x-tab name="all" label="全部">
                @foreach($routes as $route)
                    <x-route-search-item :route="$route"/>
                @endforeach
            </x-tab>
            <x-tab name="bus" label="巴士">
                @foreach($routes as $route)
                    <x-route-search-item :route="$route"/>
                @endforeach
            </x-tab>
            <x-tab name="minibus" label="小巴">
                @foreach($routes as $route)
                    <x-route-search-item :route="$route"/>
                @endforeach
            </x-tab>
        </x-search-tabs>
        <div class="flex flex-row mt-3 gap-2 items-start h-2/5">
            <div class="basis-3/5 grid grid-cols-3 gap-2 max-h-60">
                @for($x = 1; $x <= 9; $x++)
                    <button class="btn btn-neutral" wire:click="addToSearch({{ $x }})" @if(!in_array($x, $possible_number)) disabled @endif>{{ $x }}</button>
                @endfor
                <button class="btn btn-neutral" wire:click="clearSearch()">-</button>
                <button class="btn btn-neutral" wire:click="addToSearch(0)" @disabled(!in_array(0, $possible_number))>{{ 0 }}</button>
                <button class="btn btn-neutral" wire:click="backspace()"><-</button>
            </div>
            <div class="basis-2/5 grid grid-cols-2 gap-2 max-h-60 overflow-y-scroll">
                @foreach($possible_alphabet as $alphabet)
                    <button class="btn btn-neutral" wire:click="addToSearch('{{ $alphabet }}')">{{ $alphabet }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>
