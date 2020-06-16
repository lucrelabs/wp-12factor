<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Component\Job\Database;

use WPStaging\Component\Job\AbstractJob;
use WPStaging\Component\Job\Dto\SnapshotRestoreDto;
use WPStaging\Component\Job\Dto\StepsDto;
use WPStaging\Service\Adapter\Database;

// TODO RPoC
// TODO DRY Snapshot Jobs
class JobRestoreSnapshot extends AbstractJob
{

    const TRANSIENT_EXPIRATION_IN_SECONDS = 300;
    const TRANSIENT_PREFIX = 'wpstg_snapshot_restore_';

    /** @var SnapshotRestoreDto */
    private $dto;

    /** @var int */
    private $currentTask = 0;

    private $tasks = [
        JobTempDatabase::class,
        JobRenameTables::class,
    ];

    public function __construct(SnapshotRestoreDto $dto, StepsDto $steps = null)
    {
        $this->dto = $dto;
        parent::__construct($steps);
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        if ($this->dto->isReset()) {
            $this->currentTask = 0;
            $this->steps->setCurrent(0);
            return;
        }

        $transient = get_transient($this->getTransientKey());

        if (!isset($transient['currentTask'], $transient['steps'])) {
            $this->currentTask = 0;
            $this->steps->setCurrent(0);
            return;
        }

        $this->currentTask = (int) $transient['currentTask'];
        $this->steps->hydrate($transient['steps']);

        if ($this->steps->isFinished()) {
            $this->currentTask++;
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $job = $this->getCurrentJob();
        if ($job) {
            return $this->getJobResponse($job->execute());
        }

        return $this->generateResponse();
    }

    /**
     * @return AbstractJob|null
     */
    protected function getCurrentJob()
    {
        if (!isset($this->tasks[$this->currentTask])) {
            return null;
        }

        $prefix = $this->dto->getPrefix();
        $jobName = $this->tasks[$this->currentTask];

        if (JobRenameTables::class === $jobName) {
            return new JobRenameTables(JobTempDatabase::TMP_PREFIX, (new Database)->getPrefix(), $this->steps);
        }

        return new $jobName($prefix, $this->steps);
    }

    /**
     * @return string
     */
    private function getTransientKey()
    {
        return self::TRANSIENT_PREFIX . $this->dto->getPrefix();
    }

    /**
     * @param object $response
     *
     * @return object
     */
    private function getJobResponse($response)
    {
        if ($this->isFinished()) {
            delete_transient($this->getTransientKey());
            return $response;
        }

        set_transient(
            $this->getTransientKey(),
            [
                'job' => $this->generateCurrentJob(),
                'currentTask' => $this->currentTask,
                'steps' => $this->steps->toArray(),
            ],
            self::TRANSIENT_EXPIRATION_IN_SECONDS
        );

        $response->job_done = false;
        return $response;
    }

    private function isFinished()
    {
        return $this->steps->isFinished() && count($this->tasks) <= $this->currentTask + 1;
    }
}
