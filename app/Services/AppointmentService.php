<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Appointment;
use App\Models\Availability;
use Carbon\Carbon;

class AppointmentService
{
    public function create(array $data): Appointment
    {
        $startTime = Carbon::parse($data['start_time']);
        $slotCount = $data['slot_count'] ?? 1;

        if ($startTime->isPast()) {
            throw new BusinessRuleException('Appointment must be in the future.');
        }

        $availability = $this->checkAvailabilityForAppointment(
            $data['doctor_id'],
            $startTime,
            $slotCount
        );

        $endTime = $this->calculateEndTime(
            $availability,
            $startTime,
            $slotCount
        );

        if ($this->doctorHasOverlappingAppointment($data['doctor_id'], $startTime, $endTime)) {
            throw new BusinessRuleException('This slot is already booked.');
        }

        if ($this->patientHasOverlappingAppointment($data['patient_id'], $startTime, $endTime)) {
            throw new BusinessRuleException('Patient already has an appointment at this time.');
        }

        unset($data['slot_count']);

        $data['end_time'] = $endTime;
        $data['status'] = AppointmentStatus::Pending;

        return Appointment::create($data);
    }

    public function confirm(Appointment $appointment): Appointment
    {
        if ($appointment->status !== AppointmentStatus::Pending) {
            throw new BusinessRuleException('Only pending appointments can be confirmed.');
        }

        $appointment->update([
            'status' => AppointmentStatus::Confirmed,
        ]);

        return $appointment->fresh();
    }

    public function complete(Appointment $appointment): Appointment
    {
        if ($appointment->status !== AppointmentStatus::Confirmed) {
            throw new BusinessRuleException('Only confirmed appointments can be completed.');
        }

        $appointment->update([
            'status' => AppointmentStatus::Completed,
        ]);

        return $appointment->fresh();
    }

    public function cancel(Appointment $appointment, string $reason): Appointment
    {
        if ($appointment->status === AppointmentStatus::Completed) {
            throw new BusinessRuleException('Completed appointments cannot be cancelled.');
        }

        if ($appointment->status === AppointmentStatus::Cancelled) {
            throw new BusinessRuleException('Appointment is already cancelled.');
        }

        if (
            $appointment->status === AppointmentStatus::Confirmed
            && now()->diffInHours($appointment->start_time, false) < 24
        ) {
            throw new BusinessRuleException(
                'Confirmed appointments can only be cancelled at least 24 hours before start time.'
            );
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancellation_reason' => $reason,
        ]);

        return $appointment->fresh();
    }

    private function doctorHasOverlappingAppointment(
        int $doctorId,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        return Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereNot('status', AppointmentStatus::Cancelled)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    private function patientHasOverlappingAppointment(
        int $patientId,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        return Appointment::query()
            ->where('patient_id', $patientId)
            ->whereNot('status', AppointmentStatus::Cancelled)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    private function checkAvailabilityForAppointment(
        int $doctorId,
        Carbon $startTime,
        int $slotCount
    ): Availability {
        $availability = Availability::query()
            ->where('doctor_id', $doctorId)
            ->where('starts_at', '<=', $startTime)
            ->where('ends_at', '>', $startTime)
            ->first();

        if (! $availability) {
            throw new BusinessRuleException('Appointment is outside doctor availability.');
        }

        if (! $this->isValidSlotStartTime($availability, $startTime)) {
            throw new BusinessRuleException('Appointment must start at the beginning of an available slot.');
        }

        $endTime = $this->calculateEndTime($availability, $startTime, $slotCount);

        if ($endTime->greaterThan($availability->ends_at)) {
            throw new BusinessRuleException('Appointment exceeds doctor availability.');
        }

        return $availability;
    }

    private function isValidSlotStartTime(
        Availability $availability, 
        Carbon $startTime
    ): bool {
        $minutesSinceAvailabilityStart = $availability
            ->starts_at
            ->diffInMinutes($startTime);

        return $minutesSinceAvailabilityStart
            % $availability->slot_duration_minutes === 0;
    }

    private function calculateEndTime(
        Availability $availability,
        Carbon $startTime,
        int $slotCount
    ): Carbon {
        return $startTime->copy()->addMinutes(
            $availability->slot_duration_minutes * $slotCount
        );
    }
    
}