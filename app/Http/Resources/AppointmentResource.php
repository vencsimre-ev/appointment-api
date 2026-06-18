<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AppointmentResource extends JsonResource
{
    private const DATE_FORMAT = 'Y. m. d. H:i:s';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'start_time' => $this->formatDate($this->start_time),
            'end_time' => $this->formatDate($this->end_time),
            'status' => $this->status?->value,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }

    private function formatDate(mixed $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return Carbon::parse($date)->format(self::DATE_FORMAT);
    }
}
