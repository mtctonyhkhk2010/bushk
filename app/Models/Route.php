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
}
