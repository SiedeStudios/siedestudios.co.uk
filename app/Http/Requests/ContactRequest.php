<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public const BUDGETS = ['Under £250', '£250 – £1,000', '£1,000 – £5,000', '£5,000+', 'Not sure yet'];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'discord' => ['nullable', 'string', 'max:60'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_active', true)],
            'budget' => ['nullable', Rule::in(self::BUDGETS)],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'website' => ['nullable', 'max:0'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'body.min' => 'Give me a little more detail — at least a couple of sentences.',
            'website.max' => 'Something went wrong, please try again.',
        ];
    }
}
