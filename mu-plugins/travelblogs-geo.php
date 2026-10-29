<?php
/**
 * Plugin Name: Travelblogs Geo
 * Description: Coordinate dei Capitoli (postmeta _tb_lat/_tb_lng) — metabox per
 *   correzione manuale e strumento "Strumenti → Geocodifica Travelblogs" che
 *   cerca le coordinate con Nominatim (OpenStreetMap) a partire dalle
 *   Destinazioni del capitolo/dei suoi racconti. Le coordinate automatiche
 *   sono APPROSSIMATE: vanno riviste (lo strumento elenca query e risultato).
 *
 * Stato in _tb_geo_status: auto | manual | notfound.  Le righe "manual"
 * non vengono mai sovrascritte.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- metabox manuale sul Capitolo ---------------- */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'tb_geo', 'Posizione sulla mappa', 'tb_geo_metabox', 'capitolo', 'side' );
	}
);

function tb_geo_metabox( $post ) {
	wp_nonce_field( 'tb_geo_save', 'tb_geo_nonce' );
	$lat = get_post_meta( $post->ID, '_tb_lat', true );
	$lng = get_post_meta( $post->ID, '_tb_lng', true );
	$st  = get_post_meta( $post->ID, '_tb_geo_status', true );
	$q   = get_post_meta( $post->ID, '_tb_geo_query', true );
	echo '<p><label>Latitudine<br><input type="text" name="tb_lat" value="' . esc_attr( $lat ) . '" style="width:100%"></label></p>';
	echo '<p><label>Longitudine<br><input type="text" name="tb_lng" value="' . esc_attr( $lng ) . '" style="width:100%"></label></p>';
	if ( $st ) {
		echo '<p style="color:#666">Stato: <strong>' . esc_html( $st ) . '</strong>' . ( $q ? '<br>Ricerca: ' . esc_html( $q ) : '' ) . '</p>';
	}
	echo '<p style="color:#666">Se modifichi i valori, la posizione diventa "manual" e non viene più toccata dalla geocodifica. Svuota i due campi per rimuoverla.</p>';
}

add_action(
	'save_post_capitolo',
	function ( $post_id ) {
		if ( ! isset( $_POST['tb_geo_nonce'] ) || ! wp_verify_nonce( $_POST['tb_geo_nonce'], 'tb_geo_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$lat = isset( $_POST['tb_lat'] ) ? trim( wp_unslash( $_POST['tb_lat'] ) ) : '';
		$lng = isset( $_POST['tb_lng'] ) ? trim( wp_unslash( $_POST['tb_lng'] ) ) : '';
		$lat = str_replace( ',', '.', $lat );
		$lng = str_replace( ',', '.', $lng );
		if ( '' === $lat && '' === $lng ) {
			delete_post_meta( $post_id, '_tb_lat' );
			delete_post_meta( $post_id, '_tb_lng' );
			delete_post_meta( $post_id, '_tb_geo_status' );
			return;
		}
		if ( is_numeric( $lat ) && is_numeric( $lng ) && abs( $lat ) <= 90 && abs( $lng ) <= 180 ) {
			if ( (string) get_post_meta( $post_id, '_tb_lat', true ) !== (string) (float) $lat || (string) get_post_meta( $post_id, '_tb_lng', true ) !== (string) (float) $lng ) {
				update_post_meta( $post_id, '_tb_lat', (float) $lat );
				update_post_meta( $post_id, '_tb_lng', (float) $lng );
				update_post_meta( $post_id, '_tb_geo_status', 'manual' );
			}
		}
	}
);

/* ---------------- costruzione della ricerca ---------------- */
function tb_geo_deepest_term( $terms ) {
	$best  = null;
	$depth = -1;
	foreach ( $terms as $t ) {
		$d = count( get_ancestors( $t->term_id, 'destinazione', 'taxonomy' ) );
		if ( $d > $depth ) {
			$best  = $t;
			$depth = $d;
		}
	}
	return $best;
}

/** Ritorna la stringa di ricerca per un capitolo ('' se non ricavabile). */
function tb_geo_query_for( $cap_id ) {
	$terms = get_the_terms( $cap_id, 'destinazione' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$terms = array();
		if ( function_exists( 'travelblogs_get_post_di_capitolo' ) ) {
			foreach ( travelblogs_get_post_di_capitolo( $cap_id )->posts as $p ) {
				$pt = get_the_terms( $p, 'destinazione' );
				if ( ! empty( $pt ) && ! is_wp_error( $pt ) ) {
					$terms = array_merge( $terms, $pt );
				}
			}
		}
	}
	$term = $terms ? tb_geo_deepest_term( $terms ) : null;
	if ( ! $term ) {
		return '';
	}
	$parts = array( $term->name );
	foreach ( get_ancestors( $term->term_id, 'destinazione', 'taxonomy' ) as $aid ) {
		$a = get_term( $aid, 'destinazione' );
		if ( $a && ! is_wp_error( $a ) ) {
			$parts[] = $a->name;
		}
	}
	return implode( ', ', $parts );
}

function tb_geo_nominatim( $q ) {
	$url = add_query_arg(
		array(
			'q'               => $q,
			'format'          => 'jsonv2',
			'limit'           => 1,
			'accept-language' => 'it',
		),
		'https://nominatim.openstreetmap.org/search'
	);
	$res = wp_remote_get( $url, array( 'timeout' => 15, 'user-agent' => 'Travelblogs-migration/1.0 (WordPress geocoder)' ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return is_wp_error( $res ) ? $res : new WP_Error( 'http', 'HTTP ' . wp_remote_retrieve_response_code( $res ) );
	}
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $data[0]['lat'] ) ) {
		return null;
	}
	return array( (float) $data[0]['lat'], (float) $data[0]['lon'], (string) ( $data[0]['display_name'] ?? '' ) );
}

/* ---------------- pagina strumento ---------------- */
add_action(
	'admin_menu',
	function () {
		add_management_page( 'Geocodifica Travelblogs', 'Geocodifica Travelblogs', 'manage_options', 'tb-geocode', 'tb_geo_page' );
	}
);

function tb_geo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$batch  = 15;
	$report = array();

	if ( isset( $_POST['tb_geo_run'] ) && check_admin_referer( 'tb_geo_run' ) ) {
		$retry = ! empty( $_POST['tb_geo_retry'] );
		$ids   = get_posts(
			array(
				'post_type'      => 'capitolo',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		$done = 0;
		foreach ( $ids as $id ) {
			$st = get_post_meta( $id, '_tb_geo_status', true );
			if ( 'manual' === $st || 'auto' === $st || ( 'notfound' === $st && ! $retry ) ) {
				continue;
			}
			if ( $done >= $batch ) {
				break;
			}
			$q = tb_geo_query_for( $id );
			update_post_meta( $id, '_tb_geo_query', $q );
			if ( '' === $q ) {
				update_post_meta( $id, '_tb_geo_status', 'notfound' );
				$report[] = array( $id, '(nessuna destinazione)', 'nessuna ricerca possibile' );
				continue;
			}
			$r = tb_geo_nominatim( $q );
			sleep( 1 ); // Nominatim: massimo 1 richiesta al secondo.
			$done++;
			if ( is_wp_error( $r ) ) {
				$report[] = array( $id, $q, 'ERRORE: ' . $r->get_error_message() );
				continue; // niente stato: verrà ritentato.
			}
			if ( null === $r ) {
				update_post_meta( $id, '_tb_geo_status', 'notfound' );
				$report[] = array( $id, $q, 'non trovato' );
				continue;
			}
			update_post_meta( $id, '_tb_lat', $r[0] );
			update_post_meta( $id, '_tb_lng', $r[1] );
			update_post_meta( $id, '_tb_geo_status', 'auto' );
			$report[] = array( $id, $q, $r[2] . ' (' . round( $r[0], 4 ) . ', ' . round( $r[1], 4 ) . ')' );
		}
	}

	global $wpdb;
	$total  = (int) wp_count_posts( 'capitolo' )->publish;
	$counts = $wpdb->get_results( "SELECT meta_value s, COUNT(*) n FROM {$wpdb->postmeta} WHERE meta_key='_tb_geo_status' GROUP BY meta_value", OBJECT_K ); // phpcs:ignore
	$c      = function ( $k ) use ( $counts ) {
		return isset( $counts[ $k ] ) ? (int) $counts[ $k ]->n : 0;
	};
	$todo = $total - $c( 'auto' ) - $c( 'manual' ) - $c( 'notfound' );

	echo '<div class="wrap"><h1>Geocodifica Travelblogs</h1>';
	echo '<p>Capitoli: <strong>' . (int) $total . '</strong> · con posizione automatica: <strong>' . $c( 'auto' ) . '</strong> · manuale: <strong>' . $c( 'manual' ) . '</strong> · non trovati: <strong>' . $c( 'notfound' ) . '</strong> · da fare: <strong>' . max( 0, $todo ) . '</strong></p>';
	echo '<p>Cerca le coordinate con OpenStreetMap/Nominatim usando le Destinazioni del capitolo (o dei suoi racconti). Ogni clic elabora fino a ' . (int) $batch . ' capitoli (circa 1 secondo ciascuno). Le posizioni sono <em>approssimate</em>: controllale e correggile dalla metabox del capitolo.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'tb_geo_run' );
	echo '<p><label><input type="checkbox" name="tb_geo_retry" value="1"> Riprova anche i "non trovati"</label></p>';
	echo '<p><button class="button button-primary" name="tb_geo_run" value="1">Geocodifica i prossimi ' . (int) $batch . '</button></p></form>';

	if ( $report ) {
		echo '<h2>Risultato di questo giro</h2><table class="widefat striped"><thead><tr><th>Capitolo</th><th>Ricerca</th><th>Esito</th></tr></thead><tbody>';
		foreach ( $report as $row ) {
			echo '<tr><td><a href="' . esc_url( get_edit_post_link( $row[0] ) ) . '">' . esc_html( get_the_title( $row[0] ) ) . '</a></td><td>' . esc_html( $row[1] ) . '</td><td>' . esc_html( $row[2] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}
