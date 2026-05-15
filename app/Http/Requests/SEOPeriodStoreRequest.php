<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SEOPeriodStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            's_e_o_id' => 'required|integer|exists:s_e_o_s,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'date' => 'required|integer|min:1|max:31',
            'is_billing_schedule' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'is_followed_up' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            's_e_o_id.exists' => 'SEO tidak ditemukan.',
            'end_date.after' => 'Tanggal akhir harus lebih besar dari tanggal awal.',
        ];
    }
}
