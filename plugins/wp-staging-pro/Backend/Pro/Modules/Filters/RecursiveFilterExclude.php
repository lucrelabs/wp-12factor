<?php

namespace WPStaging\Backend\Pro\Modules\Filters;

use WPStaging\Iterators\RecursiveFilterExclude as BaseRecursiveFilterExclude;
use WPStaging\Backend\Pro\Modules\Jobs\PluginsHandler;

class RecursiveFilterExclude extends BaseRecursiveFilterExclude
{
    public function accept()
    {
        $result = parent::accept();
        if (!$result) {
            return false;
        }

	    // Exclude tmp and backup plugins like 'plugins/wpstg-tmp-woocommerce' and 'plugins/wpstg-bak-woocommerce'
        $pattern = sprintf('#^plugins/(%s|%s)+#', PluginsHandler::PREFIX_TEMP, PluginsHandler::PREFIX_BACKUP);
        if (preg_match($pattern, $this->getInnerIterator()->getSubPathname())) {
            return false;
        }

        return true;
    }
}
