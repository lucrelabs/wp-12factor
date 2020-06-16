<?php

namespace WPStaging\Utils;

use WPStaging\Utils\Filesystem;

// No Direct Access
if (!defined("WPINC"))
{
    die;
}

/**
 * Class for robots.txt
 *
 */
class RobotsTxt {
    
    /**
     * 
     * @var obj
     */
    public $filesystem;
    
    public function __construct() {
        $this->filesystem = new Filesystem();
    }

    /**
     * Create .htaccess file
     *
     * @param  string  $path Path to file
     * @return boolean
     */
    public function create( $path ) {
        return $this->filesystem->create( $path, implode( PHP_EOL, array(
                    'User-agent: *',
                    'Disallow: /',
                ) ) );
    }

}
