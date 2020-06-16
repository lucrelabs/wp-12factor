<?php
if( empty( $options->current ) || null === $options->current ) {
    ?>

    <p><label><input type="checkbox" id="wpstg-ext-db" name="wpstg-ext-db" value="true">
            <strong style="font-size: 14px;"><?php _e( 'Copy Staging Site to Separate Database', 'wp-staging' ); ?></strong>
            <br><?php _e( 'Database must be created manually in advance!<br/><br/><strong>Important:</strong> If there are already tables with the same database prefix and name in the database they will be dropped without any further asking!', 'wp-staging' ); ?>
        </label></p>
    <table cellspacing="0" id="wpstg-external-db">
        <tbody>
        <tr>
            <th>Server</th><td><input readonly type="text" name="wpstg_db_server" id="wpstg_db_server" value="" title="wpstg_db_server" placeholder="localhost" autocapitalize="off">
            </td></tr>
        <tr>
            <th><?php _e("User", "wp-staging")?></th><td><input readonly type="text" name="wpstg_db_username" id="wpstg_db_username" value="" autocapitalize="off" class="">
            </td></tr>
        <tr>
            <th><?php _e("Password", "wp-staging")?></th><td><input readonly type="password" name="wpstg_db_password" id="wpstg_db_password" class="">
            </td></tr>
        <tr>
            <th><?php _e("Database", "wp-staging")?></th><td><input readonly type="text" name="wpstg_db_database" id="wpstg_db_database" value="" autocapitalize="off">
            </td></tr>
        <tr>
            <th><?php _e("Database Prefix", "wp-staging")?></th><td><input readonly type="text" name="wpstg_db_prefix" id="wpstg_db_prefix" value="<?php echo $db->prefix; ?>" placeholder="<?php echo $db->prefix; ?>" autocapitalize="off">
            </td></tr>
        <tr>
            <th></th>
            <td><a href="#" id="wpstg-db-connect"><?php _e("Test Database Connection", "wp-staging")?></a></td>
        </tr>
        </tbody>
    </table>

    <?php
} else {

    $database = isset( $options->existingClones[$options->current]['databaseDatabase'] ) ? $options->existingClones[$options->current]['databaseDatabase'] : '';
    $user     = isset( $options->existingClones[$options->current]['databaseUser'] ) ? $options->existingClones[$options->current]['databaseUser'] : '';
    $password = isset( $options->existingClones[$options->current]['databasePassword'] ) ? $options->existingClones[$options->current]['databasePassword'] : '';
    $prefix   = isset( $options->existingClones[$options->current]['databasePrefix'] ) ? $options->existingClones[$options->current]['databasePrefix'] : '';
    $server   = isset( $options->existingClones[$options->current]['databaseServer'] ) ? $options->existingClones[$options->current]['databaseServer'] : '';
    ?>

    <table cellspacing="0" id="wpstg-external-db">
        <tbody>
        <tr>
            <th>Server</th><td><input disabled="disabled" readonly type="text" name="wpstg_db_server" id="wpstg_db_server" value="<?php echo $server; ?>" title="wpstg_db_server" placeholder="localhost" autocapitalize="off">
            </td></tr>
        <tr>
            <th>User</th><td><input disabled="disabled" readonly type="text" name="wpstg_db_username" id="wpstg_db_username" value="<?php echo $user; ?>" autocapitalize="off" class="">
            </td></tr>
        <tr>
            <th>Password</th><td><input disabled="disabled" readonly type="password" name="wpstg_db_password" id="wpstg_db_password" class="" value="*********">
            </td></tr>
        <tr>
            <th>Database</th><td><input disabled="disabled" readonly type="text" name="wpstg_db_database" id="wpstg_db_database" value="<?php echo $database; ?>" autocapitalize="off">
            </td></tr>
        <tr>
            <th>Database Prefix</th><td><input disabled="disabled" readonly type="text" name="wpstg_db_prefix" id="wpstg_db_prefix" value="<?php echo $prefix; ?>" placeholder="<?php echo $db->prefix; ?>" autocapitalize="off">
            </td>
        </tr>
        </tbody>
    </table>

<?php } ?>