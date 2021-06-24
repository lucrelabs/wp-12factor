<fieldset>

<?php if ( $this->description ) : ?>
	<p><?php echo $this->description; ?>
		<?php if ( $this->testmode == 'yes' ) : ?>
			<?php _e( 'TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date.', 'wcv_stripe_connect' ); ?>
		<?php endif; ?></p>
<?php endif; ?>

<?php if ( is_user_logged_in() && ( $this->saved_cards ) ): ?>
	<p class="form-row form-row-wide">

		<a class="button" style="float:right;" href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>#saved-cards"><?php _e( 'Manage cards', 'wcv_stripe_connect' ); ?></a>

		<?php foreach ( $credit_cards as $i => $credit_card ) : ?>
			<input type="radio" id="stripe_card_<?php echo $i; ?>" name="stripe_customer_id" style="width:auto;" value="<?php echo $i; ?>" />
			<label style="display:inline;" for="stripe_card_<?php echo $i; ?>"><?php _e( 'Card ending with', 'wcv_stripe_connect' ); ?> <?php echo $credit_card['active_card']; ?> (<?php echo $credit_card['exp_month'] . '/' . $credit_card['exp_year'] ?>)</label><br />
		<?php endforeach; ?>

		<input type="radio" id="new" name="stripe_customer_id" style="width:auto;" <?php checked( 1, 1 ) ?> value="new" /> <label style="display:inline;" for="new"><?php _e( 'Use a new credit card', 'wcv_stripe_connect' ); ?></label>

	</p>
	<div class="clear"></div>
<?php endif; ?>

<div class="stripe_new_card">

	<?php if ( $this->stripe_checkout ) : ?>

		<a id="stripe_payment_button" class="button" href="#"
			data-description=""
			data-amount="<?php echo WC()->cart->total * $this->multiplier; ?>"
			data-name="<?php echo sprintf( __( '%s', 'woocommerce' ), get_bloginfo( 'name' ) ); ?>"
			data-label="<?php _e( 'Confirm and Pay', 'woocommerce' ); ?>"
			data-currency="<?php echo strtoupper( get_woocommerce_currency() ); ?>"
			><?php _e( 'Enter payment details', 'woocommerce' ); ?></a>

	<?php else : ?>

		<p class="form-row form-row-wide">
			<label for="stripe_card_number"><?php _e( "Credit Card number", 'wcv_stripe_connect' ); ?> <span class="required">*</span></label>
			<input type="number" autocomplete="off" id="stripe_card_number" class="input-text card-number" />
		</p>
		<div class="clear"></div>
		<p class="form-row form-row-first">
			<label for="cc-expire-month"><?php _e( "Expiration date", 'wcv_stripe_connect' ) ?> <span class="required">*</span></label>
			<select id="cc-expire-month" class="woocommerce-select woocommerce-cc-month card-expiry-month">
				<option value=""><?php _e( 'Month', 'wcv_stripe_connect' ) ?></option>
				<option value="01"><?php _e( 'January', 'wcv_stripe_connect' ) ?></option>
				<option value="02"><?php _e( 'February', 'wcv_stripe_connect' ) ?></option>
				<option value="03"><?php _e( 'March', 'wcv_stripe_connect' ) ?></option>
				<option value="04"><?php _e( 'April', 'wcv_stripe_connect' ) ?></option>
				<option value="05"><?php _e( 'May', 'wcv_stripe_connect' ) ?></option>
				<option value="06"><?php _e( 'June', 'wcv_stripe_connect' ) ?></option>
				<option value="07"><?php _e( 'July', 'wcv_stripe_connect' ) ?></option>
				<option value="08"><?php _e( 'August', 'wcv_stripe_connect' ) ?></option>
				<option value="09"><?php _e( 'September', 'wcv_stripe_connect' ) ?></option>
				<option value="10"><?php _e( 'October', 'wcv_stripe_connect' ) ?></option>
				<option value="11"><?php _e( 'November', 'wcv_stripe_connect' ) ?></option>
				<option value="12"><?php _e( 'December', 'wcv_stripe_connect' ) ?></option>
			</select>

			<select id="cc-expire-year" class="woocommerce-select woocommerce-cc-year card-expiry-year">
				<option value=""><?php _e( 'Year', 'wcv_stripe_connect' ) ?></option>
				<?php
					for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i++ ) printf( '<option value="20%u">20%u</option>', $i, $i );
				?>
			</select>
		</p>
		<p class="form-row form-row-last">
			<label for="stripe_card_csc"><?php _e( "Card security code", 'wcv_stripe_connect' ) ?> <span class="required">*</span></label>
			<input type="text" id="stripe_card_csc" maxlength="4" style="width:4em;" autocomplete="off" class="input-text card-cvc" />
			<span class="help stripe_card_csc_description"></span>
		</p>
		<div class="clear"></div>
	<?php endif; ?>

</div>

</fieldset>
