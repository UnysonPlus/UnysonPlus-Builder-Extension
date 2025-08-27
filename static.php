<?php if (!defined('FW')) die('Forbidden');

if (!is_admin()) {
	wp_register_style(
		'fw-ext-builder-frontend-grid',
		fw_ext('builder')->get_uri('/static/css/frontend-grid.css'),
		array(),
		fw_ext('builder')->manifest->get_version()
	);
}

add_action('wp_enqueue_scripts', function() {

    if ( is_admin() ) return; // front-end only

    // Make sure Builder extension is loaded
    if ( ! function_exists('fw_ext') || ! fw_ext('builder') ) return;

    // Get Page Builder settings
    $builder_options = get_option('fw_ext_settings_options:page-builder', array());
    $legacy_enabled  = isset($builder_options['legacy_bootstrap_support']) ? $builder_options['legacy_bootstrap_support'] : 'no';

    if ( $legacy_enabled === 'yes' ) {
        // Debug: print the URI to confirm path
        // echo fw_ext('builder')->get_uri('/static/css/bootstrap-3-legacy.css');

        // Enqueue legacy Bootstrap 3 CSS
        wp_enqueue_style(
            'unyson-bootstrap-3',
            fw_ext('builder')->get_uri('/static/css/bootstrap-3-legacy.css'),
            array(),
            fw_ext('builder')->manifest->get_version()
        );
    }

});



// Add body class if legacy mode is enabled
add_filter('body_class', function($classes) {

    $builder_options = get_option('fw_ext_settings_options:page-builder', array());
    $legacy_enabled  = isset($builder_options['legacy_bootstrap_support']) ? $builder_options['legacy_bootstrap_support'] : 'no';

    if ( $legacy_enabled === 'yes' ) {
        $classes[] = 'unyson-bootstrap-legacy';
    }

    return $classes;
});


$builder_options = get_option('fw_ext_settings_options:page-builder', array());
$legacy_enabled = isset($builder_options['legacy_bootstrap_support']) ? $builder_options['legacy_bootstrap_support'] : 'no';

fw_print($legacy_enabled); // outputs 'yes' or 'no'


