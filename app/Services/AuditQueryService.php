<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * Builds the shared audit-log query (filters + eager loads) used by the
 * web index, web CSV export, and the API index/actions endpoints.
 */
final class AuditQueryService
{
    public function forFilters(Request $request): Builder
    {
        $query = Activity::query()
            ->with(['causer', 'subject' => fn ($q) => $q->withoutGlobalScopes()])
            ->latest();

        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }
        if ($request->filled('causer')) {
            $query->where('causer_id', $request->causer);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query;
    }

    /**
     * Distinct action descriptions for the filter dropdown.
     */
    public function distinctActions()
    {
        return Activity::distinct()->pluck('description')->sort()->values();
    }
}
