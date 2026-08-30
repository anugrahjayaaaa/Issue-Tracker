<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Services\AuditQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Audit
 *
 * Audit log access (gated by `audit.view`).
 */
class AuditApiController extends Controller
{
    public function __construct(private AuditQueryService $audit) {}

    /** List audit entries (filter by ?action=, ?causer=, ?from=, ?to=). */
    public function index(Request $request): JsonResponse
    {
        return response()->json(ActivityResource::collection($this->audit->forFilters($request)->paginate(20))->response()->getData(true));
    }

    /** Available action types (for filter dropdowns). */
    public function actions(): JsonResponse
    {
        return response()->json(['actions' => $this->audit->distinctActions()]);
    }
}
