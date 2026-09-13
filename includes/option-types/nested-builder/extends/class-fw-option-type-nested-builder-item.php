<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Base class for every Nested Builder item. Fixes the builder type so the item is
 * matched to FW_Option_Type_Nested_Builder (and rejected by other builders). Concrete
 * items implement get_type() / get_thumbnails() / enqueue_static() and self-register.
 */
abstract class FW_Option_Type_Nested_Builder_Item extends FW_Option_Type_Builder_Item {

	final public function get_builder_type() {
		return 'nested-builder';
	}

	/** Shared URI helper for an item's own static folder. */
	protected function item_uri( $append = '' ) {
		return fw_get_framework_directory_uri(
			'/extensions/builder/includes/option-types/' .
			$this->get_builder_type() . '/items/' .
			$this->get_type() . $append
		);
	}

	/**
	 * The per-item data the JS needs (l10n + modal options + defaults). Concrete
	 * items extend the `l10n` and `options`; defaults are derived from the options.
	 */
	public function get_item_localization() {
		return array(
			'l10n'     => array(
				'item_title' => $this->get_type(),
				'edit'       => __( 'Edit', 'fw' ),
				'remove'     => __( 'Remove', 'fw' ),
			),
			'options'  => $this->get_options(),
			'defaults' => array(
				'type'    => $this->get_type(),
				'width'   => '',
				'options' => fw_get_options_values_from_input( $this->get_options(), array() ),
			),
		);
	}

	/**
	 * Enqueue this item's canvas JS and push its localized data into the shared
	 * `window.fwNestedBuilderItems` map (keyed by type) so the register-items
	 * callback can read it. Also loads the modal option-types' static.
	 */
	public function enqueue_static() {
		$handle = 'fw-builder-' . $this->get_builder_type() . '-item-' . $this->get_type();

		wp_enqueue_script(
			$handle,
			fw_min_uri( $this->item_uri( '/static/js/scripts.js' ) ),
			array( 'fw-events', 'fw-option-builder' ),
			fw_ext( 'builder' ) ? fw_ext( 'builder' )->manifest->get_version() : fw()->manifest->get_version(),
			true
		);

		wp_add_inline_script(
			$handle,
			'window.fwNestedBuilderItems=window.fwNestedBuilderItems||{};' .
			'window.fwNestedBuilderItems[' . wp_json_encode( $this->get_type() ) . ']=' .
			wp_json_encode( $this->get_item_localization() ) . ';',
			'before'
		);

		// So the item's Edit modal can render its option types.
		fw()->backend->enqueue_options_static( $this->get_options() );
	}

	/** This item type's option schema (shown in the item's Edit modal). */
	abstract public function get_options();
}
