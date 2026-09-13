<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Front-end renderer for a Nested Builder value. The saved value is
 * array( 'json' => '<tree>' ); the tree is a list of items, each
 * { type, options, _items }. These helpers turn that into HTML and, for
 * repeater consumers (Tabs / Accordion / Slider), split the TOP-LEVEL items
 * into panes. Pure output — no admin dependency — so it loads on the front end.
 */

if ( ! function_exists( 'fw_nested_builder_render_items' ) ) {
	/** Recursively render an items tree (box / text / pane / …) to HTML. */
	function fw_nested_builder_render_items( $items ) {
		if ( ! is_array( $items ) ) {
			return '';
		}
		$out = '';
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['type'] ) ) {
				continue;
			}
			$type     = $item['type'];
			$opts     = ( isset( $item['options'] ) && is_array( $item['options'] ) ) ? $item['options'] : array();
			$children = ( isset( $item['_items'] ) && is_array( $item['_items'] ) ) ? fw_nested_builder_render_items( $item['_items'] ) : '';

			switch ( $type ) {
				case 'pane':
					$title = isset( $opts['title'] ) ? (string) $opts['title'] : '';
					$out  .= '<div class="nb-pane">'
						. ( $title !== '' ? '<h3 class="nb-pane__title">' . esc_html( $title ) . '</h3>' : '' )
						. '<div class="nb-pane__body">' . $children . '</div></div>';
					break;

				case 'box':
					$layout = isset( $opts['layout'] ) ? $opts['layout'] : 'row';
					$gap    = isset( $opts['gap'] ) ? $opts['gap'] : 'md';
					$cols   = isset( $opts['columns'] ) ? max( 1, (int) $opts['columns'] ) : 3;
					$gapmap = array( 'none' => '0', 'sm' => '.5rem', 'md' => '1rem', 'lg' => '1.75rem' );
					$style  = 'gap:' . ( isset( $gapmap[ $gap ] ) ? $gapmap[ $gap ] : '1rem' ) . ';';
					if ( $layout === 'grid' ) {
						$style .= 'display:grid;grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));';
					} elseif ( $layout === 'column' ) {
						$style .= 'display:flex;flex-direction:column;';
					} else {
						$style .= 'display:flex;flex-wrap:wrap;';
					}
					$out .= '<div class="nb-box nb-box--' . esc_attr( $layout ) . '" style="' . esc_attr( $style ) . '">' . $children . '</div>';
					break;

				case 'text':
					$content = isset( $opts['content'] ) ? (string) $opts['content'] : '';
					$align   = isset( $opts['align'] ) ? $opts['align'] : 'left';
					$out    .= '<div class="nb-text" style="text-align:' . esc_attr( $align ) . '">'
						. do_shortcode( wp_kses_post( $content ) ) . '</div>';
					break;

				default:
					// Unknown item: fall back to its children so the tree never silently drops content.
					$out .= $children;
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'fw_nested_builder_decode' ) ) {
	/** Normalize a value (array{json} or raw json string) → items array. */
	function fw_nested_builder_decode( $value ) {
		$json = '';
		if ( is_array( $value ) && isset( $value['json'] ) ) {
			$json = $value['json'];
		} elseif ( is_string( $value ) ) {
			$json = $value;
		}
		$items = json_decode( (string) $json, true );
		return is_array( $items ) ? $items : array();
	}
}

if ( ! function_exists( 'fw_nested_builder_render' ) ) {
	/** Render a whole nested-builder value to HTML (stacked). */
	function fw_nested_builder_render( $value ) {
		return fw_nested_builder_render_items( fw_nested_builder_decode( $value ) );
	}
}

if ( ! function_exists( 'fw_nested_builder_panes' ) ) {
	/**
	 * Split the TOP-LEVEL items into panes for a repeater consumer (Tabs / Accordion).
	 * Each returned pane is array( 'title' => …, 'body' => <html> ). A top-level Pane
	 * contributes its title + its children; any other top-level item is wrapped as a
	 * single untitled pane so nothing is lost.
	 *
	 * @return array[] list of { title, body }
	 */
	function fw_nested_builder_panes( $value ) {
		$items = fw_nested_builder_decode( $value );
		$panes = array();
		$i     = 0;
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['type'] ) ) {
				continue;
			}
			$i++;
			$opts  = ( isset( $item['options'] ) && is_array( $item['options'] ) ) ? $item['options'] : array();
			$title = ( isset( $opts['title'] ) && $opts['title'] !== '' )
				? (string) $opts['title']
				: sprintf( __( 'Tab %d', 'fw' ), $i );

			if ( $item['type'] === 'pane' ) {
				$body = ( isset( $item['_items'] ) && is_array( $item['_items'] ) ) ? fw_nested_builder_render_items( $item['_items'] ) : '';
			} else {
				$body = fw_nested_builder_render_items( array( $item ) );
			}

			$panes[] = array( 'title' => $title, 'body' => $body );
		}
		return $panes;
	}
}
