<?php

namespace App\Http\Requests;

use App\Enums\EventType;
use App\Enums\Goal;
use App\Models\Event;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $code = $this->input('code');
        if (is_string($code)) {
            $code = strtoupper(trim($code));
        }

        $mergeData = [
            'code' => $code,
        ];

        // If user is PIC, force branch_id to be user's branch
        if ($this->user()?->hasRole('pic') && $this->user()?->branch_id) {
            $mergeData['branch_id'] = $this->user()->branch_id;
        }

        $this->merge($mergeData);
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
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:events,code',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value === '-' || trim($value) === '') {
                        $fail("Kode event tidak boleh '-' atau kosong.");
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(EventType::class)],
            'event_date' => ['required', 'date'],
            'branch_id' => ['required', 'exists:branches,id'],
            'location' => ['required', 'string', 'max:255'],
            'pic_name' => ['required', 'string', 'max:255'],
            'goal' => ['required', Rule::enum(Goal::class)],
            'theme_product' => ['required', 'string', 'max:255'],
            'target_participants' => ['required', 'integer', 'min:0'],
            'target_leads' => ['required', 'integer', 'min:0'],
            'estimated_relevance_pct' => ['required', 'numeric', 'between:0,100'],
            'estimated_relevance_basis' => ['nullable', 'string', 'max:1000'],
            'proposal_cost' => ['required', $currencyTextCheck, 'numeric', 'min:0'],
            'team_budget' => ['required', $currencyTextCheck, 'numeric', 'min:0'],
            'proposal_doc' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    /**
     * Custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode event sudah terdaftar dalam sistem. Gunakan kode unik lain.',
            'proposal_cost.numeric' => 'Biaya proposal harus berupa angka murni (tanpa teks Rp atau titik pemisah ribuan).',
            'team_budget.numeric' => 'Budget operasional tim harus berupa angka murni (tanpa teks Rp atau titik pemisah ribuan).',
        ];
    }
}
