<?php

namespace App\Modules\Vardiya\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'personel_ids'     => 'required|array|min:1',
            'personel_ids.*'   => 'exists:personels,id',
            'shift_id'         => 'required|exists:shifts,id',
            'shift_plan_id'    => 'required|exists:shift_plans,id',
            'dates'            => 'required|array|min:1',
            'dates.*'          => 'date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'personel_ids.required' => 'En az bir personel seçiniz.',
            'shift_id.required'     => 'Vardiya seçimi zorunludur.',
            'dates.required'        => 'En az bir tarih seçiniz.',
        ];
    }
}
