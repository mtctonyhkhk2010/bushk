<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Route extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class)
            ->withPivot(['sequence', 'fare'])
            ->using(RouteStop::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withPivot(['bound']);
    }

    public function interchanges(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'interchange', 'to_route_id', 'from_route_id')
            ->withPivot(['stop_id', 'validity_minutes', 'discount_mode', 'discount', 'detail', 'success_cnt', 'spec_remark_en', 'spec_remark_tc'])
            ->using(Interchange::class);
    }
}
