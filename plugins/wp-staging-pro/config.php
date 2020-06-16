<?php
/**
 * @example 'services' or 'components' key can have parameters such as;
 * `Foo::class => ['slug' => '{{slug}}'],`
 * `slug` key matches the class constructor variable name (order does not matter) such as `__construct($slug)`
 * `{{slug}}` value matches the `params.slug` of this file.
 *
 * **IMPORTANT TOPICS**
 * 1. Order of params does not matter as we check & match variable names
 * 2. Not all params in constructor needs to be defined, only the ones we want
 * 3. Hand-typed variables are allowed; `Foo::class => ['slug' => 'bar'],`
 */

use Psr\Log\LoggerInterface;
use WPStaging\Component\Snapshot\AjaxConfirmDelete as SnapshotAjaxConfirmDelete;
use WPStaging\Component\Snapshot\AjaxConfirmRestore as SnapshotAjaxConfirmRestore;
use WPStaging\Component\Snapshot\AjaxCreate as SnapshotAjaxCreate;
use WPStaging\Component\Snapshot\AjaxCreateProgress as SnapshotAjaxCreateProgress;
use WPStaging\Component\Snapshot\AjaxDelete as SnapshotAjaxDelete;
use WPStaging\Component\Snapshot\AjaxEdit as SnapshotAjaxEdit;
use WPStaging\Component\Snapshot\AjaxExport as SnapshotAjaxExport;
use WPStaging\Component\Snapshot\AjaxListing as SnapshotAjaxListing;
use WPStaging\Component\Snapshot\AjaxRestore as SnapshotAjaxRestore;
use WPStaging\Component\Snapshot\AjaxRestoreProgress as SnapshotAjaxRestoreProgress;
use WPStaging\Service\TemplateEngine\TemplateEngine;
use WPStaging\Utils\Logger;

return [
    // Params we can use all around the application with easy access and without duplication / WET; keep it DRY!
    'params' => [
        'slug' => 'wp-staging-pro',
        'domain' => 'wp-staging',
    ],
    // Services are not initialized, they are only initialized once when they are requested. If they are already
    // initialized when requested, the same instance would be used.
    'services' => [
        TemplateEngine::class,
    ],
    // Components are initialized upon plugin init / as soon as the Container is set; such as a class that sets;
    // Ajax Request, Adds a Menu, Form etc. needs to be initialized without being requested hence they go here!
    'components' => [
        SnapshotAjaxListing::class,
        SnapshotAjaxConfirmDelete::class,
        SnapshotAjaxDelete::class,
        SnapshotAjaxCreateProgress::class,
        SnapshotAjaxCreate::class,
        SnapshotAjaxConfirmRestore::class,
        SnapshotAjaxRestoreProgress::class,
        SnapshotAjaxRestore::class,
        SnapshotAjaxExport::class,
        SnapshotAjaxEdit::class,
    ],
    // Map specific interfaces to specific classes.
    // If you map LoggerInterface::class to Logger::class, when you use LoggerInterface as a dependency,
    // it will load / pass Logger class instead
    'mapping' => [
        LoggerInterface::class => Logger::class,
    ],
];
