<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekday_id',
        'weekday',
        'start',
        'end',
        'frequency_min',
    ];
}
