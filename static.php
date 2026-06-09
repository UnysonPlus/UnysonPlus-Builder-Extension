<?php if (!defined('FW')) die('Forbidden');

if (!is_admin()) {
	wp_register_style(
		'fw-ext-builder-frontend-grid',
		fw_min_uri(fw_ext('builder')->get_uri('/static/css/frontend-grid.css')),
		array(),
		fw_ext('builder')->manifest->get_version()
	);

	// Bootstrap 3 legacy grid stylesheet (with .fw- prefixed classes). Off by
	// default; opt-in via the page-builder settings for sites migrating from
	// the original Unyson plugin so their existing .fw-container / .fw-row
	// markup keeps rendering correctly. Page-builder's main class decides
	// whether to enqueue this handle (see class-fw-extension-page-builder.php).
	wp_register_style(
		'fw-ext-builder-bootstrap-3-legacy',
		fw_min_uri(fw_ext('builder')->get_uri('/static/css/bootstrap-3-legacy.css')),
		array(),
		fw_ext('builder')->manifest->get_version()
	);
}
