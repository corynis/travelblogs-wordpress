<?php
/**
 * Template di fallback obbligatorio per WordPress (ogni tema standalone
 * deve avere un index.php a livello radice). Le pagine principali del
 * sito hanno un proprio template dedicato (front-page.php,
 * single-capitolo.php, ecc.); questo copre qualunque richiesta che non
 * corrisponda a nessuno di quelli (es. un tipo di contenuto non ancora
 * templatizzato, o l'archivio predefinito di un CPT).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div style="max-width:1280px;margin:0 auto;padding:48px 24px;">

	<?php if ( have_posts() ) : ?>

		<?php if ( is_archive() || is_search() || is_home() ) : ?>
			<h1 style="font-size:28px;margin-bottom:28px;">
				<?php
				if ( is_search() ) {
					printf( esc_html__( 'Risultati per: %s', 'travelblogs' ), '<span>' . get_search_query() . '</span>' );
				} else {
					the_archive_title();
				}
				?>
			</h1>
		<?php endif; ?>

		<div style="display:flex;flex-direction:column;gap:28px;">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div style="display:flex;gap:20px;border-bottom:1px solid #ECEAE4;padding-bottom:28px;">
					<a href="<?php the_permalink(); ?>" style="flex-shrink:0;">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'tb-card', array( 'class' => 'tb-photo', 'style' => 'width:220px;height:150px;' ) ); ?>
						<?php else : ?>
							<span class="tb-photo" style="width:220px;height:150px;display:block;background:#EDEBE5;"></span>
						<?php endif; ?>
					</a>
					<div>
						<h2 style="font-size:19px;margin-bottom:8px;">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<div style="font-size:12px;color:#8A8D94;margin-bottom:10px;"><?php echo esc_html( get_the_date() ); ?></div>
						<p style="font-size:14px;color:#5B5E64;line-height:1.6;margin:0 0 12px;"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
						<a class="tb-readmore" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more ›', 'travelblogs' ); ?></a>
					</div>
				</div>
			<?php endwhile; ?>
		</div>

		<div class="tb-pagination" style="margin-top:32px;">
			<?php the_posts_pagination(); ?>
		</div>

	<?php else : ?>

		<p><?php esc_html_e( 'Nessun contenuto trovato.', 'travelblogs' ); ?></p>

	<?php endif; ?>

</div>

<?php
get_footer();
