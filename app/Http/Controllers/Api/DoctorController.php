<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Responses\ApiResponse;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DoctorController extends Controller
{
    public function index(): JsonResponse
    {
        $doctors = Doctor::query()
            ->orderBy('name')
            ->paginate(5);

        return ApiResponse::paginated(
            'Doctors retrieved successfully.',
            DoctorResource::collection($doctors)
        );
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $doctor = Doctor::create($request->validated());

        return ApiResponse::success(
            'Doctor created successfully.',
            new DoctorResource($doctor),
            Response::HTTP_CREATED
        );
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return ApiResponse::success(
            'Doctor retrieved successfully.',
            new DoctorResource($doctor)
        );
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor->update($request->validated());

        return ApiResponse::success(
            'Doctor updated successfully.',
            new DoctorResource($doctor->fresh())
        );
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        $doctor->delete();

        return ApiResponse::success('Doctor deleted successfully.');
    }
}
