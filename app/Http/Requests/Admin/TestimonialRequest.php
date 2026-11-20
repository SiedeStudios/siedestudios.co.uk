<?php

namespace App\Http\Requests\Admin;

use App\Enums\TestimonialStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $editing = $this->route('testimonial') !== null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'avatar' => ['nullable', 'image', 'max:4096'],
            'remove_avatar' => ['boolean'],
            'body' => [$editing ? 'nullable' : 'prohibited', 'string', 'max:1500'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'status' => [$editing ? 'required' : 'nullable', Rule::enum(TestimonialStatus::class)],
            'is_featured' => ['boolean'],
        ];
    }
}
