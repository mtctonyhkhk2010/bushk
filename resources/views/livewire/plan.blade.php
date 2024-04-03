<div>
    <x-layouts.navbar :title="'Plan'">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))] px-3">
        <label class="form-control w-full max-w-xs">
            <div class="label">
                <span class="label-text">From</span>
            </div>
            <div class="dropdown">
                <input type="text"
                       placeholder="Your location"
                       class="input input-bordered w-full max-w-xs
                       @if(!empty($from) && empty($this->from_location)) input-warning @endif
                       @if(!empty($from) && !empty($this->from_location)) input-success @endif
                       "
                       wire:model.live="from"/>
                @if(!empty($this->from_suggestion) && $show_from_suggestion)
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-full">
                        @foreach($this->from_suggestion['places'] as $key => $suggestion)
                            <li><a wire:click="saveFromPosition({{ $key }})">{{ $suggestion['displayName']['text'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </label>
        <label class="form-control w-full max-w-xs">
            <div class="label">
                <span class="label-text">To</span>
            </div>
            <input type="text"
                   placeholder="To"
                   class="input input-bordered w-full max-w-xs
                   @if(!empty($to) && empty($this->to_location)) input-warning @endif
                   @if(!empty($to) && !empty($this->to_location)) input-success @endif
                   "
                   wire:model.live="to"/>
            @if(!empty($this->to_suggestion) && $show_to_suggestion)
                <ul class="p-2 shadow menu dropdown-content z-[1] bg-base-100 rounded-box w-52">
                    @foreach($this->to_suggestion['places'] as $key => $suggestion)
                        <li><a wire:click="saveToPosition({{ $key }})">{{ $suggestion['displayName']['text'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </label>
    </div>
    <x-offline/>
</div>
