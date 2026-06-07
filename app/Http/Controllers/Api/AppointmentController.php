<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request, AppointmentService $service): JsonResponse {
        $appointment = $service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment created successfully.',
            'data' => $appointment,
        ], Response::HTTP_CREATED);
    }

    public function confirm(Appointment $appointment, AppointmentService $service): JsonResponse {
        $appointment = $service->confirm($appointment);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment confirmed successfully.',
            'data' => $appointment,
        ]);
    }

    public function complete(Appointment $appointment, AppointmentService $service): JsonResponse {
        $appointment = $service->complete($appointment);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment completed successfully.',
            'data' => $appointment,
        ]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment, AppointmentService $service): JsonResponse {
        $appointment = $service->cancel(
            $appointment,
            $request->validated('cancellation_reason')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment cancelled successfully.',
            'data' => $appointment,
        ]);
    }

    public function patientAppointments(Request $request, Patient $patient): JsonResponse {
        $appointments = $patient
            ->appointments()
            ->when($request->query('status'), function ($query, string $status) {
                $query->where('status', $status);
            })
            ->orderBy('start_time')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Patient appointments retrieved successfully.',
            'data' => $appointments,
        ]);
    }
}
