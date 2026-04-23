<?php

namespace App\Http\Requests\AdminCMS;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize input before validation. Multipart form-data sends booleans as strings
     * like "true"/"false"/"on"/"" which Laravel's `boolean` rule does NOT accept.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('featured')) {
            $v = $this->input('featured');
            $truthy = [true, 1, '1', 'true', 'on', 'yes'];
            $falsy = [false, 0, '0', 'false', 'off', 'no', '', null];
            if (in_array($v, $truthy, true)) {
                $this->merge(['featured' => true]);
            } elseif (in_array($v, $falsy, true)) {
                $this->merge(['featured' => false]);
            }
        }
    }

    /**
     * Allowed media MIME types accepted for project gallery uploads.
     * Images: jpg, jpeg, png, webp, gif, avif, heic, heif
     * Videos: mp4, webm, ogg, mov, m4v, mkv, avi, 3gp
     */
    public const ALLOWED_MIMETYPES = [
        // images
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
        'image/heic',
        'image/heif',
        // videos
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
        'video/x-m4v',
        'video/x-matroska',
        'video/x-msvideo',
        'video/3gpp',
    ];

    /** Max upload size in kilobytes (50 MB) */
    public const MAX_UPLOAD_KB = 51200;

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,id',

            // New uploads — accept any image/* or video/* file (broader than a fixed allowlist
            // because browsers can report uncommon MIMEs for newer codecs / .mkv / .mov / .heic).
            'images' => 'nullable|array',
            'images.*' => 'array',
            'images.*.file' => [
                'nullable',
                'file',
                'max:'.self::MAX_UPLOAD_KB,
                function (string $attribute, $value, \Closure $fail) {
                    if (! $value) {
                        return;
                    }
                    $mime = method_exists($value, 'getMimeType') ? $value->getMimeType() : null;
                    if (! $mime || ! preg_match('#^(image|video)/#', $mime)) {
                        $fail("The {$attribute} must be an image or a video file (got mime: ".($mime ?: 'unknown').").");
                    }
                },
            ],
            'images.*.alt_text' => 'nullable|string|max:255',
            'images.*.title' => 'nullable|string|max:255',
            'images.*.sort_order' => 'nullable|integer',

            // Existing images metadata updates (kept on update; ignored on create)
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'array',
            'existing_images.*.id' => 'required|integer|exists:project_images,id',
            'existing_images.*.sort_order' => 'nullable|integer',
            'existing_images.*.alt_text' => 'nullable|string|max:255',
            'existing_images.*.title' => 'nullable|string|max:255',

            // IDs of project_images to delete on update
            'removed_image_ids' => 'nullable|array',
            'removed_image_ids.*' => 'integer|exists:project_images,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category is invalid.',
            'images.*.file.mimetypes' => 'Each upload must be an image (jpg, jpeg, png, webp, gif, avif, heic) or a video (mp4, webm, mov, m4v, mkv, avi, 3gp, ogg).',
            'images.*.file.max' => 'Each upload must not exceed 50 MB.',
            'images.*.file.file' => 'Each upload must be a valid file.',
            'existing_images.*.id.exists' => 'One of the existing images is invalid.',
            'removed_image_ids.*.exists' => 'One of the images to remove does not exist.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Log validation failures so we can debug from the server side
        \Log::warning('ProjectRequest validation failed', [
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'errors' => $validator->errors()->toArray(),
            'has_files' => $this->allFiles(),
            'input_keys' => array_keys($this->all()),
        ]);

        throw new ValidationException($validator, Response::Validation($validator->errors(), 'Validation Error'));
    }
}
