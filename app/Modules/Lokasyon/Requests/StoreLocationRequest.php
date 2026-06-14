<?php

namespace App\Modules\Lokasyon\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['location.create', 'location.update']);
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:200',
            'location_type_id'=> 'nullable|exists:location_types,id',
            'address'         => 'nullable|string|max:500',
            'city'            => 'nullable|string|max:100',
            'district'        => 'nullable|string|max:100',
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',
            'radius'          => 'nullable|integer|min:10|max:5000',
            'color'           => 'nullable|string|max:7',
            'description'     => 'nullable|string|max:1000',
            'is_active'       => 'nullable|boolean',
        ];
    }
}
