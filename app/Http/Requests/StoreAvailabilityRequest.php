<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvailabilityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                Rule::exists('doctors', 'id'),
            ],

            'starts_at' => [
                'required',
                'date',
            ],

            'ends_at' => [
                'required',
                'date',
                'after:starts_at', // nem lehet kisebb mint a start
            ],

            'slot_duration_minutes' => [
                'required',
                'integer',
                'min:30', // nem lehet kisebb mint 30 perc
            ],
        ];
    }
}
