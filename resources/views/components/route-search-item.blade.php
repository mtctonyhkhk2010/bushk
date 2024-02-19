@props([
    /** @var \Illuminate\Database\Eloquent\Builder */
    'route'
])

<div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
     class="flex items-center justify-start gap-4 p-3 cursor-pointer">
    <div>
        <h4 class="min-w-20 font-bold text-lg">
            {{ $route->name }}
            @if($route->service_type != 1)
                <span class="text-xs">特別班</span>
            @endif
        </h4>
        <div class="text-xs">
            {{ $route->companies->pluck('name_tc')->implode('+') }}
        </div>
    </div>

    <div class="flex flex-col">
        <div>
            <span class="text-xs">往</span> <span class="text-lg">{{ $route->dest_tc }}</span>
        </div>
        <div class="text-xs">
            {{ $route->orig_tc }}
        </div>
    </div>
</div>
