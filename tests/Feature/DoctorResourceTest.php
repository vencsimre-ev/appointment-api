<?php

namespace Tests\Feature;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DoctorResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctors_are_returned_through_collection(): void
    {
        Doctor::factory()->create([
            'name' => 'Dr. Beta',
            'email' => 'beta@example.com',
            'specialization' => 'kardiológus',
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Alpha',
            'email' => 'alpha@example.com',
            'specialization' => 'bőrgyógyász',
            'created_at' => Carbon::parse('2026-06-12 10:15:30'),
            'updated_at' => Carbon::parse('2026-06-12 11:45:00'),
        ]);

        $response = $this->getJson('/api/doctors');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Doctors retrieved successfully.')
            ->assertJsonPath('data.0.name', 'Dr. Alpha')
            ->assertJsonPath('data.0.email', 'alpha@example.com')
            ->assertJsonPath('data.0.specialization', 'bőrgyógyász')
            ->assertJsonPath('data.0.created_at', '2026. 06. 12. 10:15:30')
            ->assertJsonPath('data.0.updated_at', '2026. 06. 12. 11:45:00')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'specialization',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_doctors_are_paginated_by_five_per_page(): void
    {
        Doctor::factory()->count(6)->create();

        $response = $this->getJson('/api/doctors');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 6);
    }

    public function test_doctor_is_returned_through_resource(): void
    {
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Resource',
            'email' => 'resource@example.com',
            'specialization' => 'endokrinológus',
            'created_at' => Carbon::parse('2026-06-12 08:00:00'),
            'updated_at' => Carbon::parse('2026-06-12 09:30:15'),
        ]);

        $response = $this->getJson("/api/doctors/{$doctor->id}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Doctor retrieved successfully.')
            ->assertJsonPath('data.id', $doctor->id)
            ->assertJsonPath('data.name', 'Dr. Resource')
            ->assertJsonPath('data.email', 'resource@example.com')
            ->assertJsonPath('data.specialization', 'endokrinológus')
            ->assertJsonPath('data.created_at', '2026. 06. 12. 08:00:00')
            ->assertJsonPath('data.updated_at', '2026. 06. 12. 09:30:15');
    }
}
