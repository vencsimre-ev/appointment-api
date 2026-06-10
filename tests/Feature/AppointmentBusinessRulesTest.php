<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_must_start_in_the_future(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->postJson('/api/availabilities', [
            'doctor_id' => $doctor->id,
            'starts_at' => now()->subDay()->setTime(9, 0)->toDateTimeString(),
            'ends_at' => now()->subDay()->setTime(12, 0)->toDateTimeString(),
            'slot_duration_minutes' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Availability must start in the future.',
            ]);
    }

    public function test_doctor_availabilities_cannot_overlap(): void
    {
        $doctor = Doctor::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(2)->setTime(9, 0),
            'ends_at' => now()->addDays(2)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        $response = $this->postJson('/api/availabilities', [
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'ends_at' => now()->addDays(2)->setTime(13, 0)->toDateTimeString(),
            'slot_duration_minutes' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Availability overlaps an existing availability.',
            ]);
    }

    public function test_appointment_must_be_in_the_future(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->subDay()->setTime(10, 0)->toDateTimeString(),
            'end_time' => now()->subDay()->setTime(10, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Appointment must be in the future.',
            ]);
    }

    public function test_cannot_book_already_booked_slot(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $anotherPatient = Patient::factory()->create();

        $startTime = now()->addDays(3)->setTime(9, 0);
        $endTime = $startTime->copy()->addMinutes(30);

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => AppointmentStatus::Pending,
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $anotherPatient->id,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'This slot is already booked.',
            ]);
    }

    public function test_confirmed_appointment_cannot_be_cancelled_within_24_hours(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->addHours(12),
            'end_time' => now()->addHours(12)->addMinutes(30),
            'status' => AppointmentStatus::Confirmed,
        ]);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => 'Nem megfelelő az időpont.',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Confirmed appointments can only be cancelled at least 24 hours before start time.',
            ]);
    }

    public function test_appointment_cannot_start_before_doctor_availability(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->addDays(3)->setTime(8, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Appointment is outside doctor availability.',
            ]);
    }

    public function test_appointment_cannot_start_in_the_middle_of_a_slot(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->addDays(3)->setTime(9, 20)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Appointment must start at the beginning of an available slot.',
            ]);
    }

    public function test_appointment_cannot_exceed_doctor_availability(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->addDays(3)->setTime(11, 30)->toDateTimeString(),
            'slot_count' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Appointment exceeds doctor availability.',
            ]);
    }

    public function test_appointment_cannot_overlap_another_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $anotherPatient = Patient::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => now()->addDays(3)->setTime(10, 0),
            'end_time' => now()->addDays(3)->setTime(11, 0),
            'status' => AppointmentStatus::Pending,
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $anotherPatient->id,
            'start_time' => now()->addDays(3)->setTime(10, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'This slot is already booked.',
            ]);
    }
}
