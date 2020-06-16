<?php

namespace WPStaging\Backend\Pro\Notices;

/*
 *  Admin Notices | Warnings | Messages
 */

// No Direct Access
if (!defined("WPINC")) {
    die;
}

/**
 * Class Notices
 * @package WPStaging\Backend\Pro\Notices
 */
class Notices
{
    /**
     * @var object
     */
    private $notices;

    /**
     * @var object
     */
    private $license;


    /**
     * Notices constructor.
     * @param $notices parent Notices class
     */
    public function __construct($notices)
    {
        $this->notices = $notices;
        $this->license = get_option('wpstg_license_status');
    }

    public function getNotices()
    {
        $this->getGlobalAdminNotices();
        $this->getPluginAdminNotices();
    }


    /**
     * Notices shown on all admin pages
     */
    public function getGlobalAdminNotices()
    {
        // Customer never used any valid license key at all. A valid (expired) license key is needed to make use of all wp staging pro features
        // So show this admin notice on all pages to make sure customer is aware that license key must be entered
        if (((isset($this->license->error) && 'expired' !== $this->license->error) || false === $this->license) && !wpstg_is_stagingsite()) {
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-invalid.php';
        }
    }


    /**
     * Notices shown on WP Staging admin pages only
     */
    public function getPluginAdminNotices()
    {
        if (!current_user_can("update_plugins") || !$this->notices->isAdminPage()) {
            return;
        }

        // License key has been expired
        if ((isset($this->license->error) && 'expired' === $this->license->error) || (isset($this->license->license) && 'expired' === $this->license->license)) {
            $licensekey = get_option('wpstg_license_key', '');
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-expired.php';
        }

    }

}
