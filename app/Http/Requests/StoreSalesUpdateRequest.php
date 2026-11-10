<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()?->can('updateSales', $event) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $revenue = $this->input('revenue');
        if ($revenue === '' || $revenue === null) {
            $this->merge(['revenue' => null]);
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
                $fail("Kolom :attribute tidak boleh mengandung teks 'Rp' atau 'IDR'. Masukkan angka nominal saja.");
            }
        };

        return [
            'revenue' => ['nullable', $currencyTextCheck, 'numeric', 'min:0'],
            'invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'revenue.numeric' => 'Nilai omzet harus berupa angka murni (tanpa teks Rp atau titik pemisah ribuan).',
            'invoice.max' => 'Ukuran berkas bukti invoice maksimal 5 MB.',
            'invoice.mimes' => 'Format berkas bukti harus berupa PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
