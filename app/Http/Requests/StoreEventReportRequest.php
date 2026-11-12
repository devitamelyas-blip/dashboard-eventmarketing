<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()?->can('updateReport', $event) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $cost = $this->input('actual_team_cost');
        if ($cost === '' || $cost === null) {
            $this->merge(['actual_team_cost' => null]);
        }

        $participants = $this->input('actual_participants');
        if ($participants === '' || $participants === null) {
            $this->merge(['actual_participants' => null]);
        }

        $leads = $this->input('valid_leads');
        if ($leads === '' || $leads === null) {
            $this->merge(['valid_leads' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currencyTextCheck = function (string $attribute, mixed $value, Closure $fail) {
            if (is_string($value) && preg_match('/(?:rp|idr)/i', $value)) {
                $fail("Kolom :attribute tidak boleh mengandung teks 'Rp' atau 'IDR'. Masukkan angka murni saja.");
            }
        };

        return [
            'actual_participants' => ['nullable', 'integer', 'min:0'],
            'valid_leads' => ['nullable', 'integer', 'min:0'],
            'actual_team_cost' => ['nullable', $currencyTextCheck, 'numeric', 'min:0'],
            'attendance_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'documentation_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_team_cost.numeric' => 'Biaya aktual tim harus berupa angka murni (tanpa teks Rp atau titik pemisah ribuan).',
        ];
    }
}
