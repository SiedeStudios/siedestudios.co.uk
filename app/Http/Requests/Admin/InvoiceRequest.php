<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'client_project_id' => ['nullable', 'integer', Rule::exists('client_projects', 'id')->where('client_id', $this->integer('client_id'))],
            'client_name' => ['required', 'string', 'max:120'],
            'client_email' => ['nullable', 'email', 'max:150'],
            'client_company' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', Rule::in(config('site.currencies'))],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'publish' => ['boolean'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'items.*.description' => 'item description',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'price',
        ];
    }
}
