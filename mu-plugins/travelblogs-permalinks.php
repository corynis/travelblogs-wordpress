<?php
/**
 * Plugin Name: Travelblogs Permalinks
 * Description: URL gerarchici identici al vecchio travelblogs.it:
 *   Diario   /travelers/{autore}/{diario}/
 *   Capitolo /travelers/{autore}/{diario}/{capitolo}/
 *   Post     /travelers/{autore}/{diario}/{capitolo}/{post}/
 * Il profilo autore passa da /author/{x}/ a /travelers/{x}/.
 * I vecchi URL di WordPress (/viaggi/x/, /capitoli/x/, /x/) continuano a
 * funzionare e vengono rediretti (301) al nuovo indirizzo da redirect_canonical.
 *
 * Relazioni usate (postmeta, vedi travelblogs-cpt.php):
 *   capitolo -> viaggio : _viaggio_id
 *   post     -> capitolo: _capitolo_id
 *
 * Dopo l'installazione le regole si rigenerano da sole (una volta).
 * In alternativa: Impostazioni → Permalink → Salva.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TB_PERMA_VERSION = '1';

/* ------------------------------------------------------------------
 * 1. Regole di riscrittura
 * ------------------------------------------------------------------ */
add_action(
	'init',
	function () {
		global $wp_rewrite;
		$wp_rewrite->author_base = 'travelers';

		// Il 2° segmento non deve essere "page/feed/..." (paginazione e feed
		// dell'archivio autore), altrimenti verrebbe scambiato per un diario.
		$skip = '(?!(?:page|feed|rss2|atom|rdf)(?:/|$))';
		$base = '^travelers/([^/]+)/' . $skip;

		add_rewrite_rule( $base . '([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?tb_a=$matches[1]&tb_v=$matches[2]&tb_c=$matches[3]&tb_p=$matches[4]', 'top' );
		add_rewrite_rule( $base . '([^/]+)/([^/]+)/?$', 'index.php?tb_a=$matches[1]&tb_v=$matches[2]&tb_c=$matches[3]', 'top' );
		add_rewrite_rule( $base . '([^/]+)/?$', 'index.php?tb_a=$matches[1]&tb_v=$matches[2]', 'top' );
	},
	5
);

add_filter(
	'query_vars',
	function ( $vars ) {
		return array_merge( $vars, array( 'tb_a', 'tb_v', 'tb_c', 'tb_p' ) );
	}
);

add_action(
	'init',
	function () {
		if ( get_option( 'tb_perma_version' ) !== TB_PERMA_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'tb_perma_version', TB_PERMA_VERSION, true );
		}
	},
	99
);

/* ------------------------------------------------------------------
 * 2. Risoluzione dell'URL -> ID del contenuto
 * ------------------------------------------------------------------ */
function tb_perma_find( $type, $slug, $meta_key = '', $meta_value = 0, $author_id = 0 ) {
	$args = array(
		'post_type'              => $type,
		'name'                   => sanitize_title( $slug ),
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'suppress_filters'       => true,
	);
	if ( $author_id ) {
		$args['author'] = $author_id;
	}
	if ( $meta_key ) {
		$args['meta_key']   = $meta_key; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['meta_value'] = $meta_value; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$ids = get_posts( $args );
	return $ids ? (int) $ids[0] : 0;
}

add_filter(
	'request',
	function ( $qv ) {
		if ( ! isset( $qv['tb_a'] ) ) {
			return $qv;
		}

		// Query che non trova nulla -> 404 pulito.
		$not_found = array(
			'post_type' => 'post',
			'name'      => 'tb-non-trovato',
		);

		$author = get_user_by( 'slug', $qv['tb_a'] );
		if ( ! $author ) {
			return $not_found;
		}

		$viaggio = tb_perma_find( 'viaggio', $qv['tb_v'], '', 0, $author->ID );
		if ( ! $viaggio ) {
			return $not_found;
		}
		if ( empty( $qv['tb_c'] ) ) {
			return array(
				'post_type' => 'viaggio',
				'p'         => $viaggio,
			);
		}

		$capitolo = tb_perma_find( 'capitolo', $qv['tb_c'], '_viaggio_id', $viaggio );
		if ( ! $capitolo ) {
			return $not_found;
		}
		if ( empty( $qv['tb_p'] ) ) {
			return array(
				'post_type' => 'capitolo',
				'p'         => $capitolo,
			);
		}

		$post = tb_perma_find( 'post', $qv['tb_p'], '_capitolo_id', $capitolo );
		if ( ! $post ) {
			return $not_found;
		}
		return array(
			'post_type' => 'post',
			'p'         => $post,
		);
	}
);

/* ------------------------------------------------------------------
 * 3. Generazione dei permalink
 * ------------------------------------------------------------------ */
function tb_perma_is_final( $post ) {
	return $post && '' !== $post->post_name && in_array( $post->post_status, array( 'publish', 'future' ), true );
}

function tb_perma_viaggio_path( $viaggio ) {
	if ( ! tb_perma_is_final( $viaggio ) || 'viaggio' !== $viaggio->post_type ) {
		return '';
	}
	$author = get_userdata( $viaggio->post_author );
	return $author ? 'travelers/' . $author->user_nicename . '/' . $viaggio->post_name : '';
}

function tb_perma_capitolo_path( $capitolo ) {
	if ( ! tb_perma_is_final( $capitolo ) || 'capitolo' !== $capitolo->post_type ) {
		return '';
	}
	$viaggio_id = (int) get_post_meta( $capitolo->ID, '_viaggio_id', true );
	$base       = $viaggio_id ? tb_perma_viaggio_path( get_post( $viaggio_id ) ) : '';
	return $base ? $base . '/' . $capitolo->post_name : '';
}

add_filter(
	'post_type_link',
	function ( $link, $post ) {
		$path = '';
		if ( 'viaggio' === $post->post_type ) {
			$path = tb_perma_viaggio_path( $post );
		} elseif ( 'capitolo' === $post->post_type ) {
			$path = tb_perma_capitolo_path( $post );
		}
		return $path ? home_url( user_trailingslashit( $path ) ) : $link;
	},
	10,
	2
);

add_filter(
	'post_link',
	function ( $link, $post ) {
		if ( ! tb_perma_is_final( $post ) ) {
			return $link;
		}
		$cap_id = (int) get_post_meta( $post->ID, '_capitolo_id', true );
		$base   = $cap_id ? tb_perma_capitolo_path( get_post( $cap_id ) ) : '';
		return $base ? home_url( user_trailingslashit( $base . '/' . $post->post_name ) ) : $link;
	},
	10,
	2
);
