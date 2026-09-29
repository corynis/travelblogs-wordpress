<?php
/**
 * Widget di sidebar riutilizzabili — ciascuno stampa un .tb-widget-card
 * completo (titolo + contenuto), pensati per stare dentro un contenitore
 * .tb-sidebar (sfondo grigio, vedi assets/css/travelblogs.css).
 *
 * Tutti gli helper sono no-op silenziosi se la tassonomia/CPT collegato
 * non ha ancora termini/contenuti (niente notice, niente markup vuoto).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget "Destinations": termini di primo livello della tassonomia
 * `destinazione`, con conteggio post e pallino colorato per continente
 * (vedi tb_continent_slug_for_term() in functions.php).
 */
function tb_widget_destinations() {
	if ( ! taxonomy_exists( 'destinazione' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'destinazione',
			'parent'     => 0,
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$dot_colors = array(
		'sud-america' => 'var(--tb-c-sudamerica)',
		'asia'        => 'var(--tb-c-asia)',
		'africa'      => 'var(--tb-c-africa)',
		'europa'      => 'var(--tb-c-europa)',
		'oceania'     => 'var(--tb-c-oceania)',
	);
	?>
	<div class="tb-widget-card">
		<div class="tb-widget-title"><?php esc_html_e( 'Destinations', 'travelblogs' ); ?></div>
		<div>
			<?php foreach ( $terms as $term ) :
				$slug  = tb_continent_slug_for_term( $term->name );
				$color = isset( $dot_colors[ $slug ] ) ? $dot_colors[ $slug ] : '#8A8D94';
				?>
				<div class="tb-destrow">
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" style="display:flex;align-items:center;">
						<span class="tb-dot" style="background:<?php echo esc_attr( $color ); ?>"></span>
						<?php echo esc_html( $term->name ); ?>
					</a>
					<span class="tb-destcount"><?php echo esc_html( $term->count ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Widget "News Categories": tassonomia `categoria_news`.
 */
function tb_widget_news_categories() {
	if ( ! taxonomy_exists( 'categoria_news' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'categoria_news',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}
	?>
	<div class="tb-widget-card">
		<div class="tb-secbar"><?php esc_html_e( 'News Categories', 'travelblogs' ); ?></div>
		<div>
			<?php foreach ( $terms as $i => $term ) :
				$is_last = ( $i === count( $terms ) - 1 );
				?>
				<div style="display:flex;justify-content:space-between;padding:10px 0;<?php echo $is_last ? '' : 'border-bottom:1px solid var(--tb-border);'; ?>font-size:14px;">
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
					<span style="color:#8A8D94;"><?php echo esc_html( $term->count ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Widget "Tags": tag liberi sui post (post_tag).
 */
function tb_widget_tags( $limit = 12 ) {
	$tags = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'number'     => $limit,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $tags ) || empty( $tags ) ) {
		return;
	}
	?>
	<div class="tb-widget-card">
		<div class="tb-widget-title"><?php esc_html_e( 'Tags', 'travelblogs' ); ?></div>
		<div style="display:flex;flex-wrap:wrap;gap:8px;">
			<?php foreach ( $tags as $tag ) : ?>
				<a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" style="border:1px solid #DAD8D1;padding:6px 12px;font-size:12px;color:#3A3D42;border-radius:16px;">
					<?php echo esc_html( $tag->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Widget "News" / "Recent Stories": ultimi N articoli (news o post),
 * con thumb + titolo (+ pillola destinazione, se richiesta).
 *
 * @param string $post_type   'news' oppure 'post'.
 * @param string $widget_title
 * @param int    $count
 * @param bool   $with_cat_pill  mostra la pillola destinazione sotto il titolo.
 */
function tb_widget_recent( $post_type, $widget_title, $count = 3, $with_cat_pill = false ) {
	$items = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => $count,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	if ( empty( $items ) ) {
		return;
	}
	?>
	<div class="tb-widget-card">
		<div class="tb-widget-title"><?php echo esc_html( $widget_title ); ?></div>
		<div style="display:flex;flex-direction:column;gap:16px;">
			<?php foreach ( $items as $item ) : ?>
				<div style="display:flex;gap:12px;">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" style="flex-shrink:0;">
						<?php if ( has_post_thumbnail( $item ) ) : ?>
							<?php echo get_the_post_thumbnail( $item, 'tb-thumb', array( 'class' => 'tb-photo', 'style' => 'width:76px;height:56px;border-radius:6px;' ) ); ?>
						<?php else : ?>
							<span class="tb-photo" style="width:76px;height:56px;border-radius:6px;display:block;"></span>
						<?php endif; ?>
					</a>
					<div>
						<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" style="font-size:13px;font-weight:600;line-height:1.4;display:block;margin-bottom:4px;">
							<?php echo esc_html( get_the_title( $item ) ); ?>
						</a>
						<?php
						if ( $with_cat_pill ) {
							$terms = get_the_terms( $item, 'destinazione' );
							if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
								tb_cat_pill( $terms[0]->name, $terms[0]->name );
							}
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
