<?php
/**
 * Template Diario (viaggio) singolo — single-viaggio.php.
 *
 * Prima mancava: i Diari ricadevano su single.php, che mostra solo il corpo
 * del post (vuoto per un Diario, che ha solo titolo + descrizione).
 * Qui mostriamo copertina, continente, date, descrizione e l'elenco dei
 * Capitoli del viaggio con, sotto ciascuno, i racconti (post) collegati.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$tb_viaggio_id  = get_the_ID();
	$tb_author_id   = get_the_author_meta( 'ID' );
	$tb_author_name = get_the_author_meta( 'display_name' );
	$tb_trip_meta   = tb_diary_trip_meta( $tb_viaggio_id );

	// travelblogs_get_*() del mu-plugin restituiscono WP_Query: serve ->posts.
	$tb_capitoli = function_exists( 'travelblogs_get_capitoli_di_viaggio' )
		? travelblogs_get_capitoli_di_viaggio( $tb_viaggio_id )->posts
		: array();

	$tb_has_content = '' !== trim( wp_strip_all_tags( get_the_content() ) );
	?>

	<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

		<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:10px;">
			<h1 style="font-size:34px;"><?php the_title(); ?></h1>
			<div style="font-size:13px;color:#8A8D94;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
				<a href="<?php echo esc_url( get_post_type_archive_link( 'viaggio' ) ); ?>"><?php esc_html_e( 'Diari', 'travelblogs' ); ?></a> ›
				<a href="<?php echo esc_url( get_author_posts_url( $tb_author_id ) ); ?>"><?php echo esc_html( $tb_author_name ); ?></a>
			</div>
		</div>

		<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
			<?php if ( $tb_trip_meta['continent'] ) : ?>
				<?php tb_cat_pill( $tb_trip_meta['continent'], $tb_trip_meta['continent'] ); ?>
			<?php endif; ?>
			<?php if ( $tb_trip_meta['start'] ) : ?>
				<span style="font-size:12px;color:#8A8D94;"><?php tb_diary_trip_dates_html( $tb_viaggio_id ); ?></span>
			<?php endif; ?>
		</div>
		<div class="tb-title-bar"></div>

		<div class="tb-layout">

			<div class="tb-col-main">

				<div style="margin-bottom:28px;">
					<?php tb_diary_cover_html( $tb_viaggio_id, 'tb-hero', array( 'class' => 'tb-photo', 'style' => 'width:100%;height:auto;aspect-ratio:16/7;object-fit:cover;' ) ); ?>
				</div>

				<?php if ( $tb_has_content ) : ?>
					<div class="tb-article-body" style="margin-bottom:32px;"><?php the_content(); ?></div>
				<?php elseif ( has_excerpt() ) : ?>
					<p style="font-size:16px;line-height:1.7;color:#3A3D42;margin:0 0 32px;"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="tb-secbar" style="margin-bottom:24px;"><?php esc_html_e( 'All Diary Chapters', 'travelblogs' ); ?></div>

				<?php if ( empty( $tb_capitoli ) ) : ?>
					<p style="color:#5B5E64;"><?php esc_html_e( 'Questo diario non ha ancora capitoli.', 'travelblogs' ); ?></p>
				<?php else : ?>
					<div style="display:flex;flex-direction:column;gap:32px;">
						<?php foreach ( $tb_capitoli as $tb_i => $tb_cap ) :
							$tb_cap_posts = function_exists( 'travelblogs_get_post_di_capitolo' )
								? travelblogs_get_post_di_capitolo( $tb_cap->ID )->posts
								: array();
							?>
							<div>
								<div style="display:flex;gap:20px;flex-wrap:wrap;">
									<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>" style="flex-shrink:0;">
										<?php if ( has_post_thumbnail( $tb_cap->ID ) ) : ?>
											<?php echo get_the_post_thumbnail( $tb_cap->ID, 'tb-card', array( 'class' => 'tb-photo', 'style' => 'width:220px;height:150px;' ) ); ?>
										<?php else : ?>
											<span class="tb-photo" style="width:220px;height:150px;display:block;background:#EDEBE5;"></span>
										<?php endif; ?>
									</a>
									<div style="flex:1 1 260px;min-width:0;">
										<div style="font-size:11px;color:var(--tb-accent,#D6272E);font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
											<?php printf( esc_html__( 'Capitolo %d', 'travelblogs' ), (int) ( $tb_i + 1 ) ); ?>
										</div>
										<h3 style="font-size:19px;margin-bottom:6px;">
											<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>"><?php echo esc_html( get_the_title( $tb_cap ) ); ?></a>
										</h3>
										<div style="font-size:12px;color:#8A8D94;margin-bottom:8px;"><?php echo esc_html( get_the_date( '', $tb_cap ) ); ?></div>
										<p style="font-size:14px;color:#5B5E64;line-height:1.6;margin:0 0 10px;"><?php echo esc_html( wp_trim_words( get_the_excerpt( $tb_cap ), 28 ) ); ?></p>
										<a class="tb-readmore" href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
									</div>
								</div>

								<?php if ( ! empty( $tb_cap_posts ) ) : ?>
									<ul style="list-style:none;margin:14px 0 0;padding:0 0 0 12px;border-left:3px solid #ECEAE4;display:flex;flex-direction:column;gap:6px;">
										<?php foreach ( $tb_cap_posts as $tb_p ) : ?>
											<li style="font-size:14px;">
												<a href="<?php echo esc_url( get_permalink( $tb_p ) ); ?>" style="font-weight:600;"><?php echo esc_html( get_the_title( $tb_p ) ); ?></a>
												<span style="font-size:12px;color:#8A8D94;"> · <?php echo esc_html( get_the_date( '', $tb_p ) ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>

			<div class="tb-sidebar">
				<?php tb_widget_recent( 'post', __( 'Ultimi racconti', 'travelblogs' ), 3, true ); ?>
				<?php if ( count( $tb_capitoli ) > 1 ) : ?>
					<div class="tb-widget-card">
						<div class="tb-widget-title" style="margin-bottom:16px;"><?php esc_html_e( 'Chapters', 'travelblogs' ); ?></div>
						<div style="display:flex;flex-direction:column;gap:1px;background:#ECEAE4;">
							<?php foreach ( $tb_capitoli as $tb_cap ) : ?>
								<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>" style="background:#fff;padding:12px 14px;font-size:14px;font-weight:600;"><?php echo esc_html( get_the_title( $tb_cap ) ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
endwhile;

get_footer();
