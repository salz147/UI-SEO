<?php

namespace App\Http\Requests;

use App\Models\SEO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SEOUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('seo');
        
        return [
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'user_id' => 'required|integer|exists:users,id',
            'domain' => [
                'required',
                'string',
                Rule::unique((new SEO())->getTable(), 'domain')->ignore($id),
            ],
            'month_bill_at' => 'required|date',
            'package' => 'required|string|max:150',
            'bill_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.unique' => 'Domain ini sudah terdaftar dalam sistem.',
            'user_id.exists' => 'User tidak ditemukan.',
        ];
    }
}
