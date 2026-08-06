<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageAccessController extends Controller
{
    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function show(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' =>
                $this->packageAccessService
                    ->accessSummary($user),
        ]);
    }
}