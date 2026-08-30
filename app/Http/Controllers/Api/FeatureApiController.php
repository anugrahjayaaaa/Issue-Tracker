<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;

/**
 * @group Features
 *
 * Feature flags (gated by `feature.manage`).
 */
class FeatureApiController extends Controller
{
    /** List all feature flags. */
    public function index(): JsonResponse
    {
        $features = collect(config('pennant.features'))
            ->map(fn ($meta, $slug) => [
                'slug' => $slug,
                'label' => $meta['label'] ?? $slug,
                'enabled' => Feature::active($slug),
            ])
            ->sortBy('label')
            ->values();

        return response()->json(FeatureResource::collection($features));
    }

    /** Toggle a feature flag. */
    public function toggle(Request $request, string $slug): JsonResponse
    {
        abort_unless(array_key_exists($slug, config('pennant.features', [])), 404);

        $request->boolean('enabled')
            ? Feature::activate($slug)
            : Feature::deactivate($slug);

        return response()->json(new FeatureResource([
            'slug' => $slug,
            'label' => featureLabel($slug),
            'enabled' => Feature::active($slug),
        ]));
    }
}
