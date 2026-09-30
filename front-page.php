<?php
/**
 * Template Home (front-page.php) — riprende Main.dc.html del mockup:
 * hero con l'ultimo articolo di diario, "Ultimi aggiornamenti",
 * "Featured News", "Destinazioni" e la sidebar widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// "Ultimi aggiornamenti" pesca dagli articoli di diario (CPT nativo `post`,
// collegato a un capitolo via meta _capitolo_id — vedi Notion 9.9).
$tb_recent_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
$tb_recent_posts = $tb_recent_query->posts;

// Slider principale sotto il menu — riprende le ultime News, come nella
// versione precedente del sito (a differenza del mockup Canvas, che aveva
// un hero statico singolo: qui usiamo dati reali e rotanti).
$tb_slider_query = new WP_Query(
	array(
		'post_type'      => 'news',
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
$tb_slides = $tb_slider_query->posts;
// Se non ci sono ancora news, ripiega sugli ultimi articoli di diario così
// lo slider non resta vuoto.
if ( empty( $tb_slides ) ) {
	$tb_slides = array_slice( $tb_recent_posts, 0, 5 );
}

$tb_news_query = new WP_Query(
	array(
		'post_type'      => 'news',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$tb_dest_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'destinazione',
				'operator' => 'EXISTS',
			),
		),
	)
);
$tb_dest_posts   = $tb_dest_query->posts;
$tb_dest_primary = ! empty( $tb_dest_posts ) ? array_shift( $tb_dest_posts ) : null;
?>

<?php if ( ! empty( $tb_slides ) ) : ?>
<div class="tb-slider" style="width:100%;height:460px;position:relative;overflow:hidden;">
	<?php foreach ( $tb_slides as $tb_i => $tb_slide ) :
		$tb_slide_terms = get_the_terms( $tb_slide, 'destinazione' );
		if ( is_wp_error( $tb_slide_terms ) || empty( $tb_slide_terms ) ) {
			$tb_slide_terms = get_the_terms( $tb_slide, 'categoria_news' );
		}
		$tb_slide_label = ( ! is_wp_error( $tb_slide_terms ) && ! empty( $tb_slide_terms ) ) ? $tb_slide_terms[0]->name : __( 'Destinazioni', 'travelblogs' );
		?>
		<div class="tb-slide tb-photo<?php echo ( 0 === $tb_i ) ? ' is-active' : ''; ?>" style="position:absolute;inset:0;<?php echo has_post_thumbnail( $tb_slide ) ? '' : 'background-image:linear-gradient(120deg,#0B3A2E 0%,#123A55 45%,#1B4A63 100%);'; ?>">
			<?php if ( has_post_thumbnail( $tb_slide ) ) : ?>
				<div style="position:absolute;inset:0;background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $tb_slide, 'tb-hero' ) ); ?>');background-size:cover;background-position:center;"></div>
			<?php endif; ?>
			<div style="position:absolute;inset:0;background:linear-gradient(0deg,rgba(0,0,0,.65) 0%,rgba(0,0,0,.1) 55%,rgba(0,0,0,0) 100%);"></div>
			<div style="position:absolute;left:0;bottom:0;width:520px;max-width:100%;background:rgba(15,16,19,.82);color:#fff;padding:22px 26px;">
				<div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
					<?php tb_cat_pill( $tb_slide_label, $tb_slide_label ); ?>
					<span style="font-size:12px;color:#B9BCC3;"><?php echo esc_html( get_the_date( '', $tb_slide ) ); ?></span>
				</div>
				<h1 style="font-size:26px;line-height:1.25;color:#fff;">
					<a href="<?php echo esc_url( get_permalink( $tb_slide ) ); ?>" style="color:#fff;"><?php echo esc_html( get_the_title( $tb_slide ) ); ?></a>
				</h1>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ( count( $tb_slides ) > 1 ) : ?>
		<button type="button" class="tb-slider-arrow tb-slider-prev" aria-label="<?php esc_attr_e( 'Slide precedente', 'travelblogs' ); ?>">‹</button>
		<button type="button" class="tb-slider-arrow tb-slider-next" aria-label="<?php esc_attr_e( 'Slide successiva', 'travelblogs' ); ?>">›</button>
		<div class="tb-slider-dots">
			<?php foreach ( $tb_slides as $tb_i => $tb_slide ) : ?>
				<span class="tb-slider-dot<?php echo ( 0 === $tb_i ) ? ' is-active' : ''; ?>" data-slide="<?php echo (int) $tb_i; ?>"></span>
			<?php endforeach; ?>
		</div>
		<script>
		(function(){
			var root = document.currentScript.closest('.tb-slider');
			var slides = root.querySelectorAll('.tb-slide');
			var dots = root.querySelectorAll('.tb-slider-dot');
			var current = 0;
			var timer;
			function show(i){
				slides[current].classList.remove('is-active');
				dots[current].classList.remove('is-active');
				current = (i + slides.length) % slides.length;
				slides[current].classList.add('is-active');
				dots[current].classList.add('is-active');
			}
			function next(){ show(current + 1); }
			function restart(){ clearInterval(timer); timer = setInterval(next, 6000); }
			root.querySelector('.tb-slider-prev').addEventListener('click', function(){ show(current - 1); restart(); });
			root.querySelector('.tb-slider-next').addEventListener('click', function(){ show(current + 1); restart(); });
			dots.forEach(function(dot){
				dot.addEventListener('click', function(){ show(parseInt(dot.dataset.slide, 10)); restart(); });
			});
			restart();
		})();
		</script>
	<?php endif; ?>
</div>
<?php endif; ?>

<div style="max-width:1280px;margin:0 auto;padding:48px 24px;display:flex;gap:40px;align-items:flex-start;">

	<!-- COLONNA SINISTRA -->
	<div style="flex:1 1 840px;min-width:0;display:flex;flex-direction:column;gap:48px;">

		<?php if ( ! empty( $tb_recent_posts ) ) : ?>
		<div>
			<div class="tb-secbar" style="margin-bottom:20px;"><?php esc_html_e( 'Ultimi aggiornamenti', 'travelblogs' ); ?></div>
			<div style="display:flex;flex-direction:column;gap:28px;">
				<?php foreach ( $tb_recent_posts as $tb_p ) :
					$tb_terms = get_the_terms( $tb_p, 'destinazione' );
					?>
					<div style="display:flex;gap:20px;">
						<a href="<?php echo esc_url( get_permalink( $tb_p ) ); ?>" style="flex-shrink:0;">
							<?php if ( has_post_thumbnail( $tb_p ) ) : ?>
								<?php echo get_the_post_thumbnail( $tb_p, 'tb-card', array( 'class' => 'tb-photo', 'style' => 'width:220px;height:150px;' ) ); ?>
							<?php else : ?>
								<span class="tb-photo" style="width:220px;height:150px;display:block;background:#EDEBE5;"></span>
							<?php endif; ?>
						</a>
						<div>
							<h3 style="font-size:19px;margin-bottom:8px;">
								<a href="<?php echo esc_url( get_permalink( $tb_p ) ); ?>"><?php echo esc_html( get_the_title( $tb_p ) ); ?></a>
							</h3>
							<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
								<?php if ( ! is_wp_error( $tb_terms ) && ! empty( $tb_terms ) ) { tb_cat_pill( $tb_terms[0]->name, $tb_terms[0]->name ); } ?>
								<span style="font-size:12px;color:#8A8D94;"><?php echo esc_html( get_the_date( '', $tb_p ) ); ?></span>
							</div>
							<p style="font-size:14px;color:#5B5E64;line-height:1.6;margin:10px 0 12px;"><?php echo esc_html( wp_trim_words( get_the_excerpt( $tb_p ), 24 ) ); ?></p>
							<a class="tb-readmore" href="<?php echo esc_url( get_permalink( $tb_p ) ); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $tb_news_query->have_posts() ) : ?>
		<div>
			<div class="tb-secbar" style="margin-bottom:20px;"><?php esc_html_e( 'Featured News', 'travelblogs' ); ?></div>
			<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;">
				<?php $tb_i = 0; while ( $tb_news_query->have_posts() ) : $tb_news_query->the_post(); $tb_i++; ?>
					<div>
						<a href="<?php the_permalink(); ?>" style="display:block;position:relative;">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:150px;width:100%;margin-bottom:12px;' ) ); ?>
							<?php else : ?>
								<span class="tb-photo" style="height:150px;display:block;margin-bottom:12px;background:#EDEBE5;"></span>
							<?php endif; ?>
							<div style="position:absolute;top:10px;left:10px;width:26px;height:26px;background:var(--tb-accent,#D6272E);color:#fff;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;"><?php echo (int) $tb_i; ?></div>
						</a>
						<div style="font-size:14px;font-weight:600;line-height:1.4;">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</div>
						<div style="font-size:12px;color:#8A8D94;margin-top:6px;">
							<?php
							$tb_news_cats = get_the_terms( get_the_ID(), 'categoria_news' );
							echo ( ! is_wp_error( $tb_news_cats ) && ! empty( $tb_news_cats ) ) ? esc_html( $tb_news_cats[0]->name ) : esc_html__( 'News', 'travelblogs' );
							?>
							· <?php echo esc_html( get_the_date() ); ?>
						</div>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $tb_dest_primary ) : ?>
		<div>
			<div class="tb-secbar" style="margin-bottom:20px;"><?php esc_html_e( 'Destinazioni', 'travelblogs' ); ?></div>
			<div style="display:flex;gap:24px;flex-wrap:wrap;">
				<div style="flex:1 1 55%;min-width:280px;">
					<a href="<?php echo esc_url( get_permalink( $tb_dest_primary ) ); ?>">
						<?php if ( has_post_thumbnail( $tb_dest_primary ) ) : ?>
							<?php echo get_the_post_thumbnail( $tb_dest_primary, 'tb-hero', array( 'class' => 'tb-photo', 'style' => 'height:280px;width:100%;margin-bottom:14px;' ) ); ?>
						<?php else : ?>
							<span class="tb-photo" style="height:280px;display:block;margin-bottom:14px;background:#EDEBE5;"></span>
						<?php endif; ?>
					</a>
					<?php
					$tb_dp_terms = get_the_terms( $tb_dest_primary, 'destinazione' );
					tb_cat_pill( ( ! is_wp_error( $tb_dp_terms ) && ! empty( $tb_dp_terms ) ) ? $tb_dp_terms[0]->name : __( 'Destinazioni', 'travelblogs' ) );
					?>
					<h3 style="font-size:20px;line-height:1.3;margin:10px 0;">
						<a href="<?php echo esc_url( get_permalink( $tb_dest_primary ) ); ?>"><?php echo esc_html( get_the_title( $tb_dest_primary ) ); ?></a>
					</h3>
					<a class="tb-readmore" href="<?php echo esc_url( get_permalink( $tb_dest_primary ) ); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
				</div>
				<div style="flex:1 1 45%;min-width:260px;display:flex;flex-direction:column;gap:16px;">
					<?php foreach ( $tb_dest_posts as $tb_dp ) :
						$tb_dp_terms2 = get_the_terms( $tb_dp, 'destinazione' );
						?>
						<div style="display:flex;gap:14px;align-items:center;">
							<a href="<?php echo esc_url( get_permalink( $tb_dp ) ); ?>" style="flex-shrink:0;">
								<?php if ( has_post_thumbnail( $tb_dp ) ) : ?>
									<?php echo get_the_post_thumbnail( $tb_dp, 'tb-thumb', array( 'class' => 'tb-photo', 'style' => 'width:88px;height:66px;' ) ); ?>
								<?php else : ?>
									<span class="tb-photo" style="width:88px;height:66px;display:block;background:#EDEBE5;"></span>
								<?php endif; ?>
							</a>
							<div>
								<?php tb_cat_pill( ( ! is_wp_error( $tb_dp_terms2 ) && ! empty( $tb_dp_terms2 ) ) ? $tb_dp_terms2[0]->name : __( 'Destinazioni', 'travelblogs' ) ); ?>
								<div style="font-size:14px;font-weight:600;margin-top:6px;line-height:1.35;">
									<a href="<?php echo esc_url( get_permalink( $tb_dp ) ); ?>"><?php echo esc_html( get_the_title( $tb_dp ) ); ?></a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
					<?php
					// Link "More from Destinazioni": punta all'archivio del primo
					// termine reale disponibile (quello del post principale, o in
					// mancanza del primo termine di primo livello); se non esiste
					// alcun termine `destinazione`, il link viene omesso invece di
					// generare un WP_Error (get_term_link() richiede un termine
					// vero, non il nome della tassonomia).
					$tb_dest_link_term = ( ! is_wp_error( $tb_dp_terms ) && ! empty( $tb_dp_terms ) ) ? $tb_dp_terms[0] : null;
					if ( ! $tb_dest_link_term ) {
						$tb_dest_top_terms = get_terms( array( 'taxonomy' => 'destinazione', 'parent' => 0, 'hide_empty' => true, 'number' => 1 ) );
						if ( ! is_wp_error( $tb_dest_top_terms ) && ! empty( $tb_dest_top_terms ) ) {
							$tb_dest_link_term = $tb_dest_top_terms[0];
						}
					}
					$tb_dest_link_url = $tb_dest_link_term ? get_term_link( $tb_dest_link_term ) : '';
					if ( $tb_dest_link_url && ! is_wp_error( $tb_dest_link_url ) ) :
						?>
						<a href="<?php echo esc_url( $tb_dest_link_url ); ?>" style="font-size:13px;font-weight:700;color:var(--tb-accent,#D6272E);text-transform:uppercase;letter-spacing:.03em;">
							<?php esc_html_e( 'More from Destinazioni ›', 'travelblogs' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

	</div>

	<!-- SIDEBAR DESTRA -->
	<div class="tb-sidebar">
		<?php
		tb_widget_recent( 'news', __( 'News', 'travelblogs' ), 1 );

		if ( post_type_exists( 'video' ) ) {
			tb_widget_recent( 'video', __( 'Featured Videos', 'travelblogs' ), 1 );
		}

		tb_widget_destinations();
		tb_widget_news_categories();
		tb_widget_tags();
		?>
	</div>

</div>

<?php
wp_reset_postdata();
get_footer();
