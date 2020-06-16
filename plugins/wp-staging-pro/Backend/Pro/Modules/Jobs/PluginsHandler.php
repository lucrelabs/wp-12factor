<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

/**
 * This class is a file copy handler for all plugins starting with the string 'wpstg-tmp-' like 'wpstg-tmp-pluginname'
 * It does the following steps in sequential order:
 *
 * 1. Handle all plugin directories beginning with wpstg-tmp-pluginname/
 * 2. Delete previous created backup dir if it exists like wpstg-bak-woocommerce/
 * 3. Create backup of plugin as wpstg-tmp-plugin to wpstg-bak-plugin
 * 4. Rename tmp plugin as current like wpstg-tmp-woocommerce/ to woocommerce/
 * 5. Delete bak plugin like wpstg-bak-woocommerce
 *
 *  If plugin can not backed up the tmp file file will be removed and plugin will be skipped
 *
 */

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PluginsHandler
{
    const PREFIX_TEMP = 'wpstg-tmp-';
    const PREFIX_BACKUP = 'wpstg-bak-';

    /** @var string */
    private $pluginsDir;

    /** @var array */
    private $errors = array();

    /** @var string */
    private $pluginName;

    /** @var string */
    private $basePath;

    /** @var string */
    private $tmpPath;

    /** @var string */
    private $backupPath;

    /** @var object */
    private $options;


    /**
     * @param object $options
     */
    public function __construct($options)
    {
        /**
         * Do not use wpstg_get_plugins_dir() from wp-staging-optimizer.php
         * as it is a must-use plugin and there is no guarantee for existing of this function
         * @todo Create a helper class 'Strings' later
         */
        $this->pluginsDir = wpstg_get_plugins_dir_core();
        $this->options = $options;
    }

    /** @return array */
    public function getErrors()
    {
        return $this->errors;
    }

    public function handle()
    {
        $allPlugins = array_keys(get_plugins());

        foreach ($allPlugins as $plugin) {
            if (0 === strpos($plugin, self::PREFIX_TEMP)) {

                $this->pluginName = $this->getPluginName($plugin);
                $this->basePath = $this->pluginsDir . $this->pluginName;
                $this->tmpPath = $this->pluginsDir . self::PREFIX_TEMP . $this->pluginName;
                $this->backupPath = $this->pluginsDir . self::PREFIX_BACKUP . $this->pluginName;

                if (!$this->backupPlugin()) {
                    $this->errors[] = 'Plugin Handler: Skipping plugin ' .  $this->pluginName . '. Please copy it manually from staging to live via FTP!';
                    $this->removeTmpPlugin();
                    continue;
                }
                if (!$this->activateTmpPlugin()) {
                    $this->errors[] = 'Plugin Handler: Skipping plugin ' .  $this->pluginName . ' Can not activate it. Please copy it manually from staging to live via FTP.';
                    $this->restoreBackupPlugin();
                    continue;
                }
                if (!$this->removeBackupPlugin()) {
                    $this->errors[] = 'Plugin Handler: Can not remove backup plugin: ' .  $this->pluginName . '. Please remove it manually via wp-admin > plugins or via FTP.';
                    continue;
                }
            }
        }
    }

    /** @return string */
    private function getPluginName($plugin)
    {
        $pluginTmpName = dirname($plugin);
        return str_replace(self::PREFIX_TEMP, '', $pluginTmpName);
    }

    /** @return bool */
    private function activateTmpPlugin()
    {
        if (!$this->isWritableDir($this->tmpPath)) {
            $this->errors[] = 'Plugin Handler: TMP Plugin Directory does not exist or is not writable: ' . $this->tmpPath;
            return false;
        }

        if (!$this->isWritableDir($this->tmpPath) || !$this->renameDir($this->tmpPath, $this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not activate plugin: ' . self::PREFIX_TEMP . $this->pluginName . ' to ' . $this->pluginName;
            return false;
        }
        return true;
    }

    /**
     * @param string wpstg-bak-plugin-dir/plugin.php
     * @return boolean
     * @todo Allow user to delete all wpstg-bak plugins after pushing
     */
    private function backupPlugin()
    {
        // Nothing to backup on prod site
        if (!is_dir($this->basePath)) {
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);
        }

        if (!$this->isWritableDir($this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not backup plugin: ' . $this->pluginName . ' to ' . self::PREFIX_BACKUP . $this->pluginName . ' Plugin folder not writeable.';
            return false;
        }
        if (!$this->renameDir($this->basePath, $this->backupPath)) {
            $this->errors[] = 'Plugin Handler: Can not rename plugin: ' . $this->$pluginName . ' to ' . self::PREFIX_BACKUP . $this->$pluginName . ' Unknown error.';
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function restoreBackupPlugin()
    {
        if (!$this->isWritableDir($this->backupPath)) {
            $this->errors[] = 'Plugin Handler: Can not restore backup plugin: ' . self::PREFIX_BACKUP . $this->$pluginName  . ' ' . $this->backupPath . ' is not writeable.';
            return false;
        }
        if (!$this->renameDir($this->backupPath, $this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not restore plugin: ' . self::PREFIX_BACKUP . $this->$pluginName . 'Unknown error.';
            return false;
        }
        return true;
    }

    /**
     */
    private function removeTmpPlugin()
    {
        if ($this->isWritableDir($this->tmpPath)) {
            $this->rmDir($this->tmpPath);
            return true;
        }
        $this->errors[] = 'Plugin Handler: Can not remove temp plugin: ' . self::PREFIX_TEMP . $this->$pluginName  . ' Folder ' .$this->tmpPath. ' is not writeable. Remove it manually via FTP.';
        return false;
    }

    /**
     * @param $pluginName string
     */
    private function removeBackupPlugin()
    {
        // No backup to delete on prod site
        if(!is_dir($this->backupPath)){
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);
            return true;
        }
        $this->errors[] = 'Plugin Handler: Can not remove backup plugin: ' . self::PREFIX_BACKUP . $this->$pluginName  . ' Folder ' .$this->backupPath. ' is not writeable. Remove it manually via FTP.';
        return false;
    }


    /**
     * @param string $fullPath
     */
    private function rmDir($fullPath)
    {
        if (!$this->isWritableDir($fullPath)) {
            $this->errors[] = 'Failed to delete directory. Does not exist or is not writable: ' . $fullPath;
            return;
        }

        $iterator = new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
                continue;
            }

            unlink($file->getRealPath());
        }

        rmdir($fullPath);
    }

    /**
     * @param string
     * @return bool
     */
    private function isWritableDir($fullPath)
    {
        return is_dir($fullPath) && is_writable($fullPath);
    }

    /**
     * Implement custom method for renaming dirs to suppresse warnings (@) if folder is not empty
     * @param $from string
     * @param $to string
     * @return bool
     * @todo catch warning without using '@'
     */
    private function renameDir($from, $to)
    {
        if(@rename($from, $to)){
            return true;
        }
        return false;
    }
}