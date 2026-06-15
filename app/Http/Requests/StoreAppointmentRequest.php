<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => ['required', Rule::exists('patients', 'id')],
            'doctor_id' => ['required', Rule::exists('doctors', 'id')],
            'start_time' => ['required', 'date'],
            'slot_count' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
