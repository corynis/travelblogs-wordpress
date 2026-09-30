<?php
/**
 * Travelblogs theme — funzioni di base.
 *
 * NOTA: i Custom Post Type (viaggio, capitolo, news) e le tassonomie
 * (destinazione, categoria_news) sono registrati dal mu-plugin
 * `travelblogs-cpt.php` (vedi Notion, sezione 9.9) — qui NON li ridefiniamo,
 * ci limitiamo a consumarli.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TB_THEME_VERSION', '0.1.0' );
define( 'TB_ACCENT_DEFAULT', '#D6272E' );

require_once get_template_directory() . '/inc/class-tb-nav-walker.php';
require_once get_template_directory() . '/template-parts/sidebar-widgets.php';

/**
 * Theme setup.
 */
function tb_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );

	// Dimensioni immagine usate dai template (hero, card, thumb).
	add_image_size( 'tb-hero', 1280, 460, true );
	add_image_size( 'tb-card', 440, 300, true );
	add_image_size( 'tb-thumb', 152, 112, true );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principale', 'travelblogs' ),
		)
	);
}
add_action( 'after_setup_theme', 'tb_theme_setup' );

/**
 * Enqueue stili e font.
 */
function tb_enqueue_assets() {
	wp_enqueue_style(
		'travelblogs-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'travelblogs-main',
		get_template_directory_uri() . '/assets/css/travelblogs.css',
		array(),
		TB_THEME_VERSION
	);

	// Colore accento del brand, esposto come custom property inline
	// cosi' resta configurabile da un futuro pannello Personalizza
	// senza toccare il CSS.
	$accent = get_theme_mod( 'tb_accent_color', TB_ACCENT_DEFAULT );
	wp_add_inline_style(
		'travelblogs-main',
		':root{ --tb-accent: ' . esc_attr( $accent ) . '; }'
	);
}
add_action( 'wp_enqueue_scripts', 'tb_enqueue_assets' );

/**
 * Favicon / icone: il pacchetto completo vive in assets/favicon/.
 */
function tb_favicon_meta() {
	$base = get_template_directory_uri() . '/assets/favicon';
	echo '<link rel="icon" href="' . esc_url( $base . '/favicon.ico' ) . '" sizes="48x48">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $base . '/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $base . '/apple-touch-icon.png' ) . '">' . "\n";
	echo '<link rel="manifest" href="' . esc_url( $base . '/site.webmanifest' ) . '">' . "\n";
	echo '<meta name="theme-color" content="#D6272E">' . "\n";
}
add_action( 'wp_head', 'tb_favicon_meta' );

/**
 * ---------------------------------------------------------------
 * Helper: pillola categoria/tag colorata per continente.
 *
 * Il Diario (`viaggio`) ha un vero continente migrato come postmeta
 * `_continent` (nome in inglese, es. "South America" — dalla vecchia
 * tabella tblog_continent), quindi non è più una deduzione dal nome
 * del termine `destinazione`: è dato reale. Manteniamo comunque anche
 * le chiavi italiane/della tassonomia come fallback per i contesti in
 * cui non abbiamo un `_continent` a disposizione (es. un termine
 * `destinazione` isolato senza viaggio collegato).
 * ---------------------------------------------------------------
 */
function tb_continent_slug_for_term( $term_name ) {
	$map = array(
		'sud america'   => 'sud-america',
		'sudamerica'    => 'sud-america',
		'south america' => 'sud-america',
		'nord america'  => 'nord-america',
		'north america' => 'nord-america',
		'asia'          => 'asia',
		'africa'        => 'africa',
		'europa'        => 'europa',
		'europe'        => 'europa',
		'oceania'       => 'oceania',
	);
	$key = mb_strtolower( trim( $term_name ), 'UTF-8' );
	return isset( $map[ $key ] ) ? $map[ $key ] : '';
}

/**
 * Stampa una pillola .tb-cat, colorata per continente quando il nome
 * del termine passato corrisponde a uno dei 5 continenti noti,
 * altrimenti nel colore accento di default.
 */
function tb_cat_pill( $label, $term_name_for_color = null ) {
	$slug  = $term_name_for_color ? tb_continent_slug_for_term( $term_name_for_color ) : '';
	$class = 'tb-cat' . ( $slug ? ' continente-' . $slug : '' );
	echo '<span class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * ---------------------------------------------------------------
 * Template parts riutilizzabili (nav, footer, pillola destinazioni
 * nella sidebar) sono in /template-parts/ — vedi header.php e
 * footer.php per come vengono richiamati.
 * ---------------------------------------------------------------
 */
