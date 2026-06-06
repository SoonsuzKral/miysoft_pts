<?php

namespace App\Modules\Arac\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsageLogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle_id'  => 'required|exists:vehicles,id',
            'personel_id' => 'nullable|exists:personels,id',
            'start_km'    => 'nullable|numeric|min:0|max:9999999',
            'end_km'      => 'nullable|numeric|min:0|max:9999999',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'nullable',
            'end_time'    => 'nullable',
            'origin'      => 'nullable|string|max:500',
            'destination' => 'nullable|string|max:500',
            'purpose'     => 'nullable|string|max:2000',
            'notes'       => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Araç seçimi zorunludur.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
        ];
    }
}
