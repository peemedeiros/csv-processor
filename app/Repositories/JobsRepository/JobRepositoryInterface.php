<?php

namespace App\Repositories\JobsRepository;

interface JobRepositoryInterface
{
    public function getPendingJobsCount(): int;
    public function getFailedJobsCount(): int;
    public function getOldestPendingJob();
    public function getQueueDistribution(): array;
    public function getRecentFailuresCount(): int;
    public function getLatestFailures(int $limit = 5): array;

}
