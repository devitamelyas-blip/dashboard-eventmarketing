<?php

namespace App\Http\Requests;

use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('event'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verification_status' => ['required', new Enum(VerificationStatus::class)],
            'verification_note' => [
                'required_if:verification_status,' . VerificationStatus::PERLU_KOREKSI->value,
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'verification_status.required' => 'Status verifikasi wajib dipilih.',
            'verification_note.required_if' => 'Alasan wajib dicantumkan jika meminta koreksi.',
        ];
    }
}
