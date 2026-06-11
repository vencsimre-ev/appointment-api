<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $doctor1 = Doctor::create([
            'name' => 'Dr. House',
            'email' => 'house@example.com',
            'specialization' => 'Diagnosztika',
        ]);

        $doctor2 = Doctor::create([
            'name' => 'Dr. Strange',
            'email' => 'strange@example.com',
            'specialization' => 'Sebészet',
        ]);

        Doctor::factory()->count(13)->create();

        $patient1 = Patient::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+36111111',
        ]);

        Patient::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+36222222',
        ]);

        Patient::create([
            'name' => 'Jim Doe',
            'email' => 'jim@example.com',
            'phone' => '+36333333',
        ]);

        Patient::factory()->count(12)->create();

        Availability::create([
            'doctor_id' => $doctor1->id,
            'starts_at' => now()->addDays(1)->setTime(9, 0),
            'ends_at' => now()->addDays(1)->setTime(12, 0),
            'slot_duration_minutes' => 30,
        ]);

        Availability::create([
            'doctor_id' => $doctor1->id,
            'starts_at' => now()->addDays(2)->setTime(13, 0),
            'ends_at' => now()->addDays(2)->setTime(16, 0),
            'slot_duration_minutes' => 30,
        ]);

        Availability::create([
            'doctor_id' => $doctor2->id,
            'starts_at' => now()->addDays(1)->setTime(8, 0),
            'ends_at' => now()->addDays(1)->setTime(11, 0),
            'slot_duration_minutes' => 60,
        ]);

        Appointment::create([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'start_time' => now()->addDays(1)->setTime(9, 0),
            'end_time' => now()->addDays(1)->setTime(9, 30),
            'status' => AppointmentStatus::Confirmed,
        ]);
    }
}
