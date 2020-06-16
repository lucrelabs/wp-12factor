<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Component\Job\Database;

use WPStaging\Command\Database\Snapshot\SnapshotDto;
use WPStaging\Command\Database\SnapshotFactory;
use WPStaging\Component\Job\AbstractJob;
use WPStaging\Component\Job\Dto\SnapshotCreateDto;
use WPStaging\Component\Job\Dto\StepsDto;
use WPStaging\Manager\Database\TableDto;
use WPStaging\Manager\Database\TableManager;
use WPStaging\Service\Collection\Collection;
use WPStaging\Utils\Logger;

// TODO RPoC
// TODO DRY Snapshot Jobs
class JobCreateSnapshot extends AbstractJob
{

    const TRANSIENT_PREFIX = 'wpstg_snapshot_';

    /** @var SnapshotCreateDto */
    private $dto;

    /** @var Collection|TableDto[] */
    private $tables;

    public function __construct(SnapshotCreateDto $dto, StepsDto $steps = null)
    {
        $this->dto = $dto;
        $this->tables = (new TableManager)->findStartsWith();
        parent::__construct($steps);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {

        $dto = new SnapshotDto;
        $dto->setName($this->dto->getName());
        $dto->setNotes($this->dto->getNotes());
        $dto->setTargetPrefix($this->dto->getJobDbPrefix());
        $dto->setStep($this->steps->getCurrent());

        $handler = SnapshotFactory::make($dto, SnapshotFactory::CREATE_SNAPSHOT);
        $handler->handle();

        $this->logger->log(Logger::TYPE_INFO, sprintf(
            'Created snapshot with prefix %s of table %s - %d/%d',
            $this->dto->getJobDbPrefix(),
            $this->getCurrentTableName(),
            $this->steps->getCurrent() + 1,
            $this->steps->getTotal()
        ));

        return $this->getResponse();
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->steps->setTotal($this->tables->count());

        if (null !== $this->steps->getCurrent()) {
            return;
        }

        $transient = get_transient($this->getTransientKey());

        if (!$transient || !isset($transient['current'])) {
            $this->steps->setCurrent(0);
            return;
        }

        $this->steps->setCurrent((int)$transient['current']);
    }

    /**
     * @return string
     */
    private function getCurrentTableName()
    {
        $tables = $this->tables->toArray();
        if (!isset($tables[$this->steps->getCurrent()])) {
            return '';
        }

        /** @var TableDto $table */
        $table = $tables[$this->steps->getCurrent()];
        return $table->getName();
    }

    /**
     * @return string
     */
    private function getTransientKey()
    {
        return self::TRANSIENT_PREFIX . $this->dto->getJobDbPrefix();
    }

    private function getResponse()
    {
        $response = $this->generateResponse();

        if ($this->steps->isFinished()) {
            delete_transient($this->getTransientKey());
            return $response;
        }

        set_transient(
            $this->getTransientKey(),
            [
                'job' => $this->generateCurrentJob(),
                'total' => $this->steps->getTotal(),
                'current' => $this->steps->getCurrent(),
            ],
            HOUR_IN_SECONDS
        );

        return $response;
    }
}
