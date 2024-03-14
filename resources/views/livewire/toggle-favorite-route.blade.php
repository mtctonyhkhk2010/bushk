<div>
    @if($favorite)
        <x-heroicon-s-heart class="h-5 w-5 mr-2 cursor-pointer" wire:click="removeFavoriteRoute"/>
    @else
        <x-heroicon-o-heart class="h-5 w-5 mr-2 cursor-pointer" wire:click="addFavoriteRoute"/>
    @endif
</div>
