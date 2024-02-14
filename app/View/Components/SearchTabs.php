<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Mary\View\Components\Tabs;

class SearchTabs extends Tabs
{
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <div
                        x-data="{
                                tabs: [],
                                selected:
                                    @if($selected)
                                        '{{ $selected }}'
                                    @else
                                        @entangle($attributes->wire('model'))
                                    @endif
                                 ,
                                 init() {
                                     // Fix weird issue when navigating back
                                     document.addEventListener('livewire:navigating', () => {
                                         document.querySelectorAll('.tab').forEach(el =>  el.remove());
                                     });
                                 }
                        }"
                        class="relative h-[calc(100svh-112px-15rem)]"}}
                    >
                        <!-- TAB LABELS -->
                        <div class="border-b-2 border-b-base-200 flex overflow-x-auto">
                            <template x-for="tab in tabs">
                                <a
                                    role="tab"
                                    x-html="tab.label"
                                    @click="selected = tab.name"
                                    :class="(selected === tab.name) && 'border-b-2 border-b-gray-600 dark:border-b-gray-400'"
                                    class="tab font-semibold border-b-2 border-b-base-300"></a>
                            </template>
                        </div>

                        <!-- TAB CONTENT -->
                        <div role="tablist" {{ $attributes->except(['wire:model', 'wire:model.live'])->class(["tabs tabs-bordered block"]) }}>
                            {{ $slot }}
                        </div>
                    </div>
                HTML;
    }
}
