<?php

namespace App\Repositories\JobsRepository;

use Illuminate\Support\Facades\DB;

class JobRepositoryQueryBuilder implements JobRepositoryInterface
{
    /**
     * Obtém o número de jobs pendentes
     *
     * @return int
     */
    public function getPendingJobsCount(): int
    {
        return DB::table('jobs')->count();
    }

    /**
     * Obtém o número de jobs que falharam
     *
     * @return int
     */
    public function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    /**
     * Obtém o job mais antigo na fila
     *
     * @return object|null
     */
    public function getOldestPendingJob()
    {
        return DB::table('jobs')
            ->select('id', 'queue', 'created_at')
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Obtém distribuição de jobs por fila
     *
     * @return array
     */
    public function getQueueDistribution(): array
    {
        $queueStats = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->get();

        return $queueStats->toArray();
    }

    /**
     * Obtém contagem de falhas recentes (últimas 24 horas)
     *
     * @return int
     */
    public function getRecentFailuresCount(): int
    {
        return DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Obtém as falhas mais recentes
     *
     * @param int $limit Número máximo de registros
     * @return array
     */
    public function getLatestFailures(int $limit = 5): array
    {
        $latestFailures = DB::table('failed_jobs')
            ->select('id', 'queue', 'failed_at', 'exception')
            ->orderBy('failed_at', 'desc')
            ->limit($limit)
            ->get();

        return $latestFailures->toArray();
    }

}
