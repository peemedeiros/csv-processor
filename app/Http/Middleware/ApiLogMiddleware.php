<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;

        $requestData = $request->except(['password', 'password_confirmation']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $fileInfo['file'] = [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];

            $requestData['file_info'] = $fileInfo;
        }

        $responseData = json_decode($response->getContent(), true);

        $jsonEncoded = json_encode($responseData);

        // Limitar o tamanho dos dados de resposta
        if (strlen($jsonEncoded) > 65000) {
            $responseData = [
                'message' => 'Response data too large to log',
                'size' => strlen($jsonEncoded)
            ];
        }

        try {
            ApiLog::create([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => Auth::id(),
                'request_data' => $requestData,
                'response_data' => $responseData,
                'status_code' => $response->getStatusCode(),
                'execution_time' => $executionTime,
            ]);
        } catch (\Exception $e) {
            \Log::error("Falha ao registrar log da API: {$e->getMessage()}");
        }

        return $response;
    }
}
