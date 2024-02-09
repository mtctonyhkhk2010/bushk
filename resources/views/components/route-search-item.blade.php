@props([
    /** @var \Illuminate\Database\Eloquent\Builder */
    'route'
])

<x-list-item :item="$route" link="/route/{{ $route->id }}/{{ $route->name }}">
    <x-slot:value>
        {{ $route->name }} {{ $route->companies->pluck('name_tc')->implode('+') }}
    </x-slot:value>
    <x-slot:sub-value>
        {{ $route->orig_tc }}
    </x-slot:sub-value>
</x-list-item>
