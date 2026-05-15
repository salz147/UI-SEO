<?php

namespace App\Http\Requests;

use App\Models\SEO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SEOStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'user_id' => 'required|integer|exists:users,id',
            'domain' => [
                'required',
                'string',
                Rule::unique((new SEO())->getTable(), 'domain'),
            ],
            'month_bill_at' => 'required|date',
            'package' => 'required|string|max:150',
            'bill_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'month_reserved' => 'required|integer|min:1|max:24',
            'starting_month' => 'nullable|date_format:Y-m',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.unique' => 'Domain ini sudah terdaftar dalam sistem.',
            'month_reserved.required' => 'Bulan yang dipesan harus diisi.',
            'month_reserved.min' => 'Minimal pemesanan adalah 1 bulan.',
            'user_id.exists' => 'User tidak ditemukan.',
        ];
    }
}
