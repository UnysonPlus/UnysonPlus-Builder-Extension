<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Nested Builder — a lightweight embeddable page-builder canvas as an OPTION TYPE.
 *
 * Subclasses FW_Option_Type_Builder (the same base as form-builder and the main
 * page-builder), so it inherits the drag-drop canvas, JSON value storage
 * (`array('json' => '…')`), per-instance JS init and nested-`_items` recursion for
 * free. Unlike the heavy page-builder option type (singleton-bound to the post
 * screen), this registers only a SMALL, curated item set and pulls in none of the
 * page-builder's section/editor-integration singletons — so multiple instances can
 * live inside other elements' option popups (e.g. one per Tab pane), exactly the
 * way the Contact Form embeds `form-builder`.
 *
 * v1 item set: `box` (a nestable flex/grid container) + `text`. More content leaves
 * (heading, icon-box, media-image, button, divider) register the same way.
 */
class FW_Option_Type_Nested_Builder extends FW_Option_Type_Builder {

	public function get_type() {
		return 'nested-builder';
	}

	/**
	 * Require the item base + concrete items (each self-registers via
	 * FW_Option_Type_Builder::register_item_type()). Runs on option-type init.
	 */
	protected function _init() {
		$dir = dirname( __FILE__ );
		require_once $dir . '/extends/class-fw-option-type-nested-builder-item.php';
		require_once $dir . '/items/nested-builder-items.php';

		/** Lets other code register extra nested-builder items on this hook. */
		do_action( 'fw_option_type_nested_builder_init' );
	}

	/** Only accept items that belong to THIS builder (our item base class). */
	protected function item_type_is_valid( $item_type_instance ) {
		return is_subclass_of( $item_type_instance, 'FW_Option_Type_Nested_Builder_Item' );
	}

	/**
	 * Default to a FULL-WIDTH, label-less row. The builder is a wide canvas (its
	 * `_get_backend_width_type()` is already `full`), so a narrow labelled row squeezes
	 * it into a column and leaves a big empty gutter on the left. The option-row view
	 * (backend-option-design-default.php) decides the label column BEFORE the option
	 * type renders, falling back to `_default_label()` when the consumer passes no
	 * `label`. Returning false here makes that fallback hide the label column and expand
	 * the input to `fw-col-xs-12` — so the canvas spans the whole row like the
	 * page-builder does. A consumer can still pass an explicit `label` to override.
	 */
	public function _default_label( $id, $option ) {
		return false;
	}

	/**
	 * Reuse the whole base builder bundle (parent) + a small shared stylesheet for
	 * the item previews. Item JS + per-item localization are enqueued by each item's
	 * own enqueue_static().
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		parent::_enqueue_static( $id, $option, $data );

		$base = '/extensions/builder/includes/option-types/nested-builder';
		$ver  = fw_ext( 'builder' ) ? fw_ext( 'builder' )->manifest->get_version() : fw()->manifest->get_version();

		if ( file_exists( dirname( __FILE__ ) . '/static/css/nested-builder.css' ) ) {
			wp_enqueue_style(
				'fw-option-type-nested-builder',
				fw_get_framework_directory_uri( $base . '/static/css/nested-builder.css' ),
				array( 'fw-option-builder' ),
				$ver
			);
		}

		// Per-instance builder tweaks (generic isValidDirectParent so our containers nest).
		wp_enqueue_script(
			'fw-option-type-nested-builder',
			fw_get_framework_directory_uri( $base . '/static/js/nested-builder.js' ),
			array( 'fw-events', 'fw-option-builder' ),
			$ver,
			true
		);
	}
}

FW_Option_Type::register( 'FW_Option_Type_Nested_Builder' );
