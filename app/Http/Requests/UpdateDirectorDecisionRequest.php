<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDirectorDecisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('recordDecision', $this->route('event'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'director_decision' => ['required', 'in:layak,perlu_evaluasi,tidak_layak'],
            'director_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'director_decision.required' => 'Keputusan Direksi wajib dipilih.',
            'director_decision.in' => 'Keputusan Direksi harus berupa Layak, Perlu Evaluasi, atau Tidak Layak.',
        ];
    }
}
