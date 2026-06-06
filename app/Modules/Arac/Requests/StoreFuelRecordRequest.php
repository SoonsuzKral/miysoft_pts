<?php

namespace App\Modules\Arac\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelRecordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vehicle_id'  => 'required|exists:vehicles,id',
            'date'        => 'required|date',
            'km'          => 'nullable|numeric|min:0|max:9999999',
            'liters'      => 'required|numeric|min:0|max:99999',
            'unit_price'  => 'required|numeric|min:0|max:999',
            'total_cost'  => 'required|numeric|min:0|max:9999999',
            'fuel_type'   => 'nullable|string|max:20',
            'station'     => 'nullable|string|max:200',
            'full_refill' => 'boolean',
            'notes'       => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Araç seçimi zorunludur.',
            'date.required'       => 'Tarih zorunludur.',
            'liters.required'     => 'Litre zorunludur.',
            'unit_price.required' => 'Birim fiyat zorunludur.',
            'total_cost.required' => 'Toplam tutar zorunludur.',
        ];
    }
}
