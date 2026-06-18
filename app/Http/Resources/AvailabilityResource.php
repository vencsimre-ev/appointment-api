<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AvailabilityResource extends JsonResource
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
            'doctor_id' => $this->doctor_id,
            'starts_at' => $this->formatDate($this->starts_at),
            'ends_at' => $this->formatDate($this->ends_at),
            'slot_duration_minutes' => $this->slot_duration_minutes,
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
