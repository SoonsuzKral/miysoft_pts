<?php

namespace App\Modules\Ziyaretci\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:191',
            'visitor_company'  => 'nullable|string|max:191',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:191',
            'host_personel_id' => 'nullable|exists:personels,id',
            'document_no_enc'  => 'nullable|string|max:50',
            'document_type'    => 'nullable|string|max:50',
            'visit_date'       => 'required|date',
            'purpose'          => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Ziyaretçi adı zorunludur.',
            'visit_date.required'      => 'Ziyaret tarihi zorunludur.',
            'host_personel_id.exists'  => 'Seçilen personel bulunamadı.',
        ];
    }
}
