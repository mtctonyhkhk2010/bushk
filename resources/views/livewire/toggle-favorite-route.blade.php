<button class="btn btn-ghost btn-circle">
    @if(in_array($route_id, session()->get('favorite_routes') ?? []))
        <x-heroicon-s-heart class="h-5 w-5 mr-2" wire:click="removeFavoriteRoute"/>
    @else
        <x-heroicon-o-heart class="h-5 w-5 mr-2" wire:click="addFavoriteRoute"/>
    @endif
</button>
