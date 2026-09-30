<?php
/**
 * Template Capitolo singolo (single-capitolo.php) — riprende DiaryChapter.dc.html:
 * banner mappa decorativo, chip delle tappe del viaggio, elenco capitoli,
 * sidebar con galleria/ultimi racconti/elenco tappe.
 *
 * NOTA sul banner mappa: come nel mockup, è un elemento puramente decorativo
 * (gradiente CSS + pin posizionati in modo regolare lungo una polilinea),
 * non un'integrazione con coordinate GPS reali — il modello dati migrato
 * non ha ancora campi di geolocalizzazione per viaggio/capitolo. I dati
 * REALI usati sono titolo, data e ordine dei capitoli del viaggio; se in
 * futuro si aggiungono coordinate reali, questo banner può diventare una
 * vera mappa (es. Google Maps / Leaflet) senza cambiare il resto del template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$tb_capitolo_id = get_the_ID();
	$tb_viaggio_id  = get_post_meta( $tb_capitolo_id, '_viaggio_id', true );
	$tb_viaggio     = $tb_viaggio_id ? get_post( $tb_viaggio_id ) : null;

	// Tutti i capitoli dello stesso viaggio, in ordine cronologico — usati sia
	// per il banner "mappa" che per i chip e l'elenco "All Diary Chapters".
	$tb_capitoli = $tb_viaggio_id && function_exists( 'travelblogs_get_capitoli_di_viaggio' )
		? travelblogs_get_capitoli_di_viaggio( $tb_viaggio_id )
		: array( get_post( $tb_capitolo_id ) );

	if ( empty( $tb_capitoli ) ) {
		$tb_capitoli = array( get_post( $tb_capitolo_id ) );
	}

	$tb_total = count( $tb_capitoli );

	$tb_dest_terms = get_the_terms( $tb_capitolo_id, 'destinazione' );
	$tb_dest_label = ( ! is_wp_error( $tb_dest_terms ) && ! empty( $tb_dest_terms ) ) ? $tb_dest_terms[0]->name : '';

	// Autore "viaggiatore" per il breadcrumb — profilo dell'autore del viaggio.
	$tb_author_id   = $tb_viaggio ? $tb_viaggio->post_author : get_the_author_meta( 'ID' );
	$tb_author_name = get_the_author_meta( 'display_name', $tb_author_id );
	?>

	<!-- banner mappa (decorativo — vedi nota sopra) -->
	<div class="tb-gmap">
		<div class="tb-gmap-toggle"><span class="active">Map</span><span>Satellite</span></div>

		<?php if ( $tb_total > 1 ) : ?>
			<svg style="position:absolute;inset:0;width:100%;height:100%;" viewBox="0 0 100 100" preserveAspectRatio="none">
				<?php
				$tb_points = array();
				foreach ( $tb_capitoli as $tb_i => $tb_cap ) {
					$tb_frac = $tb_i / max( 1, $tb_total - 1 );
					$tb_points[] = ( 18 + $tb_frac * 64 ) . ',' . ( 80 - $tb_frac * 62 );
				}
				?>
				<polyline points="<?php echo esc_attr( implode( ' ', $tb_points ) ); ?>" fill="none" stroke="var(--tb-accent,#D6272E)" stroke-width="0.4" stroke-dasharray="1.4,1.4" opacity="0.85" vector-effect="non-scaling-stroke"/>
			</svg>
		<?php endif; ?>

		<?php foreach ( $tb_capitoli as $tb_i => $tb_cap ) :
			$tb_frac    = $tb_total > 1 ? $tb_i / ( $tb_total - 1 ) : 0;
			$tb_left    = 18 + $tb_frac * 64;
			$tb_top     = 80 - $tb_frac * 62;
			$tb_is_this = ( $tb_cap->ID === $tb_capitolo_id );
			?>
			<div class="tb-pin" style="left:<?php echo esc_attr( $tb_left ); ?>%;top:<?php echo esc_attr( $tb_top ); ?>%;background:<?php echo $tb_is_this ? 'var(--tb-accent,#D6272E)' : '#0F172A'; ?>;">
				<span><?php echo (int) ( $tb_i + 1 ); ?></span>
			</div>
			<?php if ( $tb_is_this ) :
				$tb_cap_dest = get_the_terms( $tb_cap->ID, 'destinazione' );
				$tb_cap_dest_label = ( ! is_wp_error( $tb_cap_dest ) && ! empty( $tb_cap_dest ) ) ? $tb_cap_dest[0]->name : '';
				?>
				<div class="tb-mapcard" style="left:<?php echo esc_attr( $tb_left ); ?>%;top:<?php echo esc_attr( $tb_top ); ?>%;transform:translate(-50%,-138%);">
					<?php if ( has_post_thumbnail( $tb_cap->ID ) ) : ?>
						<?php echo get_the_post_thumbnail( $tb_cap->ID, 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:100px;width:100%;' ) ); ?>
					<?php else : ?>
						<span class="tb-photo" style="height:100px;display:block;background:#EDEBE5;"></span>
					<?php endif; ?>
					<div style="padding:12px 14px;">
						<div style="font-size:10px;color:var(--tb-accent,#D6272E);font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">
							<?php printf( esc_html__( 'Tappa %d%s', 'travelblogs' ), (int) ( $tb_i + 1 ), $tb_cap_dest_label ? ' · ' . esc_html( $tb_cap_dest_label ) : '' ); ?>
						</div>
						<div style="font-size:14px;font-weight:700;margin-bottom:4px;"><?php echo esc_html( get_the_title( $tb_cap ) ); ?></div>
						<div style="font-size:12px;color:#7A7D84;"><?php echo esc_html( get_the_date( '', $tb_cap ) ); ?></div>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<div class="tb-gmap-zoom"><span>+</span><span>&minus;</span></div>
		<div class="tb-gmap-attrib"><?php esc_html_e( 'Map data © · Termini', 'travelblogs' ); ?></div>
	</div>

	<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

		<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:10px;">
			<h1 style="font-size:34px;"><?php echo esc_html( $tb_viaggio ? get_the_title( $tb_viaggio ) : get_the_title() ); ?></h1>
			<div style="font-size:13px;color:#8A8D94;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
				<?php esc_html_e( 'Viaggiatori', 'travelblogs' ); ?> ›
				<a href="<?php echo esc_url( get_author_posts_url( $tb_author_id ) ); ?>"><?php echo esc_html( $tb_author_name ); ?></a> ›
				<span style="color:#3A3D42;font-weight:600;"><?php echo esc_html( $tb_viaggio ? get_the_title( $tb_viaggio ) : get_the_title() ); ?></span>
			</div>
		</div>
		<?php
		$tb_trip_meta = $tb_viaggio_id ? tb_diary_trip_meta( $tb_viaggio_id ) : array(
			'continent' => '',
			'start'     => null,
			'end'       => null,
		);
		?>
		<?php if ( $tb_dest_label || $tb_trip_meta['continent'] || $tb_trip_meta['start'] ) : ?>
			<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
				<?php if ( $tb_dest_label ) : ?>
					<span style="font-size:13px;color:var(--tb-accent,#D6272E);font-weight:700;"><?php echo esc_html( $tb_dest_label ); ?></span>
				<?php endif; ?>
				<?php if ( $tb_trip_meta['continent'] ) : ?>
					<?php tb_cat_pill( $tb_trip_meta['continent'], $tb_trip_meta['continent'] ); ?>
				<?php endif; ?>
				<?php if ( $tb_trip_meta['start'] ) : ?>
					<span style="font-size:12px;color:#8A8D94;"><?php tb_diary_trip_dates_html( $tb_viaggio_id ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="tb-title-bar"></div>

		<div class="tb-layout">

			<!-- COLONNA SINISTRA -->
			<div class="tb-col-main">

				<?php if ( $tb_total > 1 ) : ?>
					<div style="display:flex;gap:16px;padding-bottom:28px;border-bottom:1px solid #ECEAE4;margin-bottom:28px;flex-wrap:wrap;">
						<?php foreach ( $tb_capitoli as $tb_cap ) :
							$tb_cap_dest = get_the_terms( $tb_cap->ID, 'destinazione' );
							$tb_cap_dest_label = ( ! is_wp_error( $tb_cap_dest ) && ! empty( $tb_cap_dest ) ) ? $tb_cap_dest[0]->name : '';
							$tb_active = ( $tb_cap->ID === $tb_capitolo_id );
							?>
							<a class="tb-chip<?php echo $tb_active ? ' active' : ''; ?>" href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>">
								<?php echo esc_html( get_the_date( '', $tb_cap ) ); ?><?php echo $tb_cap_dest_label ? ' · ' . esc_html( $tb_cap_dest_label ) : ''; ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="tb-secbar" style="margin-bottom:24px;"><?php esc_html_e( 'All Diary Chapters', 'travelblogs' ); ?></div>

				<div style="display:flex;flex-direction:column;gap:28px;">
					<?php foreach ( $tb_capitoli as $tb_cap ) : ?>
						<div style="display:flex;gap:20px;">
							<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>" style="flex-shrink:0;">
								<?php if ( has_post_thumbnail( $tb_cap->ID ) ) : ?>
									<?php echo get_the_post_thumbnail( $tb_cap->ID, 'tb-card', array( 'class' => 'tb-photo', 'style' => 'width:220px;height:150px;' ) ); ?>
								<?php else : ?>
									<span class="tb-photo" style="width:220px;height:150px;display:block;background:#EDEBE5;"></span>
								<?php endif; ?>
							</a>
							<div>
								<h3 style="font-size:19px;margin-bottom:8px;">
									<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>"><?php echo esc_html( get_the_title( $tb_cap ) ); ?></a>
								</h3>
								<div style="font-size:12px;color:#8A8D94;margin-bottom:10px;"><?php echo esc_html( get_the_date( '', $tb_cap ) ); ?></div>
								<p style="font-size:14px;color:#5B5E64;line-height:1.6;margin:0 0 12px;"><?php echo esc_html( wp_trim_words( get_the_excerpt( $tb_cap ), 28 ) ); ?></p>
								<a class="tb-readmore" href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			</div>

			<!-- SIDEBAR DESTRA -->
			<div class="tb-sidebar">

				<?php
				// Conteggio immagini: quante immagini in evidenza hanno gli
				// articoli di diario collegati a questo capitolo.
				$tb_diary_posts = function_exists( 'travelblogs_get_post_di_capitolo' )
					? travelblogs_get_post_di_capitolo( $tb_capitolo_id )
					: array();
				$tb_gallery_count = 0;
				foreach ( $tb_diary_posts as $tb_dp ) {
					if ( has_post_thumbnail( $tb_dp ) ) {
						$tb_gallery_count++;
					}
					$tb_gallery_count += count( get_attached_media( 'image', $tb_dp ) );
				}
				if ( $tb_gallery_count > 0 ) :
					?>
					<div class="tb-widget-card">
						<div class="tb-secbar" style="margin-bottom:16px;"><?php esc_html_e( 'Diary Image Galleries', 'travelblogs' ); ?></div>
						<?php if ( has_post_thumbnail( $tb_capitolo_id ) ) : ?>
							<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:150px;width:100%;margin-bottom:14px;border-radius:8px;' ) ); ?>
						<?php endif; ?>
						<div style="font-size:32px;font-weight:800;font-family:'Archivo',sans-serif;color:var(--tb-accent,#D6272E);line-height:1;"><?php echo (int) $tb_gallery_count; ?></div>
						<div style="font-size:13px;color:#5B5E64;margin-top:8px;"><?php esc_html_e( 'Click here to see all pictures related to this diary', 'travelblogs' ); ?></div>
					</div>
				<?php endif; ?>

				<?php tb_widget_recent( 'post', __( 'Ultimi racconti', 'travelblogs' ), 3, true ); ?>

				<?php if ( $tb_total > 1 ) : ?>
					<div class="tb-widget-card">
						<div class="tb-widget-title" style="margin-bottom:16px;"><?php esc_html_e( 'Chapters', 'travelblogs' ); ?></div>
						<div style="display:flex;flex-direction:column;gap:1px;background:#ECEAE4;">
							<?php foreach ( $tb_capitoli as $tb_cap ) : ?>
								<a href="<?php echo esc_url( get_permalink( $tb_cap ) ); ?>" style="background:#fff;padding:12px 14px;font-size:14px;font-weight:600;<?php echo ( $tb_cap->ID === $tb_capitolo_id ) ? 'color:var(--tb-accent,#D6272E);' : ''; ?>">
									<?php echo esc_html( get_the_title( $tb_cap ) ); ?>
								</a>
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
