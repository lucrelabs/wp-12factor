<div id="wpstg-step-1">
    <button id="wpstg-new-clone" class="wpstg-next-step-link wpstg-link-btn wpstg-blue-primary wpstg-button"
            data-action="wpstg_scanning">
        <?php echo __("Create new staging site", "wp-staging") ?>
    </button>
</div>

<?php if (isset($availableClones) && !empty($availableClones)): ?>
    <!-- Existing Clones -->
    <div id="wpstg-existing-clones">
        <h3>
            <?php _e("Your Staging Sites:", "wp-staging") ?>
        </h3>
        <?php foreach ($availableClones as $name => $data): ?>
            <div id="<?php echo $data["directoryName"]; ?>" class="wpstg-clone">

                <?php $urlLogin = $data["url"]; ?>

                <a href="<?php echo $urlLogin ?>" class="wpstg-clone-title" target="_blank">
                    <?php echo $data["directoryName"]; ?>
                </a>

                <?php echo apply_filters("wpstg_before_stage_buttons", $html = '', $name, $data) ?>

                <a href="<?php echo $urlLogin ?>" class="wpstg-open-clone wpstg-clone-action" target="_blank"
                   title="<?php echo __("Open the staging site in a new tab", "wp-staging") ?>">
                    <?php _e("Open", "wp-staging"); ?>
                </a>

                <a href="#" class="wpstg-execute-clone wpstg-clone-action" data-clone="<?php echo $name ?>"
                   title="<?php echo __("Update and overwrite this clone. Select folders and database tables in the next step.", "wp-staging") ?>">
                    <?php _e("Update", "wp-staging"); ?>
                </a>

                <a href="#" class="wpstg-remove-clone wpstg-clone-action" data-clone="<?php echo $name ?>"
                   title="<?php echo __("Delete this clone. Select specific folders and database tables in the next step.", "wp-staging"); ?>">
                    <?php _e("Delete", "wp-staging"); ?>
                </a>
                <?php if (isset($license->license) && 'valid' === $license->license || (isset($license->error) && 'expired' === $license->error) || wpstg_is_local()) {
                    ?>
                    <a href="#" class="wpstg-push-changes wpstg-merge-clone wpstg-clone-action"
                       data-clone="<?php echo $data["directoryName"]; ?>"
                       title="<?php echo __("Push and overwrite current website with this cloned site. Select specific folders and database tables in the next step.", "wp-staging"); ?>">
                        <?php _e("Push Changes", "wp-staging") ?>
                    </a>
                <?php } ?>

                <?php echo apply_filters("wpstg_after_stage_buttons", $html = '', $name, $data) ?>
                <div class="wpstg-staging-info">
                    <?php
                    $dbname = !empty($data['databaseDatabase']) ? __("Database: <span class='wpstg-bold'>" . $data['databaseDatabase'], "wp-staging") . '</span>' : 'Database: <span class="wpstg-bold">' . DB_NAME . '</span>';
                    $prefix = !empty($data['prefix']) ? __("Database Prefix: <span class='wpstg-bold'>" . $data['prefix'], "wp-staging") . '</span> ' : '';
                    $cloneDir = !empty($data['path']) ? __("Directory: <span class='wpstg-bold'>" . $data['path'], "wp-staging") . '</span> ' : 'Directory: ';
                    $url = !empty($data['url']) ? __("URL: <span class='wpstg-bold'>" . $data['url'], "wp-staging") . '</span> ' : 'URL: ';
                    $datetime = !empty($data['datetime']) ? __("Updated: <span>" . date("D, d M Y H:i:s T", $data['datetime']), "wp-staging") . '</span> ' : '&nbsp;&nbsp;&nbsp;';
                    $statusTooltip = "This clone is incomplete and does not work. Clone or update it again! \n\n".
                                      "Important: Keep the browser open until the cloning is finished. \n".
                                      "It will not proceed if your browser is not open.\n\n".
                                      "If you have an unstable internet connection and cloning breaks due to that, clone again only the folders wp-admin, wp-includes, and all database tables.\n\n".
                                      "That will not take much time. Then, you can proceed with the wp-content folder that usually needs the most disk space. ".
                                      "If it interrupts again, at least it will not break the existing staging site again, and you can repeat and resume the last operation.";
                    $status = !empty($data['status']) && $data['status'] !== 'finished' ? "Status: <span class='wpstg-bold' style='color:#ffc2c2;' title='$statusTooltip'>" . $data['status'] . "</span>" : '&nbsp;&nbsp;&nbsp;';

                    echo $dbname;
                    echo '</br>';
                    echo $prefix;
                    echo '</br>';
                    echo $cloneDir;
                    echo '</br>';
                    echo $url;
                    echo '</br>';
                    echo $status;
                    echo '</br>';
                    echo $datetime;
                    ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <!-- /Existing Clones -->
<?php endif ?>

<!-- Remove Clone -->
<div id="wpstg-removing-clone">

</div>
<!-- /Remove Clone -->
