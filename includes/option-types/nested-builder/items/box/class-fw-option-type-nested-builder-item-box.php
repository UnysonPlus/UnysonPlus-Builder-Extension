<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Box — a nestable flex / grid CONTAINER item. Its canvas view template contains a
 * `.builder-items` drop-zone, so the base builder auto-hosts dropped children in the
 * item's `_items` collection (that single class is the entire container↔leaf
 * difference). Holds layout options (row / column / grid + gap) used at render time.
 */
class FW_Option_Type_Nested_Builder_Item_Box extends FW_Option_Type_Nested_Builder_Item {

	public function get_type() {
		return 'box';
	}

	public function get_thumbnails() {
		return array(
			array(
				'html' =>
					'<div class="item-type-icon-title" data-hover-tip="' . esc_attr__( 'Add a Box (flex / grid container)', 'fw' ) . '">' .
					'<div class="item-type-icon"><span class="dashicons dashicons-screenoptions"></span></div>' .
					'<div class="item-type-title">' . esc_html__( 'Box', 'fw' ) . '</div>' .
					'</div>',
			),
		);
	}

	public function get_item_localization() {
		$l = parent::get_item_localization();
		$l['l10n']['item_title'] = __( 'Box', 'fw' );
		return $l;
	}

	public function get_options() {
		return array(
			'layout'  => array(
				'type'    => 'select',
				'label'   => __( 'Layout', 'fw' ),
				'desc'    => __( 'How the children flow inside this box.', 'fw' ),
				'value'   => 'row',
				'choices' => array(
					'row'    => __( 'Row (flex, wraps)', 'fw' ),
					'column' => __( 'Column (stack)', 'fw' ),
					'grid'   => __( 'Grid', 'fw' ),
				),
			),
			'columns' => array(
				'type'    => 'short-select',
				'label'   => __( 'Grid columns', 'fw' ),
				'desc'    => __( 'Used when Layout is Grid.', 'fw' ),
				'value'   => '3',
				'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
			),
			'gap'     => array(
				'type'    => 'short-select',
				'label'   => __( 'Gap', 'fw' ),
				'value'   => 'md',
				'choices' => array( 'none' => __( 'None', 'fw' ), 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
			),
		);
	}
}

FW_Option_Type_Builder::register_item_type( 'FW_Option_Type_Nested_Builder_Item_Box' );
