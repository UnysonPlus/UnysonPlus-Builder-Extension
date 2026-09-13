<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Text — a content LEAF item. Its canvas view template has NO `.builder-items`, so
 * it accepts no children (it is not a container). Holds a WYSIWYG-ish content field.
 */
class FW_Option_Type_Nested_Builder_Item_Text extends FW_Option_Type_Nested_Builder_Item {

	public function get_type() {
		return 'text';
	}

	public function get_thumbnails() {
		return array(
			array(
				'html' =>
					'<div class="item-type-icon-title" data-hover-tip="' . esc_attr__( 'Add a Text block', 'fw' ) . '">' .
					'<div class="item-type-icon"><span class="dashicons dashicons-editor-paragraph"></span></div>' .
					'<div class="item-type-title">' . esc_html__( 'Text', 'fw' ) . '</div>' .
					'</div>',
			),
		);
	}

	public function get_item_localization() {
		$l = parent::get_item_localization();
		$l['l10n']['item_title'] = __( 'Text', 'fw' );
		return $l;
	}

	public function get_options() {
		return array(
			'content' => array(
				'type'  => 'textarea',
				'label' => __( 'Content', 'fw' ),
				'desc'  => __( 'Plain text or HTML. (Rich editor comes later.)', 'fw' ),
				'value' => __( 'Text', 'fw' ),
			),
			'align'   => array(
				'type'    => 'select',
				'label'   => __( 'Alignment', 'fw' ),
				'value'   => 'left',
				'choices' => array( 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ),
			),
		);
	}

	/** Leaves hold no children — strip any stray _items on save. */
	public function get_value_from_attributes( $attributes ) {
		if ( is_array( $attributes ) ) {
			unset( $attributes['_items'] );
		}
		return $attributes;
	}
}

FW_Option_Type_Builder::register_item_type( 'FW_Option_Type_Nested_Builder_Item_Text' );
