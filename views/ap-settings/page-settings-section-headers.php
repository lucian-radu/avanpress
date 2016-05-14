<?php if ( 'ap_section-basic' == $section['id'] ) : ?>

	<p>Use the Avangate API to import product catalog and pricing information for your account and place orders.</p>
	<p>Make sure your connection details for <b>Avangate API</b> via SOAP are properly set.</p>
	<p>You can find your connection details by accessing your account info available in the
		<a href="https://backend.avangate.com/cpanel/" target="_blank" title="Avangate system settings">'System settings'</a> area of the cPanel.
	</p>

<?php elseif ( 'ap_section-advanced' == $section['id'] ) : ?>

	<p>Import previously defined products from <b>Avangate</b>.</p>

<?php endif; ?>
