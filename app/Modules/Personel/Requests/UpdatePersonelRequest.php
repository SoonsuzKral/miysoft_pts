<?php

namespace App\Modules\Personel\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;
        $personelId = $this->route('personel')?->id;

        return [
            'first_name'        => 'sometimes|required|string|max:100',
            'last_name'         => 'sometimes|required|string|max:100',
            'email'             => [
                'nullable',
                'email',
                'max:191',
                Rule::unique('personels')
                    ->where('company_id', $companyId)
                    ->ignore($personelId),
            ],
            'phone'             => ['nullable', 'string', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'national_id'       => 'nullable|string|max:20',
            'birth_date'        => 'nullable|date|before:today',
            'gender'            => 'nullable|in:M,F,other',
            'blood_type'        => 'nullable|string|max:5',
            'position_id'       => 'nullable|exists:positions,id',
            'department_id'     => 'nullable|exists:departments,id',
            'salary'            => 'nullable|numeric|min:0|max:9999999',
            'currency'          => 'nullable|string|size:3',
            'hire_date'         => 'nullable|date',
            'termination_date'  => 'nullable|date|after_or_equal:hire_date',
            'status'            => 'nullable|in:active,terminated,on_leave,suspended',
            'attributes'        => 'nullable|array',
            'is_active'         => 'nullable|boolean',
        ];
    }
}
