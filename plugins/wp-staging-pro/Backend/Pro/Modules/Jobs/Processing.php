<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use RuntimeException;
use WPStaging\Backend\Pro\Modules\Jobs\Multisite\ScanDirectories as muScanDirectories;
use WPStaging\Backend\Pro\Modules\Jobs\Multisite\Files as muFiles;
use WPStaging\Backend\Pro\Modules\Jobs\Multisite\SearchReplace as muSearchReplace;
use WPStaging\Component\Job\Dto\SnapshotCreateDto;
use WPStaging\Component\Job\Dto\StepsDto;
use WPStaging\Pro\Component\Job\Database\JobCreateSnapshot;
use WPStaging\Backend\Modules\Jobs\Job;

/**
 * Class Processing
 * Collect clone and job data and delegate all further separate job modules
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class Processing extends Job
{

    use SnapshotTrait;

    /**
     * Start the cloning job
     */
    public function start()
    {


        // Save default job settings to cache file
        $this->init();

        $methodName = $this->options->currentJob;

        if (!method_exists($this, $methodName)) {
            // If method not exists, start over with default action
            $methodName = 'jobFinish';
            $this->log("Processing: Force method '{$methodName}'");
            $this->cache->delete("clone_options");
            $this->cache->delete("files_to_copy");
            // Save default job settings and create clone_options with default settings
            $this->init();
        }

        // Call the job
        return $this->{$methodName}();
    }

    /**
     * Save processing default settings
     * @return bool
     */
    private function init()
    {
        // Make sure this runs one time only on start of processing
        if (!isset($_POST) || !isset($_POST["clone"]) || !empty($this->options->currentJob)) {
            return false;
        }

        // Delete old job files initially
        $this->cache->delete('clone_options');
        $this->cache->delete('files_to_copy');

        // Basic Options
        $this->options->root = str_replace(array("\\", '/'), DIRECTORY_SEPARATOR, ABSPATH);
        $this->options->existingClones = get_option("wpstg_existing_clones_beta", array());

        if (isset($_POST["clone"]) && array_key_exists($_POST["clone"], $this->options->existingClones)) {
            $this->options->current = $_POST["clone"];
            $this->options->databaseUser = $this->options->existingClones[strtolower($this->options->current)]['databaseUser'];
            $this->options->databasePassword = $this->options->existingClones[strtolower($this->options->current)]['databasePassword'];
            $this->options->databaseDatabase = $this->options->existingClones[strtolower($this->options->current)]['databaseDatabase'];
            $this->options->databaseServer = $this->options->existingClones[strtolower($this->options->current)]['databaseServer'];
            $this->options->databasePrefix = $this->options->existingClones[strtolower($this->options->current)]['databasePrefix'];
            $this->options->url = $this->options->existingClones[strtolower($this->options->current)]['url'];
            $this->options->path = wpstg_replace_windows_directory_separator(trailingslashit($this->options->existingClones[strtolower($this->options->current)]['path']));
        }

        // Clone
        $this->options->clone = $_POST["clone"];
        $this->options->cloneDirectoryName = preg_replace("#\W+#", '-', strtolower($this->options->clone));
        $this->options->cloneNumber = $this->options->existingClones[strtolower($this->options->clone)]['number'];
        $this->options->prefix = $this->getPrefix();


        $this->options->excludedTables = array();
        $this->options->clonedTables = array();

        // Files
        $this->options->totalFiles = 0;
        $this->options->copiedFiles = 0;

        // Directories
        $this->options->includedDirectories = array();
        $this->options->excludedDirectories = array();
        $this->options->extraDirectories = array();
        $this->options->directoriesToCopy = array();
        $this->options->scannedDirectories = array();

        // TODO REF: Job Queue; FIFO
        // Job
        $this->options->currentJob = "JobFileScanning";
        $this->options->currentStep = 0;
        $this->options->totalSteps = 0;


        // Create new Job object
        $this->options->job = new \stdClass();


        // Excluded Tables POST
        if (isset($_POST["excludedTables"]) && is_array($_POST["excludedTables"])) {
            $this->options->excludedTables = $_POST["excludedTables"];
        } else {
            $this->options->excludedTables = array();
        }

        // Excluded Directories POST
        if (isset($_POST["excludedDirectories"]) && is_array($_POST["excludedDirectories"])) {
            $this->options->excludedDirectories = $_POST["excludedDirectories"];
        }


        // Included Directories POST
        if (isset($_POST["includedDirectories"]) && is_array($_POST["includedDirectories"])) {
            $this->options->includedDirectories = $_POST["includedDirectories"];
        }

        // Extra Directories POST
        if (isset($_POST["extraDirectories"]) && !empty($_POST["extraDirectories"])) {
            $this->options->extraDirectories = array_map('trim', $_POST["extraDirectories"]);
        }

        // Never copy these folders
        $excludedDirectories = array(
            $this->options->path . 'wp-content/plugins/wp-staging-pro',
            $this->options->path . 'wp-content/plugins/wp-staging-pro-1',
            $this->options->path . 'wp-content/plugins/wp-staging',
            $this->options->path . 'wp-content/plugins/wp-staging-1',
            $this->options->path . 'wp-content/uploads/wp-staging',
        );
        $this->options->excludedDirectories = array_merge($excludedDirectories, $this->options->excludedDirectories);

        // Excluded Files
        $this->options->excludedFiles = apply_filters('wpstg_push_excluded_files', array(
            '.htaccess',
            '.DS_Store',
            '*.git',
            '*.svn',
            '*.tmp',
            'desktop.ini',
            '.gitignore',
            '*.log',
            'wp-staging-optimizer.php',
            '.wp-staging'
        ));

        // Directories to Copy Total
        $this->options->directoriesToCopy = array_merge(
            $this->options->includedDirectories, $this->options->extraDirectories
        );


        // Save settings
        $this->saveExcludedDirectories();
        $this->saveExcludedTables();
        return $this->saveOptions();
    }

    /**
     * Save excluded directories
     * @return boolean
     */
    private function saveExcludedDirectories()
    {

        if (empty($this->options->existingClones[$this->options->clone])) {
            return false;
        }

        $this->options->existingClones[$this->options->clone]['excludedDirs'] = $this->options->excludedDirectories;

        if (false === update_option("wpstg_existing_clones_beta", $this->options->existingClones)) {
            return false;
        }
        return true;
    }

    /**
     * Save excluded tables
     * @return boolean
     */
    private function saveExcludedTables()
    {

        if (empty($this->options->existingClones[$this->options->clone])) {
            return false;
        }

        $this->options->existingClones[$this->options->clone]['excludedTables'] = $this->options->excludedTables;

        if (false === update_option("wpstg_existing_clones_beta", $this->options->existingClones)) {
            return false;
        }
        return true;
    }

    /**
     * Get prefix of staging site
     * @return string
     */
    private function getPrefix()
    {
        $prefix = 'tmp_';

        if ($this->isExternalDatabase() && isset($this->options->existingClones[$this->options->current]['databasePrefix'])) {
            $prefix = $this->options->existingClones[$this->options->current]['databasePrefix'];
        }

        if (isset($this->options->existingClones[$this->options->clone]['prefix'])) {
            $prefix = $this->options->existingClones[$this->options->clone]['prefix'];
        }
        return $prefix;
    }

    /**
     * @param object $response
     * @param string $nextJob
     * @return object
     */
    private function handleJobResponse($response, $nextJob)
    {
        if (isset($response->isForceSave) && $response->isForceSave) {
            $this->options->currentStep = $response->step;
            $this->options->totalSteps = $response->total;
            $this->saveOptions();
        }

        // Job is not done. Status true means the process is finished
        // TODO Ref: $response->isFinished instead of $response->status; self explanatory hence no comment like above
        // Previous logic `if (isset($response->status) && true !== $response->status)` seems off, so if $response->status is not set then jump to next job?
        // If status is not set, then this should throw exception as this is something that's expected
        // Response should be a DTO (we have it now; cloneDTO, perhaps needs extension)
        // TODO Ref: saving options should be here! Single point not spread throughout the code base just like below $this->saveOptions(), why not saving here too?
        if( isset( $response->status ) && true !== $response->status ) {
            return $response;
        }

        $this->options->currentJob = $nextJob;
        $this->options->currentStep = 0;
        $this->options->totalSteps = 0;

        // Save options
        $this->saveOptions();

        return $response;
    }

    /**
     * Check if external database is used
     * @return boolean
     */
    private function isExternalDatabase()
    {

        if (!empty($this->options->databaseUser)) {
            return true;
        }
        return false;
    }

    /**
     * Step 1
     * Scan folders for files to copy
     * @return object
     */
    public function jobFileScanning()
    {
        if (is_multisite()) {
            $directories = new muScanDirectories;
        } else {
            $directories = new ScanDirectories;
        }
        return $this->handleJobResponse($directories->start(), 'jobSnapshot');
    }

    /**
     * Step 2
     * Take a snapshot of the production database
     */
    public function jobSnapshot()
    {
        return $this->handleJobResponse(
            (new JobCreateSnapshot($this->getSnapshotCreateDto(), $this->getStepsDto()))->execute(),
            'jobCopy'
        );
    }

    /**
     * Step 3
     * Copy Files
     * @return object
     */
    public function jobCopy()
    {
        if (is_multisite()) {
            $files = new muFiles;
        } else {
            $files = new Files;
        }

        return $this->handleJobResponse($files->start(), 'jobCopyDatabaseTmp');
    }

    /**
     * Step 4
     * Copy Database tables to tmp tables
     * @return object
     */
    public function jobCopyDatabaseTmp()
    {

        if ($this->isExternalDatabase()) {
            $database = new DatabaseTmpExternal();
        } else {
            $database = new DatabaseTmp();
        }

        return $this->handleJobResponse($database->start(), 'jobSearchReplace');
    }

    /**
     * Step 5
     * Search & Replace
     * @return object
     */
    public function jobSearchReplace()
    {

        if (is_multisite()) {
            $searchReplace = new muSearchReplace();
        } else {
            $searchReplace = new \WPStaging\Backend\Pro\Modules\Jobs\SearchReplace();
        }

        return $this->handleJobResponse($searchReplace->start(), 'jobData');
    }

    /**
     * Step 6
     * So some data operations
     * @return object
     */
    public function jobData()
    {
        return $this->handleJobResponse((new Data)->start(), 'jobDatabaseRename');
    }

    /**
     * Step 7
     * Switch live and tmp tables
     * @return object
     */
    public function jobDatabaseRename()
    {
        $databaseBackup = new \WPStaging\Backend\Pro\Modules\Jobs\DatabaseTmpRename();

        return $this->handleJobResponse($databaseBackup->start(), 'jobFinish');
    }

    /**
     * Step 8
     * Finish Job
     * @return object
     */
    public function jobFinish()
    {
        $finish = new \WPStaging\Backend\Pro\Modules\Jobs\Finish;
        return $this->handleJobResponse($finish->start(), '');
    }

    protected function getSnapshotCreateDto()
    {
        if (!isset($this->options->existingClones[$this->options->current])) {
            return new SnapshotCreateDto;
        }

        // Quick and dirty
        if (!isset($this->options->snapshotIds->{$this->options->current})) {
            $this->assignSnapshotId($this->options);
        }

        if (!isset($this->options->snapshotIds->{$this->options->current})) {
            throw new RuntimeException('Failed to get snapshot id');
        }

        $dto = new SnapshotCreateDto();
        $name = $this->options->existingClones[$this->options->current]['directoryName'];
        $dto->setName(__('Automatic Snapshot: ', 'wp-staging') . $name);
        $dto->setIncrement($this->options->snapshotIds->{$this->options->current});
        $dto->setJob($dto::JOB_AUTOMATIC);
        $dto->setNotes(sprintf(__('Snapshot automatically created before pushing staging site: %s. You can use this snapshot to revert last push action.', 'wp-staging'), $name));
        return $dto;
    }

    protected function getStepsDto()
    {
        $stepsDto = new StepsDto;

        $stepsDto->setTotal($this->options->totalSteps);
        $stepsDto->setCurrent($this->options->currentStep?:0);

        return $stepsDto;
    }
}
