<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RouteStop extends Pivot
{
    use HasFactory;

    protected function fare(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($value) ? $value / 10 : null,
            set: fn ($value) => isset($value) ? $value * 10 : null,
        );
    }
}
