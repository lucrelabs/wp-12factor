<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Component\Job\Database;

use WPStaging\Command\Database\Snapshot\SnapshotDto;
use WPStaging\Command\Database\SnapshotFactory;
use WPStaging\Component\Job\AbstractJob;
use WPStaging\Component\Job\Dto\StepsDto;
use WPStaging\Manager\Database\TableManager;
use WPStaging\Utils\Logger;

// TODO RPoC
// TODO DRY Snapshot Jobs
class JobTempDatabase extends AbstractJob
{

    const TMP_PREFIX = 'wpstgtmp_';

    /** @var string */
    private $prefix;

    /**
     * @param string $prefix
     * @param StepsDto|null $steps
     */
    public function __construct($prefix, StepsDto $steps = null)
    {
        $this->prefix = $prefix;
        parent::__construct($steps);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $dto = new SnapshotDto;
        $dto->setSourcePrefix($this->prefix);
        $dto->setTargetPrefix(self::TMP_PREFIX);
        $dto->setStep($this->steps->getCurrent());
        $dto->setIsSaveRecords(false);

        $handler = SnapshotFactory::make($dto, SnapshotFactory::CREATE_SNAPSHOT);
        $handler->handle();

        $this->logger->log(Logger::TYPE_INFO, sprintf(
            'Created temp table: %d/%d',
            $this->steps->getCurrent() + 1,
            $this->steps->getTotal()
        ));

        return $this->generateResponse();
    }

    protected function init()
    {
        $this->steps->setTotal($this->totalSteps());
    }

    /**
     * @inheritDoc
     */
    protected function totalSteps()
    {
        $tables = (new TableManager)->findStartsWith($this->prefix);
        return !$tables ? 0 : $tables->count();
    }
}
