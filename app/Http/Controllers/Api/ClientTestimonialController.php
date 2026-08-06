<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreClientTestimonialRequest;
use App\Http\Requests\UpdateClientTestimonialRequest;
use App\Models\ClientTestimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientTestimonialController extends Controller
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
                    ['feature' => 'client_testimonials']
                ),
                403
            );
        }

        return response()->json([
            'success'=>true,
            'message'=>'Client testimonials retrieved successfully.',
            'data'=>$request->user()
                ->clientTestimonials()
                ->latest()
                ->get(),
        ]);
    }

    public function store(
        StoreClientTestimonialRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_testimonials']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'client_testimonials',
            $user->clientTestimonials()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$testimonial = $request->user()
            ->clientTestimonials()
            ->create(
                $request->validated()
            );

        return response()->json([
            'success'=>true,
            'message'=>'Client testimonial created successfully.',
            'data'=>$testimonial,
        ],201);

    }

    public function show(
        ClientTestimonial $clientTestimonial,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_testimonials']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $clientTestimonial,
            $request
        );

        return response()->json([
            'success'=>true,
            'message'=>'Client testimonial retrieved successfully.',
            'data'=>$clientTestimonial,
        ]);

    }

    public function update(
        UpdateClientTestimonialRequest $request,
        ClientTestimonial $clientTestimonial
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'client_testimonials']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $clientTestimonial,
            $request
        );

        $clientTestimonial->update(
            $request->validated()
        );

        return response()->json([
            'success'=>true,
            'message'=>'Client testimonial updated successfully.',
            'data'=>$clientTestimonial->fresh(),
        ]);

    }

    public function destroy(
        ClientTestimonial $clientTestimonial,
        Request $request
    ): JsonResponse {

        $this->ensureOwnership(
            $clientTestimonial,
            $request
        );

        $clientTestimonial->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Client testimonial deleted successfully.',
        ]);

    }

    private function ensureOwnership(
        ClientTestimonial $clientTestimonial,
        Request $request
    ): void {

        if(
            $clientTestimonial->user_id !==
            $request->user()->id
        ){
            abort(
                403,
                'You are not authorised to access this testimonial.'
            );
        }

    }
}