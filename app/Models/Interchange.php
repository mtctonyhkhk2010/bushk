<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Interchange extends Pivot
{
    protected $table = 'interchange';
    public $timestamps = false;

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($value) ? $value / 10 : null,
            set: fn ($value) => isset($value) ? $value * 10 : null,
        );
    }

    protected function detail(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value == '--') return null;
                if ($value == "<a href='javascript:void(0);' class='detailtips' data-title='轉乘優惠只適用於第一程於荃灣或以後登車之乘客'>詳細</a>")
                    return '轉乘優惠只適用於第一程於荃灣或以後登車之乘客';
                return $value;
            },
        );
    }

    protected function specRemarkEn(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value == '') return null;
                return $value;
            },
        );
    }

    protected function specRemarkTc(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value == '') return null;
                return $value;
            },
        );
    }
}
