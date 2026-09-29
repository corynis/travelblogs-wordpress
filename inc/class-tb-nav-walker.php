<?php
/**
 * Walker minimale per il menu principale: rende ogni voce come
 * <a class="tb-navlink [active]"> in linea con il markup del mockup
 * (niente <ul>/<li>, il menu Trawell è una riga flat di link).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TB_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Nessun sottomenu nel design attuale: i figli, se presenti,
		// vengono comunque stampati in linea (nessuna dropdown).
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = 'tb-navlink';
		if ( in_array( 'current-menu-item', $item->classes, true ) || in_array( 'current-menu-parent', $item->classes, true ) ) {
			$classes .= ' active';
		}

		$output .= sprintf(
			'<a class="%1$s" href="%2$s">%3$s</a>',
			esc_attr( $classes ),
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
