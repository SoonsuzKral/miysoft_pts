<?php

namespace App\Modules\Finans\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'personel_id'   => 'required|exists:personels,id',
            'category_id'   => 'required|exists:expense_categories,id',
            'amount'        => 'required|numeric|min:0.01|max:9999999',
            'currency'      => 'nullable|string|size:3',
            'description'   => 'required|string|max:500',
            'expense_date'  => 'required|date|before_or_equal:today',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
        ];
    }

    public function messages(): array
    {
        return [
            'personel_id.required'  => 'Personel seçimi zorunludur.',
            'category_id.required'  => 'Masraf kategorisi zorunludur.',
            'amount.required'       => 'Tutar zorunludur.',
            'description.required'  => 'Açıklama zorunludur.',
            'expense_date.required' => 'Masraf tarihi zorunludur.',
            'expense_date.before_or_equal' => 'Masraf tarihi bugün veya öncesi olmalıdır.',
        ];
    }
}
