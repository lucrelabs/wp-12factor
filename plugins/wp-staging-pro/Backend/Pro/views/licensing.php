<div class="wpstg_admin">
    <span class="wp-staginglogo">
        <img src="<?php echo $this->url . "img/logo_clean_small_212_25.png"?>">
    </span>

    <span class="wpstg-version">
        <?php echo 'Pro v.' . WPStaging\WPStaging::getVersion() ?>
    </span>

    <div class="wpstg-header">
    <div class='wpstg-share-button-container'>
        <div class='wpstg-share-button wpstg-share-button-twitter' data-share-url="https://wordpress.org/plugins/wp-staging">
            <div clas='box'>
                <a href="https://twitter.com/intent/tweet?button_hashtag=wpstaging&text=Check%20out%20this%20plugin%20for%20creating%20a%20one%20click%20WordPress%20testing%20site&via=wpstg" target='_blank'>
                    <span class='wpstg-share'><?php echo __('Tweet #wpstaging','wp-staging'); ?></span>
                </a>
            </div>
        </div>
        <div class="wpstg-share-button wpstg-share-button-twitter">
            <div class="box">
                <a href="https://twitter.com/intent/follow?original_referer=http%3A%2F%2Fsrc.wordpress-develop.dev%2Fwp-admin%2Fadmin.php%3Fpage%3Dwpstg-settings&ref_src=twsrc%5Etfw&region=follow_link&screen_name=renehermenau&tw_p=followbutton" target="_blank">
                    <span class='wpstg-share'><?php echo __('Follow @wpstaging','wp-staging'); ?></span>
                </a>
            </div>
        </div>
        <div class="wpstg-share-button wpstg-share-button-facebook" data-share-url="https://wordpress.org/plugins/wp-staging">
            <div class="box">
                <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-staging" target="_blank">
                    <span class='wpstg-share'><?php echo __('Share on Facebook','wp-staging'); ?></span>
                </a>
            </div>
        </div>
    </div>
    </div>
<div>
    <label for="wpstg_license_key" style='display:block;margin-bottom: 5px;margin-top:10px;'><?php _e('Enter License Key to activate WP Staging Pro:','wp-staging'); ?></label>
      <form method="post" action="#">

      <input type="text" name="wpstg_license_key" style="width:260px;" value='<?php echo get_option('wpstg_license_key', ''); ?>'>
      <?php

      if (isset($license->error) && 'expired' === $license->error){
         $message =  '<span style="color:red;">' . __('Your license expired on ', 'wp-staging') . date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) )) . '</span>';
      } else if (isset($license->license) && 'valid' === $license->license) {
         $message =  __('You\'ll get updates and support until ', 'wp-staging') . date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ));
         $message .= '<p><a href="'.admin_url().'admin.php?page=wpstg_clone" id="wpstg-new-clone" class="wpstg-next-step-link wpstg-link-btn button-primary">Go to Start</a>';
      } else {
         $message = '';
      }

      wp_nonce_field( 'wpstg_license_nonce', 'wpstg_license_nonce' );
      if( isset( $license->license ) && 'valid' === $license->license ) {
         echo '<input type="hidden" name="wpstg_deactivate_license" value="1" />';
         echo '<input type="submit" class="button" value="' . __( 'Deactivate License', 'wp-staging' ) . '">';
      } else {
         echo '<input type="hidden" name="wpstg_activate_license" value="1" />';
         echo '<input type="submit" class="button-primary" value="' . __( 'Activate License', 'wp-staging' ) . '">';
      }
      ?>

    </form>
    <?php       echo '<div style="padding:3px;font-style:italic;">'.$message . '</div>'; ?>
</div>
