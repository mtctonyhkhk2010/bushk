<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use Mary\View\Components\Tab;

class SearchTab extends Tab
{
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                    <a
                        class="hidden"
                        :class="{ 'tab-active': selected === '{{ $name }}' }"
                        data-name="{{ $name }}"
                        x-init="
                                tabs.push({ name: '{{ $name }}', label: {{ json_encode($tabLabel()) }} });
                                Livewire.hook('morph.removed', ({el}) => {
                                    if (el.getAttribute('data-name') == '{{ $name }}'){
                                        tabs = tabs.filter(i => i.name !== '{{ $name }}')
                                    }
                                })
                            "
                    ></a>

                    <div x-show="selected === '{{ $name }}'" role="tabpanel" {{ $attributes->class("tab-content px-1") }}>
                        {{ $slot }}
                    </div>
                HTML;
    }
}
