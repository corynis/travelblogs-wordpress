<?php
/**
 * Template Post di diario singolo (single.php) — riprende Post.dc.html:
 * sidebar sinistra sticky (autore, condivisione, articolo prec/succ),
 * mappa inline al posto dell'hero (con placeholder del capitolo/destinazione
 * collegato), corpo articolo, sidebar destra (Destinations, Recent Stories).
 *
 * NOTA sulla mappa: come nel mockup, il pin è decorativo (nessuna
 * coordinata GPS reale nel modello dati) — mostra però il vero titolo
 * dell'articolo e la vera destinazione collegata.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$tb_post_id    = get_the_ID();
	$tb_terms      = get_the_terms( $tb_post_id, 'destinazione' );
	$tb_dest_label = ( ! is_wp_error( $tb_terms ) && ! empty( $tb_terms ) ) ? $tb_terms[0]->name : '';

	$tb_author_id = get_the_author_meta( 'ID' );

	$tb_prev = get_previous_post();
	$tb_next = get_next_post();

	$tb_word_count   = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$tb_reading_mins = max( 1, (int) ceil( $tb_word_count / 200 ) );
	?>

	<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

		<div class="tb-layout" style="align-items:flex-start;">

			<!-- SIDEBAR SINISTRA STICKY -->
			<div class="tb-author-side" style="flex:0 0 200px;">

				<div class="tb-author-card">
					<?php echo get_avatar( $tb_author_id, 64, '', get_the_author(), array( 'class' => 'tb-photo tb-author-avatar' ) ); ?>
					<div>
						<div class="tb-author-label"><?php esc_html_e( 'Written by', 'travelblogs' ); ?></div>
						<a class="tb-author-name" href="<?php echo esc_url( get_author_posts_url( $tb_author_id ) ); ?>"><?php the_author(); ?></a>
					</div>
				</div>

				<div class="tb-share-row">
					<a class="tb-share" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="Facebook">f</a>
					<a class="tb-share" href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="X">&#10005;</a>
					<span class="tb-share" aria-hidden="true">&#9686;</span>
					<a class="tb-share" href="mailto:?body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Email', 'travelblogs' ); ?>">&#9993;</a>
				</div>

				<?php if ( $tb_prev || $tb_next ) : ?>
					<div class="tb-postnav">
						<?php if ( $tb_prev ) : ?>
							<a href="<?php echo esc_url( get_permalink( $tb_prev ) ); ?>">
								<?php if ( has_post_thumbnail( $tb_prev ) ) : ?>
									<?php echo get_the_post_thumbnail( $tb_prev, 'tb-thumb', array( 'class' => 'tb-photo' ) ); ?>
								<?php else : ?>
									<span class="tb-photo" style="background:#EDEBE5;"></span>
								<?php endif; ?>
								<div>
									<div class="tb-postnav-eyebrow">‹ <?php esc_html_e( 'Precedente', 'travelblogs' ); ?></div>
									<div class="tb-postnav-title"><?php echo esc_html( get_the_title( $tb_prev ) ); ?></div>
								</div>
							</a>
						<?php endif; ?>
						<?php if ( $tb_next ) : ?>
							<a href="<?php echo esc_url( get_permalink( $tb_next ) ); ?>">
								<?php if ( has_post_thumbnail( $tb_next ) ) : ?>
									<?php echo get_the_post_thumbnail( $tb_next, 'tb-thumb', array( 'class' => 'tb-photo' ) ); ?>
								<?php else : ?>
									<span class="tb-photo" style="background:#EDEBE5;"></span>
								<?php endif; ?>
								<div>
									<div class="tb-postnav-eyebrow"><?php esc_html_e( 'Successivo', 'travelblogs' ); ?> ›</div>
									<div class="tb-postnav-title"><?php echo esc_html( get_the_title( $tb_next ) ); ?></div>
								</div>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- CONTENUTO CENTRALE -->
			<div class="tb-col-main" style="flex:1 1 700px;">

				<div class="tb-gmap tb-gmap--inline">
					<div class="tb-gmap-toggle"><span class="active">Map</span><span>Satellite</span></div>

					<div class="tb-pin" style="left:50%;top:46%;"><span>1</span></div>

					<div class="tb-mapcard" style="left:50%;top:46%;transform:translate(-50%,-138%);">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'height:100px;width:100%;' ) ); ?>
						<?php else : ?>
							<span class="tb-photo" style="height:100px;display:block;background:#EDEBE5;"></span>
						<?php endif; ?>
						<div class="tb-mapcard-body">
							<?php if ( $tb_dest_label ) : ?>
								<div class="tb-mapcard-kicker"><?php echo esc_html( $tb_dest_label ); ?></div>
							<?php endif; ?>
							<div class="tb-mapcard-title"><?php the_title(); ?></div>
						</div>
					</div>

					<div class="tb-gmap-zoom"><span>+</span><span>&minus;</span></div>
					<div class="tb-gmap-attrib"><?php esc_html_e( 'Map data © · Termini', 'travelblogs' ); ?></div>
				</div>

				<?php if ( $tb_dest_label ) : ?>
					<div style="margin-bottom:14px;"><?php tb_cat_pill( $tb_dest_label, $tb_dest_label ); ?></div>
				<?php endif; ?>
				<h1 style="font-size:34px;line-height:1.22;margin-bottom:14px;"><?php the_title(); ?></h1>
				<div style="font-size:13px;color:#8A8D94;margin-bottom:28px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
					<span><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'fa', 'travelblogs' ); ?></span>
					<span>·</span>
					<span><?php printf( esc_html__( '%d min di lettura', 'travelblogs' ), (int) $tb_reading_mins ); ?></span>
					<?php if ( comments_open() ) : ?>
						<span>·</span>
						<a href="<?php comments_link(); ?>"><?php esc_html_e( 'Aggiungi commento', 'travelblogs' ); ?></a>
					<?php endif; ?>
				</div>

				<div class="tb-body">
					<?php the_content(); ?>
				</div>

				<?php
				$tb_gallery = get_attached_media( 'image', $tb_post_id );
				if ( ! empty( $tb_gallery ) ) :
					?>
					<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:8px;">
						<?php foreach ( array_slice( $tb_gallery, 0, 3 ) as $tb_img ) : ?>
							<?php echo wp_get_attachment_image( $tb_img->ID, 'tb-card', false, array( 'class' => 'tb-photo', 'style' => 'height:160px;width:100%;border-radius:8px;' ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- SIDEBAR DESTRA -->
			<div class="tb-sidebar">
				<?php
				tb_widget_destinations();
				tb_widget_recent( 'post', __( 'Recent Stories', 'travelblogs' ), 3, true );
				?>
			</div>
		</div>
	</div>

	<?php
endwhile;

get_footer();
