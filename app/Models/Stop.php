<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stop extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function post(): BelongsToMany
    {
        return $this->belongsToMany(Route::class)->using(RouteStop::class);
    }
}
