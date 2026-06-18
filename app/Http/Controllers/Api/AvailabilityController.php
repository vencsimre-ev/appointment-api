<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Http\Responses\ApiResponse;
use App\Models\Doctor;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AvailabilityController extends Controller
{
    public function store(StoreAvailabilityRequest $request, AvailabilityService $service): JsonResponse
    {
        $availability = $service->create(
            $request->validated()
        );

        return ApiResponse::success(
            'Availability created successfully.',
            new AvailabilityResource($availability),
            Response::HTTP_CREATED
        );
    }

    public function index(Doctor $doctor): JsonResponse
    {
        $availabilities = $doctor
            ->availabilities()
            ->orderBy('starts_at')
            ->paginate(15);

        return ApiResponse::paginated(
            'Availabilities retrieved successfully.',
            AvailabilityResource::collection($availabilities)
        );
    }
}
