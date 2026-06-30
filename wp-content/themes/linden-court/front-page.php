<?php
/**
 * Front page — Linden Court homepage (dark-luxury showpiece).
 *
 * Sections: hero · statement + stats · featured apartments · amenities ·
 * testimonials · closing CTA.
 *
 * Featured apartments and testimonials are queried live from MotoPress room
 * types and the Testimonials CPT; graceful fallbacks render until that
 * content exists so the page never looks empty.
 */

/* ---- featured apartments (MotoPress room types) ---- */
$rooms = array();
if ( post_type_exists( 'mphb_room_type' ) ) {
	$rooms = get_posts( array(
		'post_type'      => 'mphb_room_type',
		'posts_per_page' => 3,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'post_status'    => 'publish',
	) );
}

/* ---- testimonials (lc_testimonial CPT) ---- */
$testis = array();
if ( post_type_exists( 'lc_testimonial' ) ) {
	$testis = get_posts( array(
		'post_type'      => 'lc_testimonial',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	) );
}

get_header();
?>

<!-- ============================ HERO ============================ -->
<section class="lc-hero">
	<div class="lc-hero__media">
		<img src="https://picsum.photos/seed/lindencourt-hero-night/2000/1300" alt="Linden Court serviced apartments at dusk" fetchpriority="high">
	</div>
	<div class="container lc-hero__inner">
		<?php lc_eyebrow( 'Solihull · Serviced Apartments' ); ?>
		<h1 class="lc-h1">Stay longer,<br><em>stay beautifully.</em></h1>
		<p class="lc-hero__lead">Light-filled, fully-serviced apartments in the heart of Solihull — for business, for leisure, and for everything in between.</p>
		<div class="lc-row-btn">
			<a class="lc-btn" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Book Your Stay <span class="arr">→</span></a>
			<a class="lc-btn lc-btn--ghost" href="<?php echo esc_url( home_url( '/amenities/' ) ); ?>">View Amenities</a>
		</div>
	</div>
	<div class="lc-hero__scroll">Scroll</div>
</section>

<!-- ============================ STATEMENT ============================ -->
<section class="lc-section">
	<div class="container">
		<div class="lc-grid lc-split">
			<div>
				<?php lc_eyebrow( 'The Linden Standard' ); ?>
				<h2 class="lc-h2">The warmth of a home.<br>The standard of a hotel.</h2>
				<p class="lc-lead">Every apartment is house-kept to a hotel standard, finished with considered materials and quiet luxury — linen bedding, underfloor warmth, fast Wi-Fi and a kitchen built to actually cook in. No front-desk friction; just a calm, private place that feels like yours.</p>
				<p class="lc-lead" style="margin-top:1.1em;">Whether you're here for a week of meetings or a long weekend, Linden Court is designed to make staying feel like the best part of the trip.</p>
				<div class="lc-stats">
					<div><div class="lc-stat__n">24</div><div class="lc-stat__l">Apartments</div></div>
					<div><div class="lc-stat__n">4.9★</div><div class="lc-stat__l">Guest rating</div></div>
					<div><div class="lc-stat__n">2 min</div><div class="lc-stat__l">To Touchwood &amp; the station</div></div>
				</div>
			</div>
			<div class="lc-split__media">
				<img src="https://picsum.photos/seed/linden-livingroom/1100/1320" alt="A Linden Court living room">
			</div>
		</div>
	</div>
</section>

<!-- ============================ FEATURED APARTMENTS ============================ -->
<section class="lc-section lc-section--alt">
	<div class="container">
		<div class="lc-grid" style="grid-template-columns:1fr; gap:14px; margin-bottom:48px;">
			<?php lc_eyebrow( 'The Apartments' ); ?>
			<div class="lc-grid" style="grid-template-columns:1fr auto; align-items:end; gap:20px;">
				<h2 class="lc-h2" style="margin:0;">A space for every stay</h2>
				<a class="lc-link" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">View all apartments <span class="arr">→</span></a>
			</div>
		</div>

		<div class="lc-grid lc-rooms-grid">
			<?php
			if ( $rooms ) :
				foreach ( $rooms as $r ) :
					?>
					<article class="lc-card">
						<a class="lc-card__media" href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>">
							<?php echo lc_room_card_image( $r->ID ); // phpcs:ignore ?>
							<span class="lc-card__tag"><?php echo esc_html( wp_trim_words( get_the_title( $r->ID ), 4 ) ); ?></span>
						</a>
						<div class="lc-card__body">
							<div class="lc-card__meta"><?php echo lc_room_card_meta( $r->ID ); // phpcs:ignore ?></div>
							<h3 class="lc-h3"><?php echo esc_html( get_the_title( $r->ID ) ); ?></h3>
							<p class="lc-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $r->post_content ), 22, '…' ) ); ?></p>
							<div class="lc-card__foot">
								<span class="lc-card__price"><?php echo lc_room_price( $r->ID ); // phpcs:ignore ?></span>
								<a class="lc-link" href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>">View <span class="arr">→</span></a>
							</div>
						</div>
					</article>
					<?php
				endforeach;
			else :
				/* graceful fallback until rooms are published */
				$fallbacks = array(
					array( 'The Linden Studio',  'A calm, compact studio for solo travellers and short stays.', 'linden-studio', '£95' ),
					array( 'One-Bedroom Suite',   'A roomy one-bed with a full kitchen and a workspace.',        'linden-onebed', '£135' ),
					array( 'The Penthouse',       'Top-floor, two-bedroom penthouse with a private terrace.',     'linden-penthouse', '£240' ),
				);
				foreach ( $fallbacks as $f ) :
					?>
					<article class="lc-card">
						<div class="lc-card__media">
							<img src="https://picsum.photos/seed/<?php echo esc_attr( $f[2] ); ?>/900/675" alt="<?php echo esc_attr( $f[0] ); ?>" loading="lazy">
							<span class="lc-card__tag"><?php echo esc_html( $f[0] ); ?></span>
						</div>
						<div class="lc-card__body">
							<div class="lc-card__meta"><span>Serviced apartment</span></div>
							<h3 class="lc-h3"><?php echo esc_html( $f[0] ); ?></h3>
							<p class="lc-card__desc"><?php echo esc_html( $f[1] ); ?></p>
							<div class="lc-card__foot">
								<span class="lc-card__price">From £<?php echo esc_html( $f[3] ); ?> <span>/ night</span></span>
								<a class="lc-link" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">View <span class="arr">→</span></a>
							</div>
						</div>
					</article>
					<?php
				endforeach;
			endif;
			?>
		</div>
	</div>
</section>

<!-- ============================ AMENITIES ============================ -->
<section class="lc-section">
	<div class="container">
		<div class="lc-head lc-head--center" style="margin-bottom:52px;">
			<?php lc_eyebrow( 'Everything Included' ); ?>
			<h2 class="lc-h2">Considered, down to the detail</h2>
			<p class="lc-lead">Every stay includes the essentials done properly — and a few thoughtful extras.</p>
		</div>
		<div class="lc-grid lc-amenities">
			<div class="lc-amen"><div class="lc-amen__ic">⋠</div><div class="lc-amen__t">House-keeping</div><div class="lc-amen__d">Weekly service &amp; fresh linen</div></div>
			<div class="lc-amen"><div class="lc-amen__ic">~-~-~-~-</div><div class="lc-amen__t">Gigabit Wi-Fi</div><div class="lc-amen__d">Fast, reliable, throughout</div></div>
			<div class="lc-amen"><div class="lc-amen__ic">⊜</div><div class="lc-amen__t">Full kitchen</div><div class="lc-amen__d">Cook, dine, settle in</div></div>
			<div class="lc-amen"><div class="lc-amen__ic">⌖</div><div class="lc-amen__t">Central location</div><div class="lc-amen__d">Minutes from everywhere</div></div>
		</div>
	</div>
</section>

<!-- ============================ TESTIMONIALS ============================ -->
<section class="lc-section lc-section--alt">
	<div class="container">
		<div class="lc-head lc-head--center" style="margin-bottom:52px;">
			<?php lc_eyebrow( 'Guest Stories' ); ?>
			<h2 class="lc-h2">Loved by people who travel often</h2>
		</div>
		<div class="lc-grid lc-testi-grid">
			<?php
			if ( $testis ) :
				foreach ( $testis as $t ) :
					$rating = function_exists( 'get_field' ) ? (int) get_field( 'rating', $t->ID ) : 5;
					$sub    = function_exists( 'get_field' ) ? get_field( 'stay', $t->ID ) : '';
					$initials = strtoupper( substr( trim( $t->post_title ), 0, 1 ) );
					?>
					<figure class="lc-testi">
						<?php echo lc_stars( $rating ? $rating : 5 ); // phpcs:ignore ?>
						<blockquote class="lc-testi__body">“<?php echo esc_html( wp_strip_all_tags( $t->post_content ) ); ?>”</blockquote>
						<figcaption class="lc-testi__who">
							<span class="lc-testi__avatar"><?php echo esc_html( $initials ); ?></span>
							<span>
								<span class="lc-testi__name"><?php echo esc_html( $t->post_title ); ?></span><br>
								<span class="lc-testi__sub"><?php echo esc_html( $sub ? $sub : 'Verified guest' ); ?></span>
							</span>
						</figcaption>
					</figure>
					<?php
				endforeach;
			else :
				$defs = array(
					array( 'James Whitfield', 'Stayed in the One-Bedroom Suite', 5, 'Spotless, quiet and genuinely comfortable. The kitchen meant I could actually unwind after work instead of eating out every night. This is how business travel should feel.' ),
					array( 'Priya Nair',       'Stayed in the Penthouse',          5, 'We booked the penthouse for a long weekend and didn\'t want to leave. Beautifully finished, faultless service, and two minutes from everything.' ),
					array( 'Marco Rossi',      'Stayed in the Linden Studio',      5, 'Booked last-minute and it exceeded every hotel I\'ve used in Solihull. Thoughtful touches everywhere. Linden Court is my default from now on.' ),
				);
				foreach ( $defs as $d ) :
					?>
					<figure class="lc-testi">
						<?php echo lc_stars( $d[2] ); // phpcs:ignore ?>
						<blockquote class="lc-testi__body">“<?php echo esc_html( $d[3] ); ?>”</blockquote>
						<figcaption class="lc-testi__who">
							<span class="lc-testi__avatar"><?php echo esc_html( substr( $d[0], 0, 1 ) ); ?></span>
							<span>
								<span class="lc-testi__name"><?php echo esc_html( $d[0] ); ?></span><br>
								<span class="lc-testi__sub"><?php echo esc_html( $d[1] ); ?></span>
							</span>
						</figcaption>
					</figure>
					<?php
				endforeach;
			endif;
			?>
		</div>
	</div>
</section>

<!-- ============================ CTA ============================ -->
<section class="lc-section lc-cta">
	<div class="lc-cta__bg"><img src="https://picsum.photos/seed/linden-city-evening/1800/1000" alt="" aria-hidden="true"></div>
	<div class="container">
		<?php lc_eyebrow( 'Ready When You Are' ); ?>
		<h2 class="lc-h2">Book your Linden Court stay</h2>
		<p class="lc-lead">Check live availability and reserve in minutes. Flexible cancellation on every booking.</p>
		<a class="lc-btn" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Check Availability <span class="arr">→</span></a>
	</div>
</section>

<?php get_footer(); ?>
