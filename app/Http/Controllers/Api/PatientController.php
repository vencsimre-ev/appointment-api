<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Http\Responses\ApiResponse;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function index(): JsonResponse
    {
        $patients = Patient::query()
            ->orderBy('name')
            ->paginate(15);

        return ApiResponse::paginated(
            'Patients retrieved successfully.',
            PatientResource::collection($patients)
        );
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->validated());

        return ApiResponse::success(
            'Patient created successfully.',
            new PatientResource($patient),
            Response::HTTP_CREATED
        );
    }

    public function show(Patient $patient): JsonResponse
    {
        return ApiResponse::success(
            'Patient retrieved successfully.',
            new PatientResource($patient)
        );
    }

    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());

        return ApiResponse::success(
            'Patient updated successfully.',
            new PatientResource($patient->fresh())
        );
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return ApiResponse::success('Patient deleted successfully.');
    }
}
