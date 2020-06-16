<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Multisite;

use WPStaging\Manager\FileSystem\FileManager;
use WPStaging\Utils\Logger;
use WPStaging\Backend\Modules\Jobs\JobExecutable;
use WPStaging\Backend\Pro\Modules\Jobs\PluginsHandler;
use SplFileObject;
use WPStaging\Service\Utils\FileSystem;

if( !defined( "WPINC" ) ) {
    die;
}

class Files extends JobExecutable
{

    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var int
     */
    private $maxFilesPerRun;

    /**
     * @var string
     */
    private $destination;

    /**
     * Initialization
     */
    public function initialize() {

        if( empty( $this->options->clone ) ) {
            $this->returnException( 'Fatal Error: Files - Can not detect staging site sub folder' );
        }

        $this->destination = ABSPATH;

        $filePath = $this->cache->getCacheDir() . "files_to_copy." . $this->cache->getCacheExtension();

        if( is_file( $filePath ) ) {
            $this->file = new \SplFileObject( $filePath, 'r' );
        }

        // Informational logs
        if( 0 == $this->options->currentStep ) {
            $this->log( "Files: Copying files..." );
        }

        $this->settings->batchSize = $this->settings->batchSize * 1000000;
        //$this->maxFilesPerRun = $this->settings->fileLimit;
        $this->maxFilesPerRun      = ($this->settings->cpuLoad === 'low') ? 50 : 1;

        // Finished - We need this here as well as in the execute() method because execute() is not run at all if totalSteps == 0 (e.g. excluding all folders). Otherwise job never ends
        if( $this->isFinished() ) {
            $this->prepareResponse( true, false );
            return false;
        }
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps() {
        $this->options->totalSteps = ceil( $this->options->totalFiles / $this->maxFilesPerRun );
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute()
    {
        if ($this->isFinished()) {
            // TODO PHP 5.4; `(new PluginsHandler)->handle();` requires min. 5.4
            $pluginsHandler = new PluginsHandler($this->options);
            $pluginsHandler->handle();

            foreach($pluginsHandler->getErrors() as $error) {
                $this->log($error, Logger::TYPE_ERROR);
            }

            $this->log('Files: Copy process finished. Continue next step...');
            $this->prepareResponse(true, false);
            return false;
        }

        // Get files and copy'em
        if (!$this->getFilesAndCopy()) {
            $this->prepareResponse(false, false);
            return false;
        }

        // Prepare and return response
        $this->prepareResponse();

        // Not finished
        return true;
    }

    /**
     * Get files and copy
     * @return bool
     */
    private function getFilesAndCopy()
    {
        if ($this->isOverThreshold()) {
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        if (isset($this->options->copiedFiles) && $this->options->copiedFiles != 0) {
            $this->file->seek($this->options->copiedFiles - 1);
        }

        $this->file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        for($i = 0; $i < $this->maxFilesPerRun; $i++) {
            $this->options->copiedFiles++;

            if ($this->file->eof()) {
                break;
            }

            $file = trim(str_replace(PHP_EOL, null, $this->file->fgets()));

            $this->copyFile($file);
        }

        if ($this->options->copiedFiles % 50 == 0) {
            $this->log(sprintf('Total %s files processed', $this->options->copiedFiles));
        }

        return true;
    }

    /**
     * Checks Whether There is Any Job to Execute or Not
     * @return bool
     */
    private function isFinished() {
        if(
                $this->options->totalSteps == 0 ||
                $this->options->currentStep > $this->options->totalSteps ||
                $this->options->copiedFiles >= $this->options->totalFiles ) {

            return true;
        }
        return false;
    }

    /**
     * @param string $file
     * @return bool
     */
    private function copyFile( $file ) {
        // Add missing path
        $file = trim( $this->options->path . $file );

        // Get parent directory
        $directory = dirname( $file );

        // Directory is excluded
        if( $this->isDirectoryExcluded( $directory ) ) {
            $this->debugLog( "Files: Skipping file/directory by rule: {$file}", Logger::TYPE_INFO );
            return false;
        }

        // Invalid file, skipping it as if succeeded
        if( !is_file( $file ) ) {
            $this->debugLog( "Not a file {$file}" );
            return true;
        }
        // Invalid file, skipping it as if succeeded
        if( !is_readable( $file ) ) {
            $this->log( "Can't read file {$file}", Logger::TYPE_WARNING );
            return true;
        }

        // File is excluded
        if ((new FileManager)->isFilenameExcluded($file, $this->options->excludedFiles)) {
            $this->debugLog( "Files: Skipping file by rule: {$file}", Logger::TYPE_INFO );
            return false;
        }


        // Get file size
        $fileSize = filesize( $file );

        // File is over maximum allowed file size (8MB)
        if( $fileSize >= $this->settings->maxFileSize * 1000000 ) {
            $this->debugLog( "Files: Skipping big file: {$file}", Logger::TYPE_INFO );
            return false;
        }

        // Failed to get destination
        if( false === ($destination = $this->getDestination( $file )) ) {
            $this->log( "Files: Can't get the destination of {$file}", Logger::TYPE_WARNING );
            return false;
        }

        // File is over batch size
        if( $fileSize >= $this->settings->batchSize ) {
            $this->log( "Files: Trying to copy big file: {$file} -> {$destination}", Logger::TYPE_INFO );
            return $this->copyBig( $file, $destination, $this->settings->batchSize );
        }


        // Attempt to copy
        if( !@copy( $file, $destination ) ) {
            $errors = error_get_last();
            $this->log( "Files: Failed to copy file to destination. Error: {$errors['message']} {$file} -> {$destination}", Logger::TYPE_ERROR );
            return false;
        }

        $this->debugLog( 'Files: Copy file ' . $file, Logger::TYPE_DEBUG );

        return true;
    }


    /**
     * Gets destination file and checks if the directory exists, if it does not attempt to create it.
     * If creating destination directory fails, it returns false, gives destination full path otherwise
     * @param string $file
     * @return bool|string
     */
    private function getDestination($file)
    {
        $relativePath = str_replace($this->options->path, null, $file);

        // Change all plugins dir from 'plugin-name' to 'wpstg-tmp-plugin-name'
        $destinationPath = preg_replace(
            '#wp-content/plugins/([A-Za-z0-9-_]+)#',
            'wp-content/plugins/wpstg-tmp-$1',
            $this->destination . $relativePath
        );

        $destinationDirectory = dirname($destinationPath);

        if (!is_dir($destinationDirectory) && !@mkdir($destinationDirectory, wpstg_get_permissions_for_directory(), true)) {
            $this->log("Files: Can not create directory {$destinationDirectory}", Logger::TYPE_ERROR);
            return false;
        }

        return $this->sanitizeDirectorySeparator($destinationPath);
    }

    /**
     * Copy bigger files than $this->settings->batchSize
     * @param string $src
     * @param string $dst
     * @param int $buffersize
     * @return boolean
     */
    private function copyBig( $src, $dst, $buffersize ) {
        $src  = fopen( $src, 'r' );
        $dest = fopen( $dst, 'w' );

        // Try first method:
        while ( !feof( $src ) ) {
            if( false === fwrite( $dest, fread( $src, $buffersize ) ) ) {
                $error = true;
            }
        }
        // Try second method if first one failed
        if( isset( $error ) && ($error === true) ) {
            while ( !feof( $src ) ) {
                if( false === stream_copy_to_stream( $src, $dest, 1024 ) ) {
                    $this->log( "Can not copy file; {$src} -> {$dest}" );
                    fclose( $src );
                    fclose( $dest );
                    return false;
                }
            }
        }
        // Close any open handler
        fclose( $src );
        fclose( $dest );
        return true;
    }

    /**
     * Replace forward slash with current directory separator
     * Windows Compatibility Fix
     * @param string $path Path
     *
     * @return string
     */
    private function sanitizeDirectorySeparator( $path ) {
        return preg_replace( '/[\\\\]+/', '/', $path );
    }

    /**
     * Check if directory is excluded from copying
     * @param string $directory
     * @return bool
     */
    private function isDirectoryExcluded( $directory ) {
        $directory = $this->sanitizeDirectorySeparator( $directory );
        foreach ( $this->options->excludedDirectories as $excludedDirectory ) {
            $excludedDirectory = $this->sanitizeDirectorySeparator( $excludedDirectory );
            if( strpos( trailingslashit( $directory ), trailingslashit( $excludedDirectory ) ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get relative path to WP media upload folder for live site e.g. /wp-content/uploads/sites/7 or wp-content/blogs.dir/ID/files
     *
     * @return string
     */
    protected function getLiveUploadFolder() {
        $uploads    = wp_upload_dir();
        $relBaseDir = str_replace( ABSPATH, '', $uploads['basedir'] );
        return $relBaseDir;
    }

}
