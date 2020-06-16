<div class="wpstg-tabs-wrapper">
    <a href="#" class="wpstg-tab-header active" data-id="#wpstg-scanning-db" style="display:block;">
        <span class="wpstg-tab-triangle">&#9658;</span>
        <?php echo __( "Database Tables", "wp-staging" ) ?>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-db">
        <?php do_action( "wpstg_scanning_db" ) ?>
        <h4 style="margin:0">
            <?php echo __( "Select tables to push to production website", "wp-staging" );
            ?>
        </h4>
        <p>
            <?php echo __( "<strong style='color:red;'>Note:</strong> Your database table selection is stored automatically<br/>and will be used as default selection for the next push.", "wp-staging" ); ?>
        </p>
        <p>
            <strong>
                <?php
                $db = empty( $options->databaseDatabase ) ? DB_NAME : $options->databaseDatabase;
                echo __( "Database: ", "wp-staging" ) . $db;
                echo '<br/>';
                echo __( "Table Prefix: ", "wp-staging" ) . $options->prefix;
                ?>
            </strong>
        </p>
        <a href="#" class="wpstg-button-unselect button" style="margin-bottom:10px;"> <?php _e( 'Unselect All', 'wp-staging' ); ?> </a>
        <?php
        if( isset( $options->tables ) ) {
            foreach ( $options->tables as $table ):

                $tableWithoutPrefix = wpstg_replace_first_match( $options->prefix, '', $table->name );

                if( !is_main_site() ) {
                    // table name without prefix must begin with "int_" if it is either no multisite main site or no regular single wordpress site or when it is a multisite child site e.g 4_options
                    $attributes = in_array( $table->name, $options->excludedTables ) ? '' : "checked";
                } else {
                    // table name without prefix must begin with "string" if it is a regular single WordPress site or a WordPress multisite main site e.g options
                    preg_match( '/^\D*/', $tableWithoutPrefix, $match );
                    $attributes = !array_filter( $match ) ? '' : "checked";
                }

                // Check if table has been stored in excluded array for further pushing threads
                if (in_array( $table->name, $options->excludedTables )){
                    $attributes = '';
                }

                ?>
                <div class="wpstg-db-table">
                    <label>
                        <input class="wpstg-db-table-checkboxes" type="checkbox" name="<?php echo $table->name ?>" <?php echo $attributes ?>>
                        <?php echo $table->name ?>
                    </label>
                    <span class="wpstg-size-info">
                        <?php echo $scan->formatSize( $table->size ) ?>
                    </span>
                </div>
                <?php
            endforeach;
        }
        ?>
        <div>
            <a href="#" class="wpstg-button-unselect button" style="margin-top:10px;"> <?php _e( 'Unselect All', 'wp-staging' ); ?> </a>
        </div>
    </div>

    <a href="#" class="wpstg-tab-header" data-id="#wpstg-scanning-files">
        <span class="wpstg-tab-triangle">&#9658;</span>
        <?php echo __( "Select Files", "wp-staging" ) ?>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-files">
        <h4>
            <?php echo __( "Select plugins, themes & uploads folder to push to production website.", "wp-staging" ) ?>
        </h4>

        <p>
            <?php echo __( "<strong style='color:red;'>Note:</strong> Your folders selection is stored automatically<br/>and will be used as default selection for the next push.", "wp-staging" ); ?>
        </p>
        <?php echo $scan->directoryListing() ?>

        <h4 style="margin:10px 0 10px 0">
            <?php echo __( "Extra Directories to Copy", "wp-staging" ) ?>
        </h4>

        <textarea id="wpstg_extraDirectories" name="wpstg_extraDirectories" style="width:100%;height:100px;"></textarea>
        <p>
            <span>
                <?php
                echo __(
                        "Enter one directory path per line.<br>" .
                        "Directory must start with absolute path: " . $options->root . $options->cloneDirectoryName, "wp-staging"
                )
                ?>
            </span>
        </p>

        <p>
            <span>
                <?php
                if( isset( $options->clone ) ) {
                    echo __( "Plugin files will be pushed to: ", "wp-staging" ) . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins';
                    echo '<br>';
                    echo __( "Theme files will be pushed to: ", "wp-staging" ) . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
                    echo '<br>';
                    echo __( "Media files will be pushed to: ", "wp-staging" ) . $options->root . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads';
                }
                ?>
            </span>
        </p>
    </div>
</div>

<button type="button" class="wpstg-prev-step-link wpstg-link-btn button-primary">
    <?php _e( "Back", "wp-staging" ) ?>
</button>

<button type="button" id="wpstg-push-changes" class="wpstg-next-step-link-pro wpstg-link-btn button-primary" data-action="wpstg_push_changes" data-clone="<?php echo $options->current; ?>">
    <?php
    echo __( 'Push to Live Site', 'wp-staging' );
    ?>
</button>
<p></p>


<?php
$adminUrl = admin_url() . 'options-permalink.php';
echo sprintf(__( "<strong>Note:</strong> If you push the database table '_users' you may have to login again. <br/>"
        . "After pushing from live to staging is complete, go to <a href='%s' target='_new'>wp-admin > settings > permalinks</a> and save permalink settings to prevent page not found errors 404.", "wp-staging" ), admin_url() . 'options-permalink.php');

?>

<div id="wpstg-error-wrapper">
    <div id="wpstg-error-details"></div>
</div>

<div class="wpstg-log-details" style="display: none;"></div>