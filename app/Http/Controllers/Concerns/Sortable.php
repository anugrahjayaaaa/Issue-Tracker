<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * Apply soft-delete-last ordering + optional column sort.
     * Soft-deleted rows (deleted_at non-null) always sort BELOW active rows.
     */
    protected function sortIndex(Builder $query, $request, string $defaultSort = 'name', array $sortable = []): Builder
    {
        // Soft-deleted always at the bottom (NULLS FIRST in MySQL/InnoDB ASC).
        $query->orderBy('deleted_at', 'asc');

        $col = $request->input('sort');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($col && in_array($col, $sortable, true)) {
            $query->orderBy($col, $dir);
        } else {
            $query->orderBy($defaultSort, $dir);
        }

        return $query;
    }
}
