<?php
/*
 * Connection details
 */
?>

<?php if ( 'wpps_settings[basic][field-hostname]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'wpps_settings[basic][field-hostname]' ); ?>" name="<?php esc_attr_e( 'wpps_settings[basic][field-hostname]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-hostname'] ); ?>" />
	<span class="example"> 	https://api.avangate.com</span>

<?php endif; ?>

<?php if ( 'wpps_settings[basic][field-location]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'wpps_settings[basic][field-location]' ); ?>" name="<?php esc_attr_e( 'wpps_settings[basic][field-location]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-location'] ); ?>" />
	<span class="example"> /soap/3.0/</span>

<?php endif; ?>

<?php if ( 'wpps_settings[basic][field-merchant-code]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'wpps_settings[basic][field-merchant-code]' ); ?>" name="<?php esc_attr_e( 'wpps_settings[basic][field-merchant-code]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-merchant-code'] ); ?>" />
	<span class="example"> Your account's merchant code available in the 'System settings' area of the cPanel.</span>

<?php endif; ?>

<?php if ( 'wpps_settings[basic][field-merchant-key]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'wpps_settings[basic][field-merchant-key]' ); ?>" name="<?php esc_attr_e( 'wpps_settings[basic][field-merchant-key]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-merchant-key'] ); ?>" />
	<span class="example"> Your account's secret key available in the 'System settings' area of the cPanel.</span>

<?php endif; ?>

<?php
/*
 * Advanced Section
 */
?>


<?php if ( 'wpps_settings[advanced][field-import-button]' == $field['label_for'] ) : ?>
	<input type="button" onclick="alert('import products'); return false;" id="<?php esc_attr_e( 'wpps_settings[advanced][field-import-button]' ); ?>" value="Import products" class="button-primary" name="<?php esc_attr_e( 'wpps_settings[advanced][field-import-button]' ); ?>" />
<?php else : ?>
	<p>Please enter connection details before updating products</p>
<?php endif; ?>
