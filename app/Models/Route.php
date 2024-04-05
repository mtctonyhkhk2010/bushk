<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Route extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class)
            ->withPivot(['sequence', 'fare', 'fare_holiday'])
            ->using(RouteStop::class);
    }

    public function from_stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class)
            ->withPivot(['sequence', 'fare', 'fare_holiday'])
            ->using(RouteStop::class);
    }

    public function to_stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class)
            ->withPivot(['sequence', 'fare', 'fare_holiday'])
            ->using(RouteStop::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withPivot(['bound']);
    }

    public function interchanges(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'interchange', 'from_route_id', 'to_route_id')
            ->withPivot(['from_stop_id', 'to_stop_id', 'validity_minutes', 'discount_mode', 'discount', 'detail', 'success_cnt', 'spec_remark_en', 'spec_remark_tc'])
            ->using(Interchange::class);
    }

    public function serviceTimes(): HasMany
    {
        return $this->hasMany(ServiceTime::class);
    }

    public function mtr_info(): HasOne
    {
        return $this->hasOne(MtrInfo::class, 'line_id', 'name');
    }
}
