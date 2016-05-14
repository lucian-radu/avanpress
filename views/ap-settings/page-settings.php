<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h1><?php esc_html_e( AP_NAME ); ?> Settings</h1>

	<?php
		$check_connection_settings = $GLOBALS['ap_settings']['settings']['check_connection_settings'];
	?>
	<hr />
	<form method="post" action="options.php">

		<?php if ($check_connection_settings == false) : ?>
			<div class="update-nag notice">
				<p>Please complete the following fields: Hostname, Location, Merchant code, Merchant key.</p>
			</div>
		<?php endif; ?>

		<?php settings_fields( 'ap_settings' ); ?>
		<?php do_settings_sections( 'ap_settings' ); ?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
			<input type="button" value="Check connection" class="ap-check-connection button-secondary" <?php if ($check_connection_settings == false) : ?>disabled="disabled" title="Please set your connection settings first." <?php endif; ?> />
			<span id="checkConnectionLoading" style="display:none;">
				<img src="<?php echo AP_PATH; ?>images/loading.gif" />
			</span>
			<span id="checkConnectionOk" style="display:none;">
				<img src="<?php echo AP_PATH; ?>images/ok.png" />
			</span>
			<span id="checkConnectionNo" style="display:none;">
				<img src="<?php echo AP_PATH; ?>/images/no.png" />
			</span>
		</p>
	</form>
	<hr />

	<h2>Import products</h2>
	<p>Update your product catalog via <b>Avangate API</b></p>
	<input type="button" id="importProducts" value="Import products" class="ap-import-products button-primary" name="Import products" 	<?php if ($check_connection_settings == false) : ?> disabled="disabled"  title="Please set your connection settings first." <?php endif; ?>  />
	<span id="importProductsLoading" style="display:none;">
		<img src="<?php echo AP_PATH; ?>images/loading.gif" />
	</span>
	<span id="importProductsOk" style="display:none;">
		<img src="<?php echo AP_PATH; ?>images/ok.png" />
	</span>
	<span id="importProductsNo" style="display:none;">
		<img src="<?php echo AP_PATH; ?>images/no.png" />
	</span>
</div> <!-- .wrap -->
