<?php
/**
 * Archivio Diari (archive-viaggio.php) — riprende DiaryArchive.dc.html:
 * elenco dei viaggi (CPT `viaggio`), paginazione, sidebar "Latest Posts".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:10px;">
		<h1 style="font-size:34px;"><?php esc_html_e( 'Diaries List', 'travelblogs' ); ?></h1>
		<div style="font-size:13px;color:#8A8D94;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
			<span style="color:#3A3D42;font-weight:600;"><?php esc_html_e( 'Diari', 'travelblogs' ); ?></span>
		</div>
	</div>
	<div class="tb-title-bar"></div>

	<div class="tb-layout">

		<!-- LISTA DIARI -->
		<div class="tb-col-main" style="display:flex;flex-direction:column;gap:32px;">

			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div style="display:flex;gap:24px;flex-wrap:wrap;">
						<a href="<?php the_permalink(); ?>" style="flex-shrink:0;">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'width:280px;height:190px;' ) ); ?>
							<?php else : ?>
								<span class="tb-photo" style="width:280px;height:190px;display:block;background:#EDEBE5;"></span>
							<?php endif; ?>
						</a>
						<div style="flex:1 1 380px;min-width:0;">
							<h3 style="font-size:21px;margin:0 0 8px;">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<div style="font-size:12px;color:#8A8D94;margin-bottom:10px;"><?php echo esc_html( get_the_date() ); ?></div>
							<p style="font-size:14px;color:#5B5E64;line-height:1.6;margin:0 0 10px;"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
							<a class="tb-readmore" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
						</div>
					</div>
				<?php endwhile; ?>

				<div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
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
				<p style="color:#5B5E64;"><?php esc_html_e( 'Nessun diario pubblicato ancora.', 'travelblogs' ); ?></p>
			<?php endif; ?>

		</div>

		<!-- SIDEBAR -->
		<div class="tb-sidebar">
			<?php tb_widget_recent( 'post', __( 'Latest Posts', 'travelblogs' ), 2 ); ?>
		</div>
	</div>
</div>

<?php
get_footer();
