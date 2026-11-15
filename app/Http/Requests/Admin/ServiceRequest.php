<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $features = $this->input('features');

        if (is_string($features)) {
            $this->merge(['features' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $features))))]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:80', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'icon' => ['required', Rule::in(Service::ICONS)],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'array', 'max:12'],
            'features.*' => ['string', 'max:120'],
            'starting_price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'price_note' => ['nullable', 'string', 'max:80'],
            'is_active' => ['boolean'],
        ];
    }
}
