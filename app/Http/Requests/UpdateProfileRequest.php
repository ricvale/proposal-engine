<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:10000'],
            'rate_card' => ['nullable', 'string', 'max:10000'],
            'past_projects' => ['nullable', 'string', 'max:10000'],
            'default_assumptions' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
