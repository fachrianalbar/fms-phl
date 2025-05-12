<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class FilterHelper
{
    /**
     * Apply filters dynamically to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @param array $relations
     * @return Builder
     */
    public static function applyFilters(
        Builder $query,
        array $filters,
        array $relations = [],
        array $dateFilters = []
    ): Builder {
        // Apply regular filters
        foreach ($filters as $alias => $value) {
            if (!empty($value)) {

                if (array_key_exists($alias, $relations)) {
                    $relationPath = explode('.', $relations[$alias]);
                    $column = array_pop($relationPath);
                    $relation = implode('.', $relationPath);

                    if ($alias == 'purchase_data' || $alias == 'maintenance_data') {
                        $query->whereHas($relation, function ($q) use ($value, $column) {
                            $q->where($column, 'like', '%' . $value . '%');
                        })->orWhereHas($relation, function ($q) use ($value, $column) {
                            $q->where($column, 'like', '%' . $value . '%');
                        });
                    } else {
                        $query->whereHas($relation, function ($q) use ($value, $column) {
                            $q->where($column, 'like', '%' . $value . '%');
                        });
                    }
                } else {
                    $query->where($alias, 'like', '%' . $value . '%');
                }
            }
        }

        // Apply date filters
        foreach ($dateFilters as $alias => $dates) {
            if (!empty($dates['start']) || !empty($dates['end'])) {
                if (array_key_exists($alias, $relations)) {
                    $relationPath = explode('.', $relations[$alias]);
                    $column = array_pop($relationPath);
                    $relation = implode('.', $relationPath);

                    $query->whereHas($relation, function ($q) use ($dates, $column) {
                        if (!empty($dates['start']) && !empty($dates['end']) && $dates['start'] === $dates['end']) {
                            // Start date and end date are the same: exact match
                            $q->whereDate($column, '=', $dates['start']);
                        } else {
                            if (!empty($dates['start'])) {
                                $q->whereDate($column, '>=', $dates['start']);
                            }
                            if (!empty($dates['end'])) {
                                $q->whereDate($column, '<=', $dates['end']);
                            }
                        }
                    });
                } else {
                    if (!empty($dates['start']) && !empty($dates['end']) && $dates['start'] === $dates['end']) {
                        // Start date and end date are the same: exact match
                        $query->whereDate($alias, '=', $dates['start']);
                    } else {
                        if (!empty($dates['start'])) {
                            $query->whereDate($alias, '>=', $dates['start']);
                        }
                        if (!empty($dates['end'])) {
                            $query->whereDate($alias, '<=', $dates['end']);
                        }
                    }
                }
            }
        }

        return $query;
    }
}
