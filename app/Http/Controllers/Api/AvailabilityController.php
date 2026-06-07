<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Models\Doctor;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AvailabilityController extends Controller
{
    public function store(StoreAvailabilityRequest $request, AvailabilityService $service): JsonResponse {

        $availability = $service->create(
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Availability created successfully.',
            'data' => $availability,
        ], Response::HTTP_CREATED);

    }

    public function index( Doctor $doctor ): JsonResponse {
        $availabilities = $doctor
            ->availabilities()
            ->orderBy('starts_at')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $availabilities,
        ]);
    }
}
