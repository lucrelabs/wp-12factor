<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Component\Job\Database;

use RuntimeException;
use WPStaging\Component\Job\AbstractJob;
use WPStaging\Component\Job\Dto\StepsDto;
use WPStaging\Manager\Database\TableManager;
use WPStaging\Repository\SnapshotRepository;
use WPStaging\Service\Adapter\Database;
use WPStaging\Utils\Logger;

// TODO RPoC
// TODO DRY Snapshot Jobs
class JobRenameTables extends AbstractJob
{

    /** @var string */
    private $source;

    /** @var string */
    private $target;

    /**
     * `$source` and `$target` is prefix for set of given tables
     * @param string $source
     * @param string $target
     * @param StepsDto|null $steps
     */
    public function __construct($source, $target, StepsDto $steps = null)
    {
        parent::__construct($steps);
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // TODO shall we check for locks and force unlock? such as;
        // `SHOW OPEN TABLES WHERE in_use > 0;`
        // `SHOW PROCESSLIST;`
        // `KILL {PROCESS_ID};`
        $tables = (new TableManager)->findStartsWith($this->source);

        if (!$tables) {
            throw new RuntimeException('Failed to find tables with prefix: ' . $this->source);
        }

        $snapshots = null;
        if ((new Database)->getPrefix() === $this->target) {
            $snapshots = (new SnapshotRepository)->findAll();
        }

        // Renaming table is rather instant thing to do, so all in one action!
        $sqlRename = 'RENAME TABLE ';
        $sqlDropTarget = 'DROP TABLE IF EXISTS ';
        $sqlDropSource = 'DROP TABLE IF EXISTS ';
        foreach ($tables as $table) {
            $newName = $this->target . str_replace($this->source, null, $table->getName());
            $sqlRename .= $table->getName() . ' TO ' . $newName . ',';
            $sqlDropTarget .= $newName . ',';
            $sqlDropSource .= $table->getName() . ',';
        }

        $database = new Database;
        $database->exec('SET FOREIGN_KEY_CHECKS = 0');
        $database->exec(trim($sqlDropTarget, ','));
        $database->exec(trim($sqlRename, ','));
        $database->exec(trim($sqlDropSource, ','));
        $database->exec('SET FOREIGN_KEY_CHECKS = 1');

        wp_cache_flush();

        $this->logger->log(Logger::TYPE_INFO, sprintf('Replaced %s to %s', $this->source, $this->target));

        if ($snapshots && 0 < $snapshots->count()) {
            (new SnapshotRepository)->save($snapshots);
        }

        return $this->generateResponse();
    }

    protected function init()
    {
        $this->steps->setTotal(1);
    }
}
