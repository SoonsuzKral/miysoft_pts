<?php

namespace App\Modules\Tatil\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:191',
            'date'           => 'required|date',
            'type'           => 'nullable|string|in:national,religious,custom',
            'country_code'   => 'nullable|string|max:5',
            'region'         => 'nullable|string|max:100',
            'is_national'    => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tatil adı zorunludur.',
            'date.required' => 'Tarih zorunludur.',
        ];
    }
}
