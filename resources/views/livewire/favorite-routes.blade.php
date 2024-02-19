<div>
    <x-layouts.navbar title="已收藏路線">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px)] divide-y divide-slate-400/25">
        @if(empty($routes))
            <div class="p-3">
                未有收藏路線
            </div>
        @endif
        @foreach($routes as $route)
            <div wire:navigate href="/route/{{ $route->id }}/{{ $route->name }}"
                 class="flex items-center justify-start gap-4 p-3 cursor-pointer">
                <h4 class="min-w-14 font-bold text-lg">
                    {{ $route->name }}
                </h4>
                <div class="flex flex-col">
                    <div class="">
                        往 {{ $route->dest_tc }}
                    </div>
                    <div class>
                        abc
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
