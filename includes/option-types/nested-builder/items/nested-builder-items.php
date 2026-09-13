<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Require every built-in Nested Builder item. Each file ends with
 * FW_Option_Type_Builder::register_item_type( … ), which self-registers it.
 */
$dir = dirname( __FILE__ );

require_once $dir . '/pane/class-fw-option-type-nested-builder-item-pane.php';
require_once $dir . '/box/class-fw-option-type-nested-builder-item-box.php';
require_once $dir . '/text/class-fw-option-type-nested-builder-item-text.php';
