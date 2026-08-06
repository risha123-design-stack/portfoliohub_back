<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreClientProjectRequest;
use App\Http\Requests\UpdateClientProjectRequest;
use App\Models\ClientProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientProjectController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_projects']
                ),
                403
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Client projects retrieved successfully.',
            'data' => $request->user()
                ->clientProjects()
                ->latest()
                ->get(),
        ]);
    }

    public function store(
        StoreClientProjectRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_projects']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'client_projects',
            $user->clientProjects()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$project = $request->user()
            ->clientProjects()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client project created successfully.',
            'data' => $project,
        ],201);

    }

    public function show(
        ClientProject $clientProject,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_projects']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $clientProject,
            $request
        );

        return response()->json([
            'success'=>true,
            'message'=>'Client project retrieved successfully.',
            'data'=>$clientProject,
        ]);

    }

    public function update(
        UpdateClientProjectRequest $request,
        ClientProject $clientProject
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_projects']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $clientProject,
            $request
        );

        $clientProject->update(
            $request->validated()
        );

        return response()->json([
            'success'=>true,
            'message'=>'Client project updated successfully.',
            'data'=>$clientProject->fresh(),
        ]);

    }

    public function destroy(
        ClientProject $clientProject,
        Request $request
    ): JsonResponse {

        $this->ensureOwnership(
            $clientProject,
            $request
        );

        $clientProject->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Client project deleted successfully.',
        ]);

    }

    private function ensureOwnership(
        ClientProject $clientProject,
        Request $request
    ): void {

        if(
            $clientProject->user_id !==
            $request->user()->id
        ){
            abort(
                403,
                'You are not authorised to access this client project.'
            );
        }

    }
}