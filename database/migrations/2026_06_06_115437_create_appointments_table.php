<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('doctor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->string('status')->default(AppointmentStatus::Pending);

            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['doctor_id', 'start_time', 'end_time']);
            $table->index(['patient_id', 'start_time', 'end_time']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
