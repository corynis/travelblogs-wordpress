<?php
/**
 * Pagina Foto (page-foto.php) — riprende Foto.dc.html: griglia di gallerie
 * fotografiche raggruppate per capitolo di viaggio, con conteggio immagini.
 *
 * Si attiva automaticamente su una Pagina WordPress con slug "foto"
 * (convenzione page-{slug}.php di WordPress) — crea una Pagina con quello
 * slug in Aspetto → Pagine se non esiste già, il menu può già puntarci.
 *
 * Il conteggio foto per galleria è reale: immagine in evidenza del
 * capitolo + immagini in evidenza/allegate degli articoli di diario
 * collegati (via travelblogs_get_post_di_capitolo(), mu-plugin CPT).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$tb_paged = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) );

$tb_has_capitolo = post_type_exists( 'capitolo' );
$tb_galleries    = array();

if ( $tb_has_capitolo ) {
	$tb_capitoli_query = new WP_Query(
		array(
			'post_type'      => 'capitolo',
			'posts_per_page' => 9,
			'paged'          => $tb_paged,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	while ( $tb_capitoli_query->have_posts() ) {
		$tb_capitoli_query->the_post();
		$tb_cap_id = get_the_ID();

		$tb_count = has_post_thumbnail( $tb_cap_id ) ? 1 : 0;
		$tb_count += count( get_attached_media( 'image', $tb_cap_id ) );

		if ( function_exists( 'travelblogs_get_post_di_capitolo' ) ) {
			foreach ( travelblogs_get_post_di_capitolo( $tb_cap_id ) as $tb_dp ) {
				$tb_count += has_post_thumbnail( $tb_dp ) ? 1 : 0;
				$tb_count += count( get_attached_media( 'image', $tb_dp ) );
			}
		}

		$tb_dest_terms = get_the_terms( $tb_cap_id, 'destinazione' );

		$tb_galleries[] = array(
			'id'       => $tb_cap_id,
			'title'    => get_the_title(),
			'link'     => get_permalink(),
			'subtitle' => ( ! is_wp_error( $tb_dest_terms ) && ! empty( $tb_dest_terms ) ) ? $tb_dest_terms[0]->name : '',
			'count'    => $tb_count,
		);
	}
	wp_reset_postdata();
}
?>

<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:10px;">
		<h1 style="font-size:34px;"><?php esc_html_e( 'Foto', 'travelblogs' ); ?></h1>
		<div style="font-size:13px;color:#8A8D94;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
			<span style="color:#3A3D42;font-weight:600;"><?php esc_html_e( 'Foto', 'travelblogs' ); ?></span>
		</div>
	</div>
	<div style="font-size:14px;color:#5B5E64;margin-bottom:28px;"><?php esc_html_e( 'Tutte le gallerie fotografiche, raggruppate per capitolo di viaggio.', 'travelblogs' ); ?></div>
	<div class="tb-title-bar"></div>

	<?php if ( ! $tb_has_capitolo ) : ?>

		<p style="color:#5B5E64;"><?php esc_html_e( 'Il tipo di contenuto "capitolo" non risulta ancora registrato: questa pagina si popolerà automaticamente non appena sarà disponibile.', 'travelblogs' ); ?></p>

	<?php elseif ( empty( $tb_galleries ) ) : ?>

		<p style="color:#5B5E64;"><?php esc_html_e( 'Nessuna galleria disponibile ancora.', 'travelblogs' ); ?></p>

	<?php else : ?>

		<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:28px 24px;margin-bottom:36px;">
			<?php foreach ( $tb_galleries as $tb_g ) : ?>
				<div>
					<a href="<?php echo esc_url( $tb_g['link'] ); ?>" style="display:block;position:relative;">
						<?php if ( has_post_thumbnail( $tb_g['id'] ) ) : ?>
							<?php echo get_the_post_thumbnail( $tb_g['id'], 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:210px;width:100%;margin-bottom:12px;' ) ); ?>
						<?php else : ?>
							<span class="tb-photo" style="height:210px;display:block;margin-bottom:12px;background:#EDEBE5;"></span>
						<?php endif; ?>
						<div class="tb-gcount"><?php printf( esc_html__( '%d foto', 'travelblogs' ), (int) $tb_g['count'] ); ?></div>
					</a>
					<h3 style="font-size:16px;margin-bottom:4px;">
						<a href="<?php echo esc_url( $tb_g['link'] ); ?>"><?php echo esc_html( $tb_g['title'] ); ?></a>
					</h3>
					<?php if ( $tb_g['subtitle'] ) : ?>
						<div style="font-size:12px;color:#8A8D94;"><?php echo esc_html( $tb_g['subtitle'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div style="display:flex;gap:8px;flex-wrap:wrap;">
			<?php
			$tb_links = paginate_links(
				array(
					'base'      => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
					'format'    => '',
					'current'   => $tb_paged,
					'total'     => $tb_capitoli_query->max_num_pages,
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

	<?php endif; ?>

</div>

<?php
get_footer();
