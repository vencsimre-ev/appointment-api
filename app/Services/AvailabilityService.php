<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Availability;
use Carbon\Carbon;

class AvailabilityService
{
    public function create(array $data): Availability
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        if ($startsAt->isPast()) {
            throw new BusinessRuleException(
                'Availability must start in the future.'
            );
        }

        if ($startsAt->diffInMinutes($endsAt) < 30) {
            throw new BusinessRuleException(
                'Availability must be at least 30 minutes long.'
            );
        }        

        $isOverlap = $this->hasOverlap( $data['doctor_id'], $startsAt, $endsAt);
        if ($isOverlap) {
            throw new BusinessRuleException(
                'Availability overlaps an existing availability.'
            );
        }

        return Availability::create($data);
    }

    private function hasOverlap(
        int $doctorId,
        Carbon $startsAt,
        Carbon $endsAt
    ): bool {
        return Availability::query()
            ->where('doctor_id', $doctorId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}