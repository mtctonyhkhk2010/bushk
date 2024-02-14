<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Mary\View\Components\Header;

class CustomHeader extends Header
{
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div id="{{ $anchor }}" {{ $attributes->class(["", "mary-header-anchor" => $withAnchor]) }}>
                    <div class="flex flex-wrap gap-5 justify-between items-center h-[56px]">
                        <div>
                            <div @class(["$size font-extrabold", is_string($title) ? '' : $title?->attributes->get('class') ]) >
                                @if($withAnchor)
                                    <a href="#{{ $anchor }}">
                                @endif

                                {{ $title }}

                                @if($withAnchor)
                                    </a>
                                @endif
                            </div>

                            @if($subtitle)
                                <div @class(["text-gray-500 text-sm mt-1", is_string($subtitle) ? '' : $subtitle?->attributes->get('class') ]) >
                                    {{ $subtitle }}
                                </div>
                            @endif
                        </div>
                        <div @class(["flex items-center justify-center gap-3 grow order-last sm:order-none", is_string($middle) ? '' : $middle?->attributes->get('class')])>
                            <div class="w-full lg:w-auto">
                                {{ $middle }}
                            </div>
                        </div>
                        <div @class(["flex items-center gap-3", is_string($actions) ? '' : $actions?->attributes->get('class') ]) >
                            {{ $actions}}
                        </div>
                    </div>

                    @if($separator)
                        <hr class="my-5" />

                        @if($progressIndicator)
                            <div class="h-0.5 -mt-9 mb-9">
                                <progress
                                    class="progress progress-primary w-full h-0.5 dark:h-1"
                                    wire:loading

                                    @if($progressTarget())
                                        wire:target="{{ $progressTarget() }}"
                                     @endif></progress>
                            </div>
                        @endif
                    @endif
                </div>
                HTML;
    }
}
