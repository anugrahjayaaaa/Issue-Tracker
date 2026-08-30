<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiToken\ApiTokenStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group API Tokens
 *
 * Personal access tokens (Sanctum) for the authenticated user.
 */
class ApiTokenApiController extends Controller
{
    /** List tokens (plain token shown only on creation). */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->latest()->get(['id', 'name', 'created_at', 'last_used_at']);

        return response()->json(['tokens' => $tokens]);
    }

    /** Create a token. Returns the plain token ONCE. */
    public function store(ApiTokenStoreRequest $request): JsonResponse
    {
        $plain = $request->user()->createToken($request->validated()['name'], ['mobile'])->plainTextToken;

        return response()->json([
            'message' => __('messages.token_created'),
            'plain_token' => $plain,
        ], 201);
    }

    /** Revoke a token. */
    public function destroy(Request $request, int $token): JsonResponse
    {
        $request->user()->tokens()->where('id', $token)->delete();

        return response()->json(['message' => __('messages.token_revoked')]);
    }
}
