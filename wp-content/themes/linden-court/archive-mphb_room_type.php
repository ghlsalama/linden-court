<?php
/**
 * Archive: Accommodations (mphb_room_type) — dark-luxury listing.
 * Overrides MotoPress's bare default archive at /accommodation/.
 */

$rooms = get_posts( array(
	'post_type'      => 'mphb_room_type',
	'post_status'    => 'publish',
	'numberposts'    => -1,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
) );

get_header();
?>

<header class="lc-pagehead">
	<div class="container">
		<div class="lc-breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Apartments</div>
		<?php lc_eyebrow( 'The Apartments' ); ?>
		<h1 class="lc-h1" style="margin-top:14px;">Find your apartment</h1>
		<p class="lc-lead">Five considered apartments — from a calm studio to a two-bedroom penthouse. All fully serviced, all in the heart of Solihull.</p>
	</div>
</header>

<section class="lc-section lc-section--alt">
	<div class="container">

		<!-- live availability search (MotoPress booking engine) -->
		<div class="lc-search" style="margin-bottom:56px;">
			<?php echo do_shortcode( '[mphb_availability_search adults="2" template="horizontal"]' ); ?>
		</div>

		<div class="lc-grid lc-rooms-grid">
			<?php foreach ( $rooms as $r ) : ?>
				<article class="lc-card">
					<a class="lc-card__media" href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>">
						<?php echo lc_room_card_image( $r->ID ); // phpcs:ignore ?>
						<span class="lc-card__tag"><?php echo esc_html( get_the_title( $r->ID ) ); ?></span>
					</a>
					<div class="lc-card__body">
						<div class="lc-card__meta"><?php echo lc_room_card_meta( $r->ID ); // phpcs:ignore ?></div>
						<h3 class="lc-h3"><?php echo esc_html( get_the_title( $r->ID ) ); ?></h3>
						<p class="lc-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $r->post_content ), 24, '…' ) ); ?></p>
						<div class="lc-card__foot">
							<span class="lc-card__price"><?php echo lc_room_price( $r->ID ); // phpcs:ignore ?></span>
							<a class="lc-link" href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>">View <span class="arr">→</span></a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="lc-section lc-cta">
	<div class="lc-cta__bg"><img src="https://picsum.photos/seed/linden-cta/1800/1000" alt="" aria-hidden="true"></div>
	<div class="container">
		<?php lc_eyebrow( 'Not Sure Which Fits?' ); ?>
		<h2 class="lc-h2">Tell us your dates — we'll find the perfect apartment</h2>
		<p class="lc-lead">Every booking is flexible, with clear pricing and no hidden fees.</p>
		<a class="lc-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to Us <span class="arr">→</span></a>
	</div>
</section>

<?php get_footer(); ?>
