<?php
/**
 * Header condiviso: ticker "le ultime", logo, tagline, menu principale.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="tb-page-wrap">

	<?php
	// Ticker "le ultime" — mostra l'ultimo post pubblicato (diario o news).
	$tb_ticker_post = get_posts(
		array(
			'post_type'      => array( 'post', 'news' ),
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	if ( ! empty( $tb_ticker_post ) ) :
		$tb_latest = $tb_ticker_post[0];
		?>
		<div class="tb-ticker">
			<span class="tb-ticker-label"><?php esc_html_e( 'LE ULTIME', 'travelblogs' ); ?></span>
			<span>&lsaquo;</span>
			<a class="tb-ticker-title" href="<?php echo esc_url( get_permalink( $tb_latest ) ); ?>">
				<?php echo esc_html( get_the_title( $tb_latest ) ); ?>
			</a>
			<span>&rsaquo;</span>
			<span class="tb-ticker-date"><?php echo esc_html( human_time_diff( get_the_time( 'U', $tb_latest ), current_time( 'timestamp' ) ) ); ?> fa</span>
			<form class="tb-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="text" name="s" placeholder="Cerca&hellip;" aria-label="<?php esc_attr_e( 'Cerca', 'travelblogs' ); ?>" style="background:transparent;border:none;color:inherit;">
			</form>
		</div>
	<?php endif; ?>

	<div class="tb-brandbar">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> &mdash; home">
			<img class="tb-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/travelblogs-logo-color.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="245" height="44">
		</a>
		<?php $tb_tagline = get_bloginfo( 'description' ); ?>
		<?php if ( $tb_tagline ) : ?>
			<div class="tb-tagline"><?php echo esc_html( $tb_tagline ); ?></div>
		<?php endif; ?>
	</div>

	<nav class="tb-nav" aria-label="<?php esc_attr_e( 'Menu principale', 'travelblogs' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'walker'         => new TB_Nav_Walker(),
				)
			);
		} else {
			// Fallback finché il menu non è configurato in Aspetto → Menu.
			// Gli URL sono risolti dinamicamente (archivio reale del CPT,
			// pagina reale) invece di essere scritti a mano: uno slug
			// hardcoded si disallinea silenziosamente se il CPT viene
			// registrato con un rewrite diverso (è già successo con
			// "Diari" → /viaggi/ invece di /diari/). Quando un URL non si
			// riesce a risolvere, il link è omesso invece di puntare a un
			// indirizzo indovinato.
			$tb_diari_url = post_type_exists( 'viaggio' ) ? get_post_type_archive_link( 'viaggio' ) : false;
			$tb_video_url = post_type_exists( 'video' ) ? get_post_type_archive_link( 'video' ) : false;
			$tb_news_url  = post_type_exists( 'news' ) ? get_post_type_archive_link( 'news' ) : false;
			$tb_foto_page = get_page_by_path( 'foto' );
			$tb_foto_url  = $tb_foto_page ? get_permalink( $tb_foto_page ) : false;
			$tb_contatti_page = get_page_by_path( 'contatti' );
			$tb_contatti_url  = $tb_contatti_page ? get_permalink( $tb_contatti_page ) : false;

			// "Viaggiatori": con un solo autore attivo linkiamo direttamente
			// al suo profilo, invece di un archivio-di-tutti-gli-autori che
			// con la sola Shella non avrebbe senso.
			$tb_travelers_url = false;
			$tb_authors       = get_users( array( 'has_published_posts' => array( 'post', 'viaggio' ), 'number' => 2 ) );
			if ( 1 === count( $tb_authors ) ) {
				$tb_travelers_url = get_author_posts_url( $tb_authors[0]->ID );
			}

			$tb_fallback_links = array(
				'Home'        => home_url( '/' ),
				'Viaggiatori' => $tb_travelers_url,
				'Diari'       => $tb_diari_url,
				'Video'       => $tb_video_url,
				'Foto'        => $tb_foto_url,
				'News'        => $tb_news_url,
				'Contatti'    => $tb_contatti_url,
			);
			foreach ( $tb_fallback_links as $tb_label => $tb_url ) {
				if ( ! $tb_url || is_wp_error( $tb_url ) ) {
					continue;
				}
				printf(
					'<a class="tb-navlink" href="%s">%s</a>',
					esc_url( $tb_url ),
					esc_html( $tb_label )
				);
			}
		}
		?>
	</nav>
