<button class="p-0">
    @if(in_array($stop_code, session()->get('favorite_stops2') ?? []))
        <x-heroicon-s-heart class="h-5 w-5 mr-2 cursor-pointer" wire:click="removeFavoriteStop('{{ $stop_code }}')"/>
    @else
        <x-heroicon-o-heart class="h-5 w-5 mr-2 cursor-pointer" wire:click="addFavoriteStop('{{ $stop_code }}')"/>
    @endif
</button>
