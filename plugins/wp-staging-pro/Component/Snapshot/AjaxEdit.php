<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Component\Snapshot;

use WPStaging\Command\Database\Snapshot\SnapshotDto;
use WPStaging\Repository\SnapshotRepository;
use WPStaging\Service\Adapter\Hooks;
use WPStaging\Service\Component\AbstractTemplateComponent;
use WPStaging\Service\TemplateEngine\TemplateEngine;

class AjaxEdit extends AbstractTemplateComponent
{

    /** @var SnapshotRepository */
    private $repository;

    public function __construct(SnapshotRepository $repository, Hooks $hooks, TemplateEngine $templateEngine)
    {
        parent::__construct($hooks, $templateEngine);
        $this->repository = $repository;
    }

    public function registerHooks()
    {
        $this->addAction('wp_ajax_wpstg--snapshots--edit', 'render');
    }

    public function render()
    {
        if (!$this->isSecureAjax('wpstg_ajax_nonce', 'nonce')) {
            return;
        }

        $id = sanitize_text_field(isset($_POST['id']) ? $_POST['id'] : '');
        $name = sanitize_text_field(isset($_POST['name']) ? $_POST['name'] : '');
        $notes = sanitize_textarea_field(isset($_POST['notes']) ? $_POST['notes'] : '');

        $snapshots = $this->repository->findAll();
        if (!$snapshots) {
            wp_send_json([
                'error' => true,
                'message' => __('No snapshots exist in the system', 'wp-staging'),
            ]);
            return;
        }

        $snapshot = $snapshots->findById($id);
        if (!$snapshot) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Snapshot ID: %s not found', 'wp-staging'), $id),
            ]);
            return;
        }

        $snapshot->setName($name?: SnapshotDto::SNAPSHOT_DEFAULT_NAME);
        $snapshot->setNotes($notes?: null);

        if (!$this->repository->save($snapshots)) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Failed to update snapshot ID: %s', 'wp-staging'), $id),
            ]);
            return;
        }

        wp_send_json(true);
    }
}
