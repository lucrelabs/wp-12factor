<?php
if( empty( $options->current ) || null === $options->current ) {
    ?>

    <p>
        <strong style="font-size: 14px;"> <?php _e( 'Copy Staging Site to Custom Directory', 'wp-staging' ); ?></strong>
        <br>
        <?php _e( 'Path must be writeable by PHP and an absolute path like <code>/www/public_html/dev</code>', 'wp-staging' ); ?>
        <br/>
    </p>
    <?php
    /**
     * Used for overwriting the default target directory and target hostname via hook
     */
    $directory = \WPStaging\WPStaging::getWPpath();
    $directory = apply_filters( 'wpstg_cloning_target_dir', $directory );
    $customDir = "";
    $customDir = apply_filters( 'wpstg_cloning_target_dir', $customDir );

    $hostname = get_site_url();
    $hostname = apply_filters( 'wpstg_cloning_target_hostname', $hostname );
    $customHostname = "";
    $customHostname = apply_filters( 'wpstg_cloning_target_hostname', $customHostname );

    ?>

    <table cellspacing="0" id="wpstg-clone-directory">
        <tbody>
            <tr><th style="text-align:left;min-width: 120px;"><?php _e( 'Target Directory:', 'wp-staging' ); ?> </th>
                <td> <input style="width:300px;" type="text" name="wpstg_clone_dir" id="wpstg_clone_dir" value="<?php echo $customDir; ?>" title="wpstg_clone_dir" placeholder="<?php echo $directory; ?>" autocapitalize="off"></td>
            </tr>
            <tr>
                <td></td>
                <td>
                  <code>
                    <a id="wpstg-use-target-dir" data-base-path="<?php echo $directory?>" data-path="<?php echo $directory?>" class="wpstg-pointer">
                      Set Default:
                    </a>
                    <span class="wpstg-use-target-dir--value">
                      <?php echo $directory; ?>
                    </span>
                  </code>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2">
                    <p><strong style="font-size: 14px;"> <?php _e( 'Specify Target Hostname', 'wp-staging' ); ?></strong>
                        <br/>
                        <?php _e( 'Set the hostname of the target site, for instance https://example.com or https://example.com/staging', 'wp-staging' ); ?>
                        <br/>
                        <?php _e( 'Make sure the hostname points to the target directory from above.', 'wp-staging' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
              <th style="text-align:left;min-width:120px;">Target Hostname: </th>
              <td>
                <input style="width:300px;" type="text" name="wpstg_clone_hostname" id="wpstg_clone_hostname" value="<?php echo $customHostname; ?>" title="wpstg_clone_hostname" placeholder="<?php echo $hostname; ?>" autocapitalize="off">
              </td>
            </tr>
            <tr>
                <td></td>
                <td>
                  <code>
                    <a id="wpstg-use-target-hostname" data-base-uri="<?php echo $hostname?>" data-uri="<?php echo $hostname?>" class="wpstg-pointer">
                      Set Default:
                    </a>
                    <span class="wpstg-use-target-hostname--value">
                      <?php echo get_site_url(); ?>
                    </span>
                  </code>
                </td>
            </tr>
        </tbody>
    </table>

    <?php
} else {

    $cloneDir  = isset( $options->existingClones[$options->current]['cloneDir'] ) ? $options->existingClones[$options->current]['cloneDir'] : '';
    $hostname  = isset( $options->existingClones[$options->current]['url'] ) ? $options->existingClones[$options->current]['url'] : '';
    $directory = isset( $options->existingClones[$options->current]['path'] ) ? $options->existingClones[$options->current]['path'] : '';
    ?>

    <table cellspacing="0" id="wpstg-clone-directory">
        <tbody>
            <tr><th style="text-align:left;min-width: 120px;">Target Directory: </th>
                <td> <input disabled="disabled" readonly style="width:300px;" type="text" name="wpstg_clone_dir" id="wpstg_clone_dir" value="<?php echo $directory; ?>" title="wpstg_clone_dir" placeholder="<?php echo \WPStaging\WPStaging::getWPpath(); ?>" autocapitalize="off"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
            </tr>
            <tr><th style="text-align:left;min-width:120px;">Target Hostname: </th><td> <input style="width:300px;" type="text" name="wpstg_clone_hostname" id="wpstg_clone_hostname" value="<?php echo $hostname; ?>" title="wpstg_clone_hostname" placeholder="<?php echo get_site_url(); ?>" autocapitalize="off">
                </td>
            </tr>
        </tbody>
    </table>

<?php } ?>

