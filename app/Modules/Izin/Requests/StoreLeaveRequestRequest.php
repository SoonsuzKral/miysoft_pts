<?php

namespace App\Modules\Izin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'personel_id'    => 'required|exists:personels,id',
            'leave_type_id'  => 'required|exists:leave_types,id',
            'start_date'     => 'required|date|after_or_equal:today',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'nullable|string|max:1000',
            'attachment'     => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ];
    }

    public function messages(): array
    {
        return [
            'personel_id.required'   => 'Personel seçimi zorunludur.',
            'leave_type_id.required' => 'İzin türü seçimi zorunludur.',
            'start_date.required'    => 'Başlangıç tarihi zorunludur.',
            'start_date.after_or_equal' => 'Başlangıç tarihi bugün veya sonrası olmalıdır.',
            'end_date.required'      => 'Bitiş tarihi zorunludur.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
        ];
    }
}
