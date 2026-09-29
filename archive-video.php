<?php
/**
 * Archivio Video (archive-video.php) — riprende Video.dc.html: griglia di
 * video con play button, paginazione, sidebar Categories/Tags.
 *
 * NOTA: il CPT `video` non è tra quelli documentati su Notion al momento
 * della stesura di questo template (solo viaggio/capitolo/news sono
 * confermati). Il template è quindi predisposto per usarlo appena verrà
 * registrato (stesso pattern esatto degli altri archivi): se il CPT non
 * esiste ancora mostra un avviso invece di dati inventati o di un errore.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$tb_has_video_cpt = post_type_exists( 'video' );
?>

<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:10px;">
		<h1 style="font-size:34px;"><?php esc_html_e( 'Video', 'travelblogs' ); ?></h1>
		<div style="font-size:13px;color:#8A8D94;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
			<span style="color:#3A3D42;font-weight:600;"><?php esc_html_e( 'Video', 'travelblogs' ); ?></span>
		</div>
	</div>
	<div class="tb-title-bar"></div>

	<?php if ( ! $tb_has_video_cpt ) : ?>

		<p style="color:#5B5E64;">
			<?php esc_html_e( 'La sezione Video non è ancora collegata a un tipo di contenuto nel sito (nessun CPT "video" registrato). Il template è pronto: basta registrare il CPT perché questa pagina si popoli automaticamente.', 'travelblogs' ); ?>
		</p>

	<?php else : ?>

		<div class="tb-layout">

			<!-- GRIGLIA VIDEO -->
			<div class="tb-col-main">
				<?php if ( have_posts() ) : ?>
					<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:28px 24px;margin-bottom:28px;">
						<?php
						while ( have_posts() ) :
							the_post();
							$tb_v_terms = get_the_terms( get_the_ID(), 'destinazione' );
							$tb_v_label = ( ! is_wp_error( $tb_v_terms ) && ! empty( $tb_v_terms ) ) ? $tb_v_terms[0]->name : __( 'Video', 'travelblogs' );
							?>
							<div>
								<a href="<?php the_permalink(); ?>" style="display:block;position:relative;">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:210px;width:100%;margin-bottom:12px;' ) ); ?>
									<?php else : ?>
										<span class="tb-photo" style="height:210px;display:block;margin-bottom:12px;background:#1F2023;"></span>
									<?php endif; ?>
									<div class="tb-play"><span>▶</span></div>
								</a>
								<?php tb_cat_pill( $tb_v_label, $tb_v_label ); ?>
								<h3 style="font-size:17px;margin-top:6px;">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<div style="font-size:12px;color:#8A8D94;margin-top:6px;"><?php echo esc_html( get_the_date() ); ?></div>
							</div>
						<?php endwhile; ?>
					</div>

					<div style="display:flex;gap:8px;flex-wrap:wrap;">
						<?php
						$tb_links = paginate_links(
							array(
								'format'    => '',
								'current'   => max( 1, get_query_var( 'paged' ) ),
								'total'     => $GLOBALS['wp_query']->max_num_pages,
								'type'      => 'array',
								'prev_text' => '‹',
								'next_text' => '›',
							)
						);
						if ( $tb_links ) {
							foreach ( $tb_links as $tb_link ) {
								$tb_active = ( false !== strpos( $tb_link, 'current' ) );
								echo '<div class="tb-page' . ( $tb_active ? ' active' : '' ) . '" style="width:auto;padding:0 10px;">' . wp_kses_post( $tb_link ) . '</div>';
							}
						}
						?>
					</div>
				<?php else : ?>
					<p style="color:#5B5E64;"><?php esc_html_e( 'Nessun video pubblicato ancora.', 'travelblogs' ); ?></p>
				<?php endif; ?>
			</div>

			<!-- SIDEBAR -->
			<div class="tb-sidebar">
				<?php
				if ( taxonomy_exists( 'categoria_video' ) ) {
					$tb_video_cats = get_terms( array( 'taxonomy' => 'categoria_video', 'hide_empty' => true ) );
					if ( ! is_wp_error( $tb_video_cats ) && ! empty( $tb_video_cats ) ) :
						?>
						<div class="tb-widget-card">
							<div class="tb-widget-title" style="margin-bottom:14px;"><?php esc_html_e( 'Categories', 'travelblogs' ); ?></div>
							<div style="display:flex;flex-direction:column;">
								<?php foreach ( $tb_video_cats as $tb_vc ) : ?>
									<div class="tb-catrow">
										<a href="<?php echo esc_url( get_term_link( $tb_vc ) ); ?>"><?php echo esc_html( $tb_vc->name ); ?></a>
										<span class="tb-catnum"><?php echo (int) $tb_vc->count; ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
					endif;
				}
				tb_widget_tags();
				?>
			</div>
		</div>

	<?php endif; ?>

</div>

<?php
get_footer();
