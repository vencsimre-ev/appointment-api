<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request, AppointmentService $service): JsonResponse
    {
        $appointment = $service->create($request->validated());

        return ApiResponse::success(
            'Appointment created successfully.',
            new AppointmentResource($appointment),
            Response::HTTP_CREATED
        );
    }

    public function confirm(Appointment $appointment, AppointmentService $service): JsonResponse
    {
        $appointment = $service->confirm($appointment);

        return ApiResponse::success(
            'Appointment confirmed successfully.',
            new AppointmentResource($appointment)
        );
    }

    public function complete(Appointment $appointment, AppointmentService $service): JsonResponse
    {
        $appointment = $service->complete($appointment);

        return ApiResponse::success(
            'Appointment completed successfully.',
            new AppointmentResource($appointment)
        );
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment, AppointmentService $service): JsonResponse
    {
        $appointment = $service->cancel(
            $appointment,
            $request->validated('cancellation_reason')
        );

        return ApiResponse::success(
            'Appointment cancelled successfully.',
            new AppointmentResource($appointment)
        );
    }

    public function patientAppointments(Request $request, Patient $patient): JsonResponse
    {
        $status = $request->query('status');

        $query = $patient->appointments();

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query
            ->orderBy('start_time')
            ->paginate(15);

        return ApiResponse::paginated(
            'Patient appointments retrieved successfully.',
            AppointmentResource::collection($appointments)
        );
    }
}
