<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PatientResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
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
