<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Stop extends Model
{

    protected $guarded = [];

    protected $geometry = ['position'];


    /**
     * Select geometrical attributes as text from database.
     *
     * @var bool
     */
    protected $geometryAsText = true;

    /**
     * Get a new query builder for the model's table.
     * Manipulate in case we need to convert geometrical fields to text.
     *
     * @param  bool $excludeDeleted
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery($excludeDeleted = true)
    {
        if (!empty($this->geometry) && $this->geometryAsText === true)
        {
            $raw = '';
            foreach ($this->geometry as $column)
            {
                $raw .= 'ST_AsWKT(`' . $this->table . '`.`' . $column . '`) as `' . $column . '`, ';
            }
            $raw = substr($raw, 0, -2);

            return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
        }

        return parent::newQuery($excludeDeleted);
    }

    protected function longitude(): Attribute
    {
        $position = $this->attributes['position'];
        return Attribute::make(
            get: function () use ($position) {
                $start  = strpos($position, '(');
                $end    = strpos($position, ' ', $start + 1);
                $length = $end - $start;
                return substr($position, $start + 1, $length - 1);
            },
        )->shouldCache();
    }

    protected function latitude(): Attribute
    {
        $position = $this->attributes['position'];
        return Attribute::make(
            get: function () use ($position) {
                $start  = strpos($position, ' ');
                $end    = strpos($position, ')', $start + 1);
                $length = $end - $start;
                return substr($position, $start + 1, $length - 1);
            },
        )->shouldCache();
    }

    public function post(): BelongsToMany
    {
        return $this->belongsToMany(Route::class)->using(RouteStop::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class)
            ->using(RouteStop::class);
    }
}
