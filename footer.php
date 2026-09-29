<?php
/**
 * Footer condiviso: 3 colonne (About / Get in touch / Credits) + riga continenti + copyright.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer class="tb-footer">
		<div class="tb-footer-grid">
			<div>
				<h4><?php esc_html_e( 'About Travelblogs', 'travelblogs' ); ?></h4>
				<p><?php echo wp_kses_post( get_theme_mod( 'tb_footer_about', "Nato da una tesi di laurea, il portale dei viaggiatori per viaggiatori. Perch&eacute; l'importante &egrave; viaggiare!" ) ); ?></p>
			</div>
			<div>
				<h4><?php esc_html_e( 'Get In Touch', 'travelblogs' ); ?></h4>
				<div class="tb-footer-social">
					<a href="<?php echo esc_url( get_theme_mod( 'tb_social_facebook', '#' ) ); ?>" aria-label="Facebook">f</a>
					<a href="<?php echo esc_url( get_theme_mod( 'tb_social_x', '#' ) ); ?>" aria-label="X">&#10005;</a>
					<a href="<?php echo esc_url( get_theme_mod( 'tb_social_instagram', '#' ) ); ?>" aria-label="Instagram">&#9686;</a>
				</div>
			</div>
			<div>
				<h4><?php esc_html_e( 'Credits', 'travelblogs' ); ?></h4>
				<p><?php esc_html_e( 'Designed by Shella.it', 'travelblogs' ); ?></p>
			</div>
		</div>

		<?php
		// Riga continenti — link verso archivio Destinazione filtrato per continente,
		// quando i termini di primo livello della tassonomia esistono.
		$tb_continents = array( 'Africa', 'Asia', 'Australia', 'Europe', 'North America', 'Other', 'South America' );
		?>
		<div style="border-top:1px solid #24262B;">
			<div style="max-width:1280px;margin:0 auto;padding:16px 24px;display:flex;gap:26px;font-size:13px;align-items:center;flex-wrap:wrap;">
				<?php foreach ( $tb_continents as $tb_continent ) : ?>
					<span><?php echo esc_html( $tb_continent ); ?></span>
				<?php endforeach; ?>
				<a href="#top" style="margin-left:auto;color:#8A8D94;">Top &uarr;</a>
			</div>
		</div>

		<div class="tb-footer-bottom">
			&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</div>
	</footer>

</div><!-- .tb-page-wrap -->

<?php wp_footer(); ?>
</body>
</html>
