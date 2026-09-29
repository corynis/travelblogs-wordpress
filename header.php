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
			// Fallback statico finché il menu non è configurato in Aspetto → Menu.
			$tb_fallback_links = array(
				'/'                => 'Home',
				'/viaggiatori/'    => 'Viaggiatori',
				'/diari/'          => 'Diari',
				'/video/'          => 'Video',
				'/foto/'           => 'Foto',
				'/news/'           => 'News',
				'/contatti/'       => 'Contatti',
			);
			foreach ( $tb_fallback_links as $tb_url => $tb_label ) {
				printf(
					'<a class="tb-navlink" href="%s">%s</a>',
					esc_url( home_url( $tb_url ) ),
					esc_html( $tb_label )
				);
			}
		}
		?>
	</nav>
