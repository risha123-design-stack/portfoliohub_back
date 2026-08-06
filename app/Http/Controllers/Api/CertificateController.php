<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $certificates = $user->certificates()
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('issue_date')
            ->latest()
            ->get()
            ->map(
                fn (Certificate $certificate): array =>
                    $this->formatCertificate($certificate)
            );

        return response()->json([
            'success' => true,
            'certificates' => $certificates,
            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'certificates',
                    $certificates->count()
                ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'certificates',
            $user->certificates()->count(),
            $this->packageAccessService
                ->nextPackage($user)
                ?? 'Platinum'
        );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate(
            $this->rules(isUpdate: false),
            $this->messages()
        );

        $validated = $this->prepareData(
            $validated,
            $request
        );

        $validated['user_id'] = $user->id;

        if (
            $request->hasFile(
                'certificate_file'
            )
        ) {
            $file = $request->file(
                'certificate_file'
            );

            $validated['certificate_file'] =
                $file->store(
                    'certificates/' . $user->id,
                    'public'
                );

            $validated['original_file_name'] =
                $file->getClientOriginalName();
        }

        $certificate =
            Certificate::create($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Certificate created successfully.',
            'certificate' =>
                $this->formatCertificate(
                    $certificate
                ),
            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'certificates',
                    $user->certificates()->count()
                ),
        ], 201);
    }

    public function update(
        Request $request,
        Certificate $certificate
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $certificate,
            $user->id
        );

        $validated = $request->validate(
            $this->rules(isUpdate: true),
            $this->messages()
        );

        $validated = $this->prepareData(
            $validated,
            $request
        );

        if (
            $request->boolean(
                'remove_certificate_file'
            )
        ) {
            $this->deleteStoredFile(
                $certificate->certificate_file
            );

            $validated[
                'certificate_file'
            ] = null;

            $validated[
                'original_file_name'
            ] = null;
        }

        if (
            $request->hasFile(
                'certificate_file'
            )
        ) {
            $this->deleteStoredFile(
                $certificate->certificate_file
            );

            $file = $request->file(
                'certificate_file'
            );

            $validated['certificate_file'] =
                $file->store(
                    'certificates/' . $user->id,
                    'public'
                );

            $validated['original_file_name'] =
                $file->getClientOriginalName();
        }

        unset(
            $validated[
                'remove_certificate_file'
            ]
        );

        $certificate->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Certificate updated successfully.',
            'certificate' =>
                $this->formatCertificate(
                    $certificate->fresh()
                ),
        ]);
    }

    public function destroy(
        Request $request,
        Certificate $certificate
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $certificate,
            $user->id
        );

        $this->deleteStoredFile(
            $certificate->certificate_file
        );

        $certificate->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Certificate deleted successfully.',
            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'certificates',
                    $user->certificates()->count()
                ),
        ]);
    }

    public function preview(
        Request $request,
        Certificate $certificate
    ): JsonResponse|StreamedResponse {
        $this->authorizeOwnership(
            $certificate,
            $request->user()->id
        );

        if (!$this->storedFileExists($certificate)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Certificate file not found.',
            ], 404);
        }

        return Storage::disk('public')->response(
            $certificate->certificate_file,
            $certificate->original_file_name
                ?: 'certificate',
            [
                'Content-Disposition' =>
                    'inline',
            ]
        );
    }

    public function download(
        Request $request,
        Certificate $certificate
    ): JsonResponse|StreamedResponse {
        $this->authorizeOwnership(
            $certificate,
            $request->user()->id
        );

        if (!$this->storedFileExists($certificate)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Certificate file not found.',
            ], 404);
        }

        return Storage::disk('public')->download(
            $certificate->certificate_file,
            $certificate->original_file_name
                ?: 'certificate'
        );
    }

    private function rules(bool $isUpdate): array
    {
        return [
            'certificate_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'issuing_organization' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'category' => [
                'nullable',
                'string',
                'max:150',
            ],

            'credential_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'credential_url' => [
                'nullable',
                'url:http,https',
                'max:1000',
            ],

            'issue_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:issue_date',
            ],

            'never_expires' => [
                'sometimes',
                'boolean',
            ],

            'certificate_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'remove_certificate_file' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'certificate_name.required' =>
                'Certificate name is required.',

            'issuing_organization.required' =>
                'Issuing organization is required.',

            'credential_url.url' =>
                'Enter a valid HTTP or HTTPS credential URL.',

            'issue_date.before_or_equal' =>
                'Issue date cannot be in the future.',

            'expiry_date.after_or_equal' =>
                'Expiry date cannot be earlier than the issue date.',

            'certificate_file.mimes' =>
                'Upload a PDF, JPG, JPEG or PNG certificate file.',

            'certificate_file.max' =>
                'The certificate file may not exceed 5 MB.',
        ];
    }

    private function prepareData(
        array $validated,
        Request $request
    ): array {
        $validated['never_expires'] =
            $request->boolean(
                'never_expires'
            );

        $validated['is_featured'] =
            $request->boolean(
                'is_featured'
            );

        $validated['display_order'] =
            $validated['display_order']
            ?? 0;

        if ($validated['never_expires']) {
            $validated['expiry_date'] =
                null;
        }

        return $validated;
    }

    private function formatCertificate(
        Certificate $certificate
    ): array {
        return [
            ...$certificate->toArray(),

            'has_file' =>
                (bool) $certificate
                    ->certificate_file,

            'preview_endpoint' =>
                "/certificates/{$certificate->id}/preview",

            'download_endpoint' =>
                "/certificates/{$certificate->id}/download",
        ];
    }

    private function storedFileExists(
        Certificate $certificate
    ): bool {
        return
            (bool) $certificate
                ->certificate_file &&
            Storage::disk('public')->exists(
                $certificate
                    ->certificate_file
            );
    }

    private function deleteStoredFile(
        ?string $path
    ): void {
        if (
            $path &&
            Storage::disk('public')->exists(
                $path
            )
        ) {
            Storage::disk('public')->delete(
                $path
            );
        }
    }

    private function authorizeOwnership(
        Certificate $certificate,
        int $userId
    ): void {
        abort_unless(
            (int) $certificate->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this certificate.'
        );
    }
}
