<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
