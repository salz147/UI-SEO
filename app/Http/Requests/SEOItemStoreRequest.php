<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SEOItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seo_period_id' => 'required|integer|exists:s_e_o_periods,id',
            'type' => 'required|string|max:100',
            'media_url' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'seo_period_id.exists' => 'SEO Period tidak ditemukan.',
            'keyword.required' => 'Keyword harus diisi.',
            'media_type.in' => 'Tipe media tidak valid.',
            'media_url.url' => 'URL media tidak valid.',
        ];
    }
}
