<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pane — a titled CONTAINER item. Each TOP-LEVEL Pane is one repeatable unit for the
 * consuming element: a tab (Tabs), a collapsible row (Accordion), a slide (Slider),
 * a step (Steps). Its `title` is the tab label / accordion header; its nested content
 * (Boxes / Text / …) is the pane body. Has a `.builder-items` drop-zone (container).
 */
class FW_Option_Type_Nested_Builder_Item_Pane extends FW_Option_Type_Nested_Builder_Item {

	public function get_type() {
		return 'pane';
	}

	public function get_thumbnails() {
		return array(
			array(
				'html' =>
					'<div class="item-type-icon-title" data-hover-tip="' . esc_attr__( 'Add a Pane (tab / accordion row / slide)', 'fw' ) . '">' .
					'<div class="item-type-icon"><span class="dashicons dashicons-feedback"></span></div>' .
					'<div class="item-type-title">' . esc_html__( 'Pane', 'fw' ) . '</div>' .
					'</div>',
			),
		);
	}

	public function get_item_localization() {
		$l = parent::get_item_localization();
		$l['l10n']['item_title'] = __( 'Pane', 'fw' );
		return $l;
	}

	public function get_options() {
		return array(
			'title' => array(
				'type'  => 'text',
				'label' => __( 'Title', 'fw' ),
				'desc'  => __( 'The tab label / accordion header for this pane.', 'fw' ),
				'value' => __( 'Pane', 'fw' ),
			),
		);
	}
}

FW_Option_Type_Builder::register_item_type( 'FW_Option_Type_Nested_Builder_Item_Pane' );
