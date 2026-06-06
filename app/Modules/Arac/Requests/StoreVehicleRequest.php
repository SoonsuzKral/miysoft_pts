<?php

namespace App\Modules\Arac\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'plate'                 => 'required|string|max:20',
            'brand'                 => 'required|string|max:100',
            'model'                 => 'required|string|max:100',
            'year'                  => 'nullable|integer|min:1900|max:2099',
            'color'                 => 'nullable|string|max:30',
            'vin'                   => 'nullable|string|max:50',
            'engine_type'           => 'nullable|string|max:30',
            'fuel_type'             => 'nullable|string|max:30',
            'acquisition_date'      => 'nullable|date',
            'acquisition_cost'      => 'nullable|numeric|min:0|max:99999999',
            'status'                => 'nullable|string|in:active,maintenance,out_of_service',
            'assigned_personel_id'  => 'nullable|exists:personels,id',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date|after_or_equal:last_maintenance_date',
            'current_km'            => 'nullable|numeric|min:0|max:9999999',
            'last_maintenance_km'   => 'nullable|numeric|min:0|max:9999999',
            'engine_capacity'       => 'nullable|numeric|min:0|max:99',
            'fuel_consumption_avg'  => 'nullable|numeric|min:0|max:99',
            'fuel_tank_capacity'    => 'nullable|numeric|min:0|max:9999',
            'insurance_date'        => 'nullable|date',
            'traffic_date'          => 'nullable|date',
            'examination_date'      => 'nullable|date',
            'notes'                 => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'plate.required' => 'Plaka zorunludur.',
            'brand.required' => 'Marka zorunludur.',
            'model.required' => 'Model zorunludur.',
        ];
    }
}
