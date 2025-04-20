<?php

namespace App\Http\Controllers;

use App\Services\Monitoring\QueueHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueHealthController extends Controller
{
    public function __construct(protected QueueHealthService $queueHealthService)
    {
    }

    public function check(Request $request): JsonResponse
    {
        $verbose = $request->has('verbose');

        $healthStatus = $this->queueHealthService->getWorkerStatus($verbose);

        return response()->json($healthStatus)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('X-Worker-Health-Status', $healthStatus['status']);
    }
}
