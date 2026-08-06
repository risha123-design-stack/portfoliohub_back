<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentPortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    protected function prepareForValidation(): void
    {
        $status = trim(
            (string) $this->status
        );

        $this->merge([
            'title' => trim(
                (string) $this->title
            ),

            'platform' => trim(
                (string) $this->platform
            ),

            'content_type' => trim(
                (string) $this->content_type
            ),

            'category' =>
                $this->category === ''
                    ? null
                    : trim(
                        (string) $this->category
                    ),

            'publish_date' =>
                $status === 'Draft' ||
                $this->publish_date === ''
                    ? null
                    : $this->publish_date,

            'content_url' =>
                $this->content_url === ''
                    ? null
                    : trim(
                        (string) $this->content_url
                    ),

            'thumbnail_url' =>
                $this->thumbnail_url === ''
                    ? null
                    : trim(
                        (string) $this->thumbnail_url
                    ),

            'views' =>
                $this->views === ''
                    ? null
                    : $this->views,

            'likes' =>
                $this->likes === ''
                    ? null
                    : $this->likes,

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'status' => $status,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'platform' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'content_type' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'publish_date' => [
                'required_if:status,Published,Scheduled',
                'prohibited_if:status,Draft',
                'nullable',
                'date',

                Rule::when(
                    in_array(
                        $this->input(
                            'status'
                        ),
                        [
                            'Published',
                            'Archived',
                        ],
                        true
                    ),
                    [
                        'before_or_equal:today',
                    ]
                ),

                Rule::when(
                    $this->input(
                        'status'
                    ) === 'Scheduled',
                    [
                        'after:today',
                    ]
                ),
            ],

            'status' => [
                'required',

                Rule::in([
                    'Published',
                    'Draft',
                    'Scheduled',
                    'Archived',
                ]),
            ],

            'content_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'thumbnail_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'views' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'likes' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' =>
                'Content title is required.',

            'platform.required' =>
                'Platform is required.',

            'content_type.required' =>
                'Content type is required.',

            'publish_date.required_if' =>
                'Publish date is required for published or scheduled content.',

            'publish_date.prohibited_if' =>
                'Draft content cannot have a publish date.',

            'publish_date.before_or_equal' =>
                'Published or archived content cannot have a future publish date.',

            'publish_date.after' =>
                'Scheduled content must have a future publish date.',

            'content_url.url' =>
                'Content URL must be a valid HTTP or HTTPS URL.',

            'thumbnail_url.url' =>
                'Thumbnail URL must be a valid HTTP or HTTPS URL.',

            'views.integer' =>
                'Views must be a whole number.',

            'views.min' =>
                'Views cannot be negative.',

            'likes.integer' =>
                'Likes must be a whole number.',

            'likes.min' =>
                'Likes cannot be negative.',
        ];
    }
}
