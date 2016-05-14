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
				<p>Please set your connection settings first.</p>
			</div>
		<?php endif; ?>

		<?php settings_fields( 'ap_settings' ); ?>
		<?php do_settings_sections( 'ap_settings' ); ?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
			<input type="button" value="Check connection" class="ap-check-connection button-primary" <?php if ($check_connection_settings == false) : ?>disabled="disabled" title="Please set your connection settings first." <?php endif; ?> />
		</p>
	</form>
	<hr />

	<h2>Import products</h2>
	<input type="button" id="importProducts" value="Import products" class="ap-import-products button-primary" name="Import products" 	<?php if ($check_connection_settings == false) : ?> disabled="disabled"  title="Please set your connection settings first." <?php endif; ?>  />
</div> <!-- .wrap -->
