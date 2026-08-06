<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $skills = $user->skills()
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'skills' => $skills,
            'package_limit' => $this->packageLimitData(
                $this->packageAccessService,
                $user,
                'skills',
                $skills->count()
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'skills',
            $user->skills()->count(),
            $this->packageAccessService->nextPackage($user) ?? 'Platinum'
        );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('skills', 'name')->where(
                    fn ($query) => $query->where('user_id', $user->id)
                ),
            ],
            'skill_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'proficiency_level' => [
                'nullable',
                Rule::in(['Beginner', 'Intermediate', 'Advanced', 'Expert']),
            ],
            'years_of_experience' => 'nullable|numeric|min:0|max:80',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['user_id'] = $user->id;
        $validated['name'] = trim($validated['name']);
        $validated['skill_type'] = isset($validated['skill_type'])
            ? trim($validated['skill_type'])
            : null;
        $validated['category'] = isset($validated['category'])
            ? trim($validated['category'])
            : null;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $skill = Skill::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Skill created successfully.',
            'skill' => $skill,
            'package_limit' => $this->packageLimitData(
                $this->packageAccessService,
                $user,
                'skills',
                $user->skills()->count()
            ),
        ], 201);
    }

    public function update(Request $request, Skill $skill): JsonResponse
    {
        $user = $request->user();

        $this->authorizeOwnership($skill->user_id, $user->id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('skills', 'name')
                    ->ignore($skill->id)
                    ->where(
                        fn ($query) => $query->where('user_id', $user->id)
                    ),
            ],
            'skill_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'proficiency_level' => [
                'nullable',
                Rule::in(['Beginner', 'Intermediate', 'Advanced', 'Expert']),
            ],
            'years_of_experience' => 'nullable|numeric|min:0|max:80',
            'is_featured' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['skill_type'] = isset($validated['skill_type'])
            ? trim($validated['skill_type'])
            : null;
        $validated['category'] = isset($validated['category'])
            ? trim($validated['category'])
            : null;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $skill->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Skill updated successfully.',
            'skill' => $skill->fresh(),
        ]);
    }

    public function destroy(Request $request, Skill $skill): JsonResponse
    {
        $user = $request->user();

        $this->authorizeOwnership($skill->user_id, $user->id);

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill deleted successfully.',
            'package_limit' => $this->packageLimitData(
                $this->packageAccessService,
                $user,
                'skills',
                $user->skills()->count()
            ),
        ]);
    }

    private function authorizeOwnership(int $ownerId, int $userId): void
    {
        abort_unless(
            $ownerId === $userId,
            403,
            'You are not authorized to access this skill.'
        );
    }
}