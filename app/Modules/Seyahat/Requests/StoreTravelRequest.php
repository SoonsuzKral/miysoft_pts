<?php

namespace App\Modules\Seyahat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTravelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'personel_id'        => 'required|exists:personels,id',
            'destination'        => 'required|string|max:500',
            'departure_date'     => 'required|date|after_or_equal:today',
            'return_date'        => 'required|date|after_or_equal:departure_date',
            'purpose'            => 'nullable|string|max:2000',
            'accommodation'      => 'nullable|string|max:500',
            'transportation_mode'=> 'nullable|string|max:50',
            'estimated_cost'     => 'nullable|numeric|min:0|max:9999999',
            'currency'           => 'nullable|string|size:3',
        ];

        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $rules['departure_date'] = 'required|date';
            $rules['return_date'] = 'required|date|after_or_equal:departure_date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'personel_id.required'    => 'Personel seçimi zorunludur.',
            'destination.required'    => 'Gidilecek yer zorunludur.',
            'departure_date.required' => 'Gidiş tarihi zorunludur.',
            'return_date.required'    => 'Dönüş tarihi zorunludur.',
            'return_date.after_or_equal' => 'Dönüş tarihi, gidiş tarihinden sonra olmalıdır.',
        ];
    }
}
