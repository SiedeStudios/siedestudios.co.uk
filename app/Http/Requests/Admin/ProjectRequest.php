<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');

        if (is_string($tags)) {
            $this->merge(['tags' => array_values(array_filter(array_map('trim', explode(',', $tags))))]);
        }
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('projects', 'slug')->ignore($project)],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:20000'],
            'accent_mode' => ['nullable', 'in:auto,custom,none'],
            'accent_color' => ['nullable', 'required_if:accent_mode,custom', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'results' => ['nullable', 'array', 'max:4'],
            'results.*.value' => ['nullable', 'string', 'max:16'],
            'results.*.label' => ['nullable', 'required_with:results.*.value', 'string', 'max:60'],
            'chapter_images' => ['nullable', 'array', 'max:30'],
            'chapter_images.*' => ['nullable', 'integer'],
            'client' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'string', 'max:16'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:40'],
            'site_url' => ['nullable', 'url', 'max:255'],
            'discord_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'store_url' => ['nullable', 'url', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'is_featured' => ['boolean'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'cover' => ['nullable', 'image', 'max:8192'],
            'remove_cover' => ['boolean'],
        ];
    }
}
