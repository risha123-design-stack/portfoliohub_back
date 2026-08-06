<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $resumes = $user->resumes()
            ->orderByDesc('is_primary')
            ->latest()
            ->get()
            ->map(
                fn (Resume $resume): array =>
                    $this->formatResume($resume)
            );

        return response()->json([
            'success' => true,
            'resumes' => $resumes,
            'package_limit' => $this->packageLimitData(
                $user,
                $resumes->count()
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'resume',
            $user->resumes()->count(),
            $this->requiredUpgradePackage($user)
        );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate(
            $this->rules(isUpdate: false),
            $this->messages()
        );

        $file = $request->file('resume_file');

        $validated['resume_file'] = $file->store(
            'resumes/' . $user->id,
            'public'
        );

        $validated['original_file_name'] =
            $file->getClientOriginalName();

        $validated['user_id'] = $user->id;
        $validated['is_primary'] =
            $request->boolean('is_primary');

        if ($validated['is_primary']) {
            $user->resumes()->update([
                'is_primary' => false,
            ]);
        }

        $resume = Resume::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resume uploaded successfully.',
            'resume' => $this->formatResume($resume),
            'package_limit' => $this->packageLimitData(
                $user,
                $user->resumes()->count()
            ),
        ], 201);
    }

    public function update(
        Request $request,
        Resume $resume
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership($resume, $user->id);

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $validated = $request->validate(
            $this->rules(isUpdate: true),
            $this->messages()
        );

        if ($request->hasFile('resume_file')) {
            $this->deleteStoredFile(
                $resume->resume_file
            );

            $file = $request->file('resume_file');

            $validated['resume_file'] =
                $file->store(
                    'resumes/' . $user->id,
                    'public'
                );

            $validated['original_file_name'] =
                $file->getClientOriginalName();
        }

        $validated['is_primary'] =
            $request->boolean('is_primary');

        if ($validated['is_primary']) {
            $user->resumes()
                ->whereKeyNot($resume->id)
                ->update([
                    'is_primary' => false,
                ]);
        }

        $resume->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resume updated successfully.',
            'resume' => $this->formatResume(
                $resume->fresh()
            ),
        ]);
    }

    public function destroy(
        Request $request,
        Resume $resume
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership($resume, $user->id);

        $this->deleteStoredFile(
            $resume->resume_file
        );

        $resume->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resume deleted successfully.',
            'package_limit' => $this->packageLimitData(
                $user,
                $user->resumes()->count()
            ),
        ]);
    }

    public function preview(
        Request $request,
        Resume $resume
    ): JsonResponse|StreamedResponse {
        $this->authorizeOwnership(
            $resume,
            $request->user()->id
        );

        if (!$this->storedFileExists($resume)) {
            return response()->json([
                'success' => false,
                'message' => 'Resume file not found.',
            ], 404);
        }

        if (!$this->canPreview($resume)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Only PDF resumes can be previewed in the browser. Download this file to view it.',
            ], 422);
        }

        return Storage::disk('public')->response(
            $resume->resume_file,
            $resume->original_file_name ?: 'resume.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
            ]
        );
    }

    public function download(
        Request $request,
        Resume $resume
    ): JsonResponse|StreamedResponse {
        $this->authorizeOwnership(
            $resume,
            $request->user()->id
        );

        if (!$this->storedFileExists($resume)) {
            return response()->json([
                'success' => false,
                'message' => 'Resume file not found.',
            ], 404);
        }

        $resume->increment('downloads');

        return Storage::disk('public')->download(
            $resume->resume_file,
            $resume->original_file_name ?: 'resume'
        );
    }

    private function rules(bool $isUpdate): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'resume_version' => [
                'nullable',
                'string',
                'max:50',
            ],

            'visibility' => [
                'required',
                Rule::in([
                    'public',
                    'private',
                ]),
            ],

            'is_primary' => [
                'sometimes',
                'boolean',
            ],

            'resume_file' => [
                $isUpdate
                    ? 'nullable'
                    : 'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' =>
                'Resume title is required.',

            'resume_file.required' =>
                'Select a resume file to upload.',

            'resume_file.mimes' =>
                'Upload a PDF, DOC or DOCX resume.',

            'resume_file.max' =>
                'The resume file may not exceed 10 MB.',
        ];
    }

    private function formatResume(
        Resume $resume
    ): array {
        $extension = strtolower(
            pathinfo(
                $resume->original_file_name
                    ?: $resume->resume_file,
                PATHINFO_EXTENSION
            )
        );

        return [
            ...$resume->toArray(),

            'file_extension' => $extension,

            'can_preview' =>
                $extension === 'pdf',

            'preview_endpoint' =>
                "/resumes/{$resume->id}/preview",

            'download_endpoint' =>
                "/resumes/{$resume->id}/download",
        ];
    }

    private function canPreview(
        Resume $resume
    ): bool {
        return strtolower(
            pathinfo(
                $resume->original_file_name
                    ?: $resume->resume_file,
                PATHINFO_EXTENSION
            )
        ) === 'pdf';
    }

    private function storedFileExists(
        Resume $resume
    ): bool {
        return
            (bool) $resume->resume_file &&
            Storage::disk('public')->exists(
                $resume->resume_file
            );
    }

    private function deleteStoredFile(
        ?string $path
    ): void {
        if (
            $path &&
            Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }

    private function moduleAccessError(
        $user
    ): ?JsonResponse {
        if (
            $this->packageAccessService
                ->canAccessModule(
                    $user,
                    'resume'
                )
        ) {
            return null;
        }

        return response()->json(
            $this->packageAccessService
                ->upgradeResponse(
                    'Resume module is available from Gold.',
                    'Gold',
                    [
                        'feature' => 'resume',
                    ]
                ),
            403
        );
    }

    private function packageLimitData(
        $user,
        int $currentCount
    ): array {
        $limit =
            $this->packageAccessService
                ->limit(
                    $user,
                    'resume'
                );

        $limitReached =
            $limit !== null &&
            $currentCount >= $limit;

        return [
            'feature' => 'resume',
            'current_count' => $currentCount,
            'limit' => $limit,
            'unlimited' => $limit === null,
            'limit_reached' => $limitReached,

            'required_package' =>
                $limitReached
                    ? $this->requiredUpgradePackage(
                        $user
                    )
                    : null,
        ];
    }

    private function requiredUpgradePackage(
        $user
    ): string {
        return
            $this->packageAccessService
                ->nextPackage($user)
            ?? 'Platinum';
    }

    private function authorizeOwnership(
        Resume $resume,
        int $userId
    ): void {
        abort_unless(
            (int) $resume->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this resume.'
        );
    }
}
