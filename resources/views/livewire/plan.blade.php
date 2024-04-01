<div>
    <x-layouts.navbar :title="'Plan'">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))]">
        <label class="form-control w-full max-w-xs">
            <div class="label">
                <span class="label-text">From</span>
            </div>
            <input type="text" placeholder="Your location" class="input input-bordered w-full max-w-xs" wire:model="from"/>
        </label>
        <label class="form-control w-full max-w-xs">
            <div class="label">
                <span class="label-text">To</span>
            </div>
            <input type="text" placeholder="To" class="input input-bordered w-full max-w-xs" wire:model="to"/>
        </label>
    </div>
    <x-offline/>
</div>
