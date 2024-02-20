<div>
    <x-layouts.navbar title="已收藏車站">

    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px)] divide-y divide-slate-400/25">
        @if(empty($stops))
            <div class="p-3">
                未有收藏車站
            </div>
        @endif
        @foreach($stops as $stop)
            <div wire:navigate href="/route/{{ $stop->id }}/{{ $stop->name }}"
                 class="flex items-center justify-start gap-4 p-3 cursor-pointer">
                <h4 class="min-w-14 font-bold text-lg">
                    {{ $stop->name }}
                </h4>
                <div class="flex flex-col">

                </div>
            </div>
        @endforeach
    </div>
</div>
