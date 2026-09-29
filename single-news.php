<?php
/**
 * Template News singola (single-news.php) — riprende NewsSingle.dc.html:
 * hero, autore/editor, corpo articolo, sidebar categorie, tag in fondo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$tb_news_id   = get_the_ID();
	$tb_cats      = get_the_terms( $tb_news_id, 'categoria_news' );
	$tb_cat_label = ( ! is_wp_error( $tb_cats ) && ! empty( $tb_cats ) ) ? $tb_cats[0]->name : __( 'News', 'travelblogs' );
	$tb_tags      = get_the_terms( $tb_news_id, 'post_tag' );
	?>

	<div style="max-width:1280px;margin:0 auto;padding:36px 24px 60px;">

		<div style="margin-bottom:6px;"><?php tb_cat_pill( $tb_cat_label ); ?></div>
		<h1 style="font-size:32px;line-height:1.25;margin:12px 0 12px;max-width:820px;"><?php the_title(); ?></h1>
		<?php
		// NOTA: il mockup mostrava anche un conteggio "views" — il modello dati
		// migrato non ha (per ora) un contatore di visualizzazioni reale, quindi
		// non lo inventiamo: se in futuro viene aggiunto un meta dedicato, basta
		// stamparlo qui.
		?>
		<div style="font-size:13px;color:#8A8D94;margin-bottom:32px;">
			<?php echo esc_html( get_the_date() ); ?> ·
			<?php printf( esc_html__( 'By %s', 'travelblogs' ), esc_html( get_the_author() ) ); ?>
		</div>

		<div class="tb-title-bar"></div>

		<div class="tb-layout">

			<!-- ARTICOLO -->
			<div class="tb-col-main">

				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'tb-hero', array( 'class' => 'tb-photo', 'style' => 'width:100%;height:380px;margin-bottom:28px;' ) ); ?>
				<?php else : ?>
					<span class="tb-photo" style="width:100%;height:380px;display:block;margin-bottom:28px;background:#EDEBE5;"></span>
				<?php endif; ?>

				<div style="display:flex;gap:12px;align-items:center;margin-bottom:28px;">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', '', array( 'class' => 'tb-photo', 'style' => 'border-radius:50%;' ) ); ?>
					<div>
						<div style="font-size:13px;font-weight:700;"><?php the_author(); ?></div>
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" style="font-size:12px;color:var(--tb-accent,#D6272E);font-weight:600;"><?php esc_html_e( 'Profile', 'travelblogs' ); ?></a>
					</div>
				</div>

				<div class="tb-body">
					<?php the_content(); ?>
				</div>

				<?php
				// Galleria immagini allegate all'articolo (se presenti).
				$tb_gallery = get_attached_media( 'image', $tb_news_id );
				if ( ! empty( $tb_gallery ) ) :
					?>
					<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:8px;">
						<?php foreach ( array_slice( $tb_gallery, 0, 3 ) as $tb_img ) : ?>
							<?php echo wp_get_attachment_image( $tb_img->ID, 'tb-card', false, array( 'class' => 'tb-photo', 'style' => 'height:160px;width:100%;' ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- SIDEBAR -->
			<div class="tb-sidebar">
				<?php tb_widget_news_categories(); ?>
			</div>
		</div>

		<?php if ( ! is_wp_error( $tb_tags ) && ! empty( $tb_tags ) ) : ?>
			<div style="margin-top:36px;padding-top:20px;border-top:1px solid #E2E0D9;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
				<span style="font-size:12px;color:#8A8D94;text-transform:uppercase;letter-spacing:.04em;"><?php esc_html_e( 'Tag:', 'travelblogs' ); ?></span>
				<?php foreach ( $tb_tags as $tb_tag ) : ?>
					<a href="<?php echo esc_url( get_term_link( $tb_tag ) ); ?>"><?php tb_cat_pill( $tb_tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>

	<?php
endwhile;

get_footer();
