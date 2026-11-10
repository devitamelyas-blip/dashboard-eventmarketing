<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSurveyRequest extends FormRequest
{
    /**
     * Public survey is accessible to participants.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Default organization to 'Pribadi/Umum' if empty
        if (!$this->filled('organization')) {
            $this->merge(['organization' => 'Pribadi/Umum']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'unaided_brand_recall' => ['nullable', 'string', 'max:255'],
            'audience_relevance' => ['required', 'boolean'],
            'is_customer_or_partner' => ['required', 'boolean'],
            'relationship_score' => ['required', 'integer', 'between:1,5'],
            'awareness_before' => ['required', 'integer', 'between:1,5'],
            'awareness_after' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
