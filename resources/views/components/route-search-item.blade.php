@props([
    /** @var \Illuminate\Database\Eloquent\Builder */
    'route'
])

<x-list-item :item="$route" link="/route/{{ $route->id }}/{{ $route->name }}">
    <x-slot:value>
        {{ $route->name }} 往 {{ $route->dest_tc }}
    </x-slot:value>
    <x-slot:sub-value>
        {{ $route->companies->pluck('name_tc')->implode('+') }}
        @if($route->service_type != 1)
            (特別班)
        @endif
    </x-slot:sub-value>
</x-list-item>
