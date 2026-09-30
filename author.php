<?php
/**
 * Template Profilo Viaggiatore (author.php) — riprende Profile.dc.html:
 * banner mappa decorativo, card profilo autore, elenco dei suoi viaggi
 * (CPT `viaggio`), sidebar con "Countries Visited" e ultimi racconti.
 *
 * NOTA: come nel mockup, il banner mappa e i "flag" nel widget Countries
 * Visited sono decorativi — il modello dati non ha ancora un campo
 * codice-paese ISO reale, quindi l'abbreviazione mostrata è dedotta dalle
 * prime due lettere del nome del termine `destinazione` (livello Paese).
 * Il CONTEGGIO e i NOMI dei paesi sono invece reali, dedotti dai
 * termini `destinazione` di primo livello effettivamente usati sui
 * contenuti di questo autore.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$tb_author_id     = get_queried_object_id();
$tb_author_name   = get_the_author_meta( 'display_name', $tb_author_id );
$tb_author_bio    = get_the_author_meta( 'description', $tb_author_id );

// "Diari" dell'autore — CPT `viaggio` se esiste, altrimenti fallback su `post`.
$tb_diary_type  = post_type_exists( 'viaggio' ) ? 'viaggio' : 'post';
$tb_paged       = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) );

$tb_diaries_query = new WP_Query(
	array(
		'post_type'      => $tb_diary_type,
		'author'         => $tb_author_id,
		'posts_per_page' => 6,
		'paged'          => $tb_paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

// Numero totale di articoli pubblicati dall'autore (per lo stat "Articles").
$tb_articles_count = (int) count_user_posts( $tb_author_id, 'post' );
if ( post_type_exists( 'viaggio' ) ) {
	$tb_articles_count += (int) count_user_posts( $tb_author_id, 'viaggio' );
}
if ( post_type_exists( 'capitolo' ) ) {
	$tb_articles_count += (int) count_user_posts( $tb_author_id, 'capitolo' );
}

// Paesi visitati: termini `destinazione` di primo livello usati sui
// contenuti (di qualunque tipo) di questo autore.
$tb_country_terms = array();
if ( taxonomy_exists( 'destinazione' ) ) {
	$tb_author_post_ids = get_posts(
		array(
			'post_type'      => array( 'post', 'viaggio', 'capitolo' ),
			'author'         => $tb_author_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $tb_author_post_ids ) ) {
		$tb_terms_raw = wp_get_object_terms( $tb_author_post_ids, 'destinazione' );
		if ( ! is_wp_error( $tb_terms_raw ) ) {
			foreach ( $tb_terms_raw as $tb_t ) {
				$tb_top = $tb_t->parent ? get_term( $tb_t->parent, 'destinazione' ) : $tb_t;
				if ( $tb_top && ! is_wp_error( $tb_top ) ) {
					$tb_country_terms[ $tb_top->term_id ] = $tb_top;
				}
			}
		}
	}
}
$tb_country_terms = array_values( $tb_country_terms );

?>

<!-- banner mappa (decorativo — vedi nota sopra) -->
<div class="tb-gmap">
	<div class="tb-gmap-toggle"><span class="active">Map</span><span>Satellite</span></div>

	<?php
	$tb_pin_colors = array( '#6B4A9E', '#D6272E', '#2C6E9E', '#B4711F', '#2E8B57' );
	foreach ( $tb_country_terms as $tb_i => $tb_term ) :
		// Posizione pseudo-casuale ma stabile (deterministica dal nome del
		// termine), solo per non sovrapporre i pin — non è una coordinata reale.
		$tb_hash = crc32( $tb_term->name );
		$tb_left = 15 + ( $tb_hash % 75 );
		$tb_top  = 25 + ( ( $tb_hash >> 8 ) % 60 );
		$tb_size = 18 + ( $tb_term->count % 4 ) * 4;
		?>
		<div class="tb-gmap-pin" style="left:<?php echo esc_attr( $tb_left ); ?>%;top:<?php echo esc_attr( $tb_top ); ?>%;width:<?php echo esc_attr( $tb_size ); ?>px;height:<?php echo esc_attr( $tb_size ); ?>px;font-size:<?php echo esc_attr( max( 9, $tb_size - 15 ) ); ?>px;background:<?php echo esc_attr( $tb_pin_colors[ $tb_i % count( $tb_pin_colors ) ] ); ?>;">
			<?php echo (int) $tb_term->count; ?>
		</div>
	<?php endforeach; ?>

	<div class="tb-gmap-zoom"><span>+</span><span>&minus;</span></div>
	<div class="tb-gmap-attrib"><?php esc_html_e( 'Map data © · Termini', 'travelblogs' ); ?></div>
</div>

<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

	<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:10px;">
		<h1 style="font-size:34px;"><?php echo esc_html( $tb_author_name ); ?></h1>
		<div style="font-size:13px;color:#8A8D94;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'travelblogs' ); ?></a> ›
			<?php esc_html_e( 'Travelers', 'travelblogs' ); ?> ›
			<span style="color:#3A3D42;font-weight:600;"><?php echo esc_html( $tb_author_name ); ?></span>
		</div>
	</div>
	<div class="tb-title-bar"></div>

	<div class="tb-layout">

		<!-- COLONNA SINISTRA -->
		<div class="tb-col-main">

			<!-- profile card -->
			<div style="display:flex;gap:26px;margin-bottom:40px;flex-wrap:wrap;">
				<?php echo get_avatar( $tb_author_id, 150, '', $tb_author_name, array( 'class' => 'tb-photo', 'style' => 'border-radius:50%;flex-shrink:0;' ) ); ?>
				<div style="flex-grow:1;min-width:240px;">
					<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
						<div>
							<div style="font-size:13px;color:#8A8D94;text-transform:uppercase;letter-spacing:.05em;"><?php esc_html_e( 'Traveler', 'travelblogs' ); ?></div>
							<h2 style="font-size:26px;margin:2px 0 10px;"><?php echo esc_html( $tb_author_name ); ?></h2>
						</div>
					</div>
					<?php if ( $tb_author_bio ) : ?>
						<p style="font-size:14px;color:#5B5E64;line-height:1.7;max-width:520px;margin:0 0 14px;"><?php echo esc_html( $tb_author_bio ); ?></p>
					<?php endif; ?>
					<div style="display:flex;gap:36px;">
						<div>
							<div style="font-size:22px;font-weight:700;font-family:'Archivo',sans-serif;"><?php echo (int) $tb_articles_count; ?></div>
							<div style="font-size:12px;color:#8A8D94;text-transform:uppercase;letter-spacing:.04em;"><?php esc_html_e( 'Articles', 'travelblogs' ); ?></div>
						</div>
					</div>
				</div>
			</div>

			<!-- diari -->
			<?php if ( $tb_diaries_query->have_posts() ) : ?>
				<div class="tb-secbar" style="margin-bottom:24px;">
					<?php printf( esc_html__( 'Diaries By %1$s (%2$d)', 'travelblogs' ), esc_html( $tb_author_name ), (int) $tb_diaries_query->found_posts ); ?>
				</div>
				<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:28px 24px;margin-bottom:28px;">
					<?php
					while ( $tb_diaries_query->have_posts() ) :
						$tb_diaries_query->the_post();
						$tb_terms = get_the_terms( get_the_ID(), 'destinazione' );
						?>
						<div>
							<a href="<?php the_permalink(); ?>">
								<?php
								// Stessa logica di archive-viaggio.php: il Diario non ha
								// mai avuto una copertina propria, quindi si prende quella
								// di un Capitolo collegato (vedi tb_diary_cover_html()).
								tb_diary_cover_html( get_the_ID(), 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:200px;width:100%;margin-bottom:14px;' ) );
								?>
							</a>
							<h3 style="font-size:18px;margin-bottom:10px;">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;flex-wrap:wrap;">
								<?php if ( ! is_wp_error( $tb_terms ) && ! empty( $tb_terms ) ) { tb_cat_pill( $tb_terms[0]->name, $tb_terms[0]->name ); } ?>
								<span style="font-size:12px;color:#8A8D94;"><?php echo esc_html( get_the_date() ); ?></span>
							</div>
							<a class="tb-readmore" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
						</div>
					<?php endwhile; ?>
				</div>

				<div style="display:flex;gap:8px;">
					<?php
					$tb_links = paginate_links(
						array(
							'base'      => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
							'format'    => '',
							'current'   => $tb_paged,
							'total'     => $tb_diaries_query->max_num_pages,
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
			<?php wp_reset_postdata(); ?>

		</div>

		<!-- SIDEBAR DESTRA -->
		<div class="tb-sidebar">

			<?php if ( ! empty( $tb_country_terms ) ) : ?>
				<div class="tb-widget-card">
					<div class="tb-secbar" style="margin-bottom:20px;"><?php esc_html_e( 'Countries Visited', 'travelblogs' ); ?></div>
					<div style="margin-bottom:8px;">
						<div style="font-size:40px;font-weight:800;font-family:'Archivo',sans-serif;color:var(--tb-accent,#D6272E);line-height:1;margin-bottom:4px;"><?php echo (int) count( $tb_country_terms ); ?></div>
						<div style="font-size:12px;color:#8A8D94;text-transform:uppercase;letter-spacing:.05em;margin-bottom:16px;"><?php esc_html_e( 'All countries visited', 'travelblogs' ); ?></div>
						<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;">
							<?php foreach ( $tb_country_terms as $tb_i => $tb_term ) :
								$tb_abbr  = strtoupper( mb_substr( $tb_term->name, 0, 2 ) );
								$tb_color = $tb_pin_colors[ $tb_i % count( $tb_pin_colors ) ];
								?>
								<a href="<?php echo esc_url( get_term_link( $tb_term ) ); ?>" class="tb-flag" style="background:<?php echo esc_attr( $tb_color ); ?>;" title="<?php echo esc_attr( $tb_term->name ); ?>"><?php echo esc_html( $tb_abbr ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php tb_widget_recent( 'post', __( 'Ultimi racconti', 'travelblogs' ), 3, true, $tb_author_id ); ?>

		</div>
	</div>
</div>

<?php
get_footer();
