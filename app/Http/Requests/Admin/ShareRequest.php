<?php

namespace App\Http\Requests\Admin;

use App\Models\Share;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareRequest extends FormRequest
{
    public const EXPIRY = ['never' => null, '1h' => '1 hour', '1d' => '1 day', '1w' => '1 week', '1m' => '1 month'];

    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:500000'],
            'language' => ['required', Rule::in(Share::LANGUAGES)],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'remove_password' => ['boolean'],
            'burn_after_read' => ['boolean'],
            'expiry' => ['nullable', Rule::in(array_keys(self::EXPIRY))],
        ];
    }
}
