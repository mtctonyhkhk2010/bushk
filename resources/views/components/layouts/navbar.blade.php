@props(['title'])

<div class="navbar h-[56px] min-h-[56px] bg-[#7dcfeb] dark:bg-base-200">
    <div class="navbar-start">
        @if(isset($start)) {{ $start }} @endif
    </div>
    <div class="navbar-center">
        {{ $title }}
    </div>
    <div class="navbar-end">
        @if(isset($end)) {{ $end }} @endif
    </div>
</div>
