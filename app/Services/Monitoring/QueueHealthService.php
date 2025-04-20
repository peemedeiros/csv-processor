<?php

namespace App\Services\Monitoring;

use App\Repositories\JobsRepository\JobRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueueHealthService
{
    public function __construct(protected JobRepositoryInterface $jobRepository)
    {
    }

    /**
     * Obtém o status de saúde do worker
     *
     * @param bool $verbose Se deve incluir informações detalhadas
     * @return array
     */
    public function getWorkerStatus(bool $verbose = false): array
    {
        try {
            // Obtém os dados através do repository
            $pendingCount = $this->jobRepository->getPendingJobsCount();
            $failedCount = $this->jobRepository->getFailedJobsCount();
            $oldestJob = $this->jobRepository->getOldestPendingJob();

            // Determinar o status de saúde
            $status = $this->determineHealthStatus($pendingCount, $failedCount, $oldestJob);

            // Criar a resposta básica
            $response = [
                'status' => $status,
                'stats' => [
                    'pending_jobs' => $pendingCount,
                    'failed_jobs' => $failedCount,
                ],
                'timestamp' => now()->toIso8601String()
            ];

            // Se tiver o job mais antigo, adicionar informações
            if ($oldestJob) {
                $response['stats']['oldest_job'] = [
                    'created_at' => Carbon::createFromTimestamp($oldestJob->created_at)->toIso8601String(),
                    'age_minutes' => Carbon::createFromTimestamp($oldestJob->created_at)->diffInMinutes(),
                    'queue' => $oldestJob->queue
                ];
            }

            // Adicionar detalhes extras se solicitado
            if ($verbose) {
                $response['details'] = $this->getDetailedStats();
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do worker: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Erro ao verificar status do worker',
                'timestamp' => now()->toIso8601String()
            ];
        }
    }

    /**
     * Determina o status de saúde com base nas métricas
     *
     * @param int $pendingCount Número de jobs pendentes
     * @param int $failedCount Número de jobs que falharam
     * @param object|null $oldestJob Informações sobre o job mais antigo
     * @return string 'healthy', 'warning' ou 'critical'
     */
    private function determineHealthStatus(int $pendingCount, int $failedCount, $oldestJob): string
    {
        // Configurações de limites de alerta
        $pendingThreshold = config('queue.health.pending_threshold', 100);
        $failedThreshold = config('queue.health.failed_threshold', 10);
        $oldestJobThresholdMinutes = config('queue.health.oldest_job_minutes', 30);

        // Verificar job mais antigo
        $oldJobWarning = false;
        if ($oldestJob && $pendingCount > 0) {
            $jobAge = Carbon::createFromTimestamp($oldestJob->created_at)->diffInMinutes();
            $oldJobWarning = $jobAge > $oldestJobThresholdMinutes;
        }

        // Determinar o status
        if ($failedCount >= $failedThreshold) {
            return 'critical';
        }

        if ($oldJobWarning || $pendingCount >= $pendingThreshold) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * Obtém estatísticas detalhadas para o modo verbose
     *
     * @return array
     */
    private function getDetailedStats(): array
    {
        return [
            'queue_distribution' => $this->jobRepository->getQueueDistribution(),
            'recent_failures' => [
                'count_24h' => $this->jobRepository->getRecentFailuresCount(),
                'latest' => $this->jobRepository->getLatestFailures(5)
            ],
            'system_info' => $this->getSystemInfo(),
        ];
    }

    /**
     * Obtém informações do sistema
     *
     * @return array
     */
    private function getSystemInfo(): array
    {
        return [
            'queue_driver' => config('queue.default'),
            'worker_timeout' => config('queue.connections.database.retry_after', 90),
            'max_tries' => config('queue.connections.database.max_tries', 3),
            'thresholds' => [
                'pending' => config('queue.health.pending_threshold', 100),
                'failed' => config('queue.health.failed_threshold', 10),
                'oldest_job_minutes' => config('queue.health.oldest_job_minutes', 30),
            ]
        ];
    }
}
