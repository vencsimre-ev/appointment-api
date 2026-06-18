<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApiResourceResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_is_returned_through_resource(): void
    {
        $response = $this->postJson('/api/patients', [
            'name' => 'Teszt Elek',
            'email' => 'teszt.elek@example.com',
            'phone' => '+36 30 123 4567',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Patient created successfully.')
            ->assertJsonPath('data.name', 'Teszt Elek')
            ->assertJsonPath('data.email', 'teszt.elek@example.com')
            ->assertJsonPath('data.phone', '+36 30 123 4567')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_doctor_availabilities_are_returned_through_resource_collection(): void
    {
        $doctor = Doctor::factory()->create();

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => Carbon::parse('2026-06-20 09:00:00'),
            'ends_at' => Carbon::parse('2026-06-20 12:00:00'),
            'slot_duration_minutes' => 30,
        ]);

        $response = $this->getJson("/api/doctors/{$doctor->id}/availabilities");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Availabilities retrieved successfully.')
            ->assertJsonPath('data.0.doctor_id', $doctor->id)
            ->assertJsonPath('data.0.starts_at', '2026. 06. 20. 09:00:00')
            ->assertJsonPath('data.0.ends_at', '2026. 06. 20. 12:00:00')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'doctor_id',
                        'starts_at',
                        'ends_at',
                        'slot_duration_minutes',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_appointment_and_patient_appointments_are_returned_through_resources(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $startTime = Carbon::parse('2026-06-21 09:00:00');

        Availability::factory()->create([
            'doctor_id' => $doctor->id,
            'starts_at' => Carbon::parse('2026-06-21 09:00:00'),
            'ends_at' => Carbon::parse('2026-06-21 12:00:00'),
            'slot_duration_minutes' => 30,
        ]);

        $createdAppointment = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'start_time' => $startTime->toDateTimeString(),
        ]);

        $createdAppointment->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Appointment created successfully.')
            ->assertJsonPath('data.patient_id', $patient->id)
            ->assertJsonPath('data.doctor_id', $doctor->id)
            ->assertJsonPath('data.start_time', '2026. 06. 21. 09:00:00')
            ->assertJsonPath('data.end_time', '2026. 06. 21. 09:30:00')
            ->assertJsonPath('data.status', 'pending');

        $appointment = Appointment::query()->firstOrFail();

        $appointments = $this->getJson("/api/patients/{$patient->id}/appointments");

        $appointments->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Patient appointments retrieved successfully.')
            ->assertJsonPath('data.0.id', $appointment->id)
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'patient_id',
                        'doctor_id',
                        'start_time',
                        'end_time',
                        'status',
                        'cancellation_reason',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }
}
