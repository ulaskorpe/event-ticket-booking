<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
    /**
     * Filter rows by an inclusive date range on the given column (date part only).
     */
    public function scopeFilterByDate(Builder $query, ?string $from = null, ?string $to = null, string $column = 'date'): Builder
    {
        if ($from !== null && $from !== '') {
            $query->whereDate($column, '>=', $from);
        }

        if ($to !== null && $to !== '') {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Case-insensitive partial match on a string column (default: title).
     */
    public function scopeSearchByTitle(Builder $query, ?string $term, string $column = 'title'): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        return $query->where($column, 'like', '%'.$term.'%');
    }
}
