<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorCollection;
use App\Http\Resources\DoctorResource;
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

        return (new DoctorCollection($doctors))->response();
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $doctor = Doctor::create($request->validated());

        return (new DoctorResource($doctor))
            ->additional([
                'status' => 'success',
                'message' => 'Doctor created successfully.',
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return (new DoctorResource($doctor))
            ->additional([
                'status' => 'success',
                'message' => 'Doctor retrieved successfully.',
            ])
            ->response();
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor->update($request->validated());

        return (new DoctorResource($doctor->fresh()))
            ->additional([
                'status' => 'success',
                'message' => 'Doctor updated successfully.',
            ])
            ->response();
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        $doctor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Doctor deleted successfully.',
        ]);
    }
}
