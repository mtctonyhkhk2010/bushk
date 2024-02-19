<div>
    <x-layouts.navbar :title="empty($search) ? '搜尋' : $search">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px)]">
        <x-search-tabs wire:model.live="selected_tab" class="h-[calc(100%-2.5rem)] overflow-y-scroll">
            <x-search-tab name="bus" label="巴士" class="divide-y divide-slate-400/25">
                @foreach($routes as $route)
                    <x-route-search-item :route="$route"/>
                @endforeach
            </x-search-tab>
            <x-search-tab name="minibus" label="小巴" class="divide-y divide-slate-400/25">
                @foreach($routes as $route)
                    <x-route-search-item :route="$route"/>
                @endforeach
            </x-search-tab>
        </x-search-tabs>
        <div class="flex flex-row gap-2 items-start h-2/5 max-h-60">
            <div class="basis-3/5 grid grid-cols-3 gap-2 max-h-60">
                @for($x = 1; $x <= 9; $x++)
                    <button class="btn btn-neutral h-[3.3rem] min-h-[3.3rem]" wire:click="addToSearch({{ $x }})" @if(!in_array($x, $possible_number)) disabled @endif>{{ $x }}</button>
                @endfor
                <button class="btn btn-neutral h-[3.3rem] min-h-[3.3rem]" wire:click="clearSearch()"><x-icon name="m-minus-circle" /></button>
                <button class="btn btn-neutral h-[3.3rem] min-h-[3.3rem]" wire:click="addToSearch(0)" @disabled(!in_array(0, $possible_number))>0</button>
                <button class="btn btn-neutral h-[3.3rem] min-h-[3.3rem]" wire:click="backspace()"><x-icon name="m-backspace" /></button>
            </div>
            <div class="basis-2/5 grid grid-cols-2 gap-2 max-h-60 overflow-y-scroll">
                @foreach($possible_alphabet as $alphabet)
                    <button class="btn btn-neutral h-[2.7rem] min-h-[2.7rem]" wire:click="addToSearch('{{ $alphabet }}')">{{ $alphabet }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>
