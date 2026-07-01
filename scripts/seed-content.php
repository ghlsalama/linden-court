<?php
/**
 * Linden Court — content seed.
 *   • About / Amenities / Contact pages built as genuine Elementor documents
 *   • Testimonials (lc_testimonial) with ACF fields (rating, stay)
 *   • Services (mphb_room_service)
 *   • Primary nav menu, assigned to the theme's 'primary' location
 *
 * Idempotent: pages matched by slug are updated.
 * Usage: docker compose exec wordpress wp eval-file /tmp/seed-content.php --allow-root
 */

echo "== Content seed ==\n";

/* ---------------------------------------------------------------------
 * Elementor document builder
 * ------------------------------------------------------------------ */
function lc_el_id() { return substr( md5( uniqid( '', true ) ), 0, 7 ); }

function lc_el_widget( $type, $settings = array() ) {
	return array( 'id' => lc_el_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
}
function lc_el_col( $size, $widgets ) {
	return array( 'id' => lc_el_id(), 'elType' => 'column', 'settings' => array( '_column_size' => $size ), 'elements' => $widgets );
}
function lc_el_section( $cols, $settings = array() ) {
	$defaults = array(
		'gap'              => 'no',
		'padding'          => array( 'unit' => 'px', 'top' => '70', 'right' => '0', 'bottom' => '70', 'left' => '0', 'isLinked' => false ),
		'padding_mobile'   => array( 'unit' => 'px', 'top' => '50', 'right' => '20', 'bottom' => '50', 'left' => '20', 'isLinked' => false ),
		'content_width'    => array( 'unit' => 'px', 'size' => '1100', 'sizes' => array() ),
		'stretch_section'  => 'section',
	);
	return array( 'id' => lc_el_id(), 'elType' => 'section', 'settings' => array_merge( $defaults, $settings ), 'elements' => $cols );
}

/** Save a page as an Elementor document (slug-based, idempotent). */
function lc_el_page( $slug, $title, $sections ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$pid = $existing ? $existing->ID : 0;
	$pid = wp_insert_post( array(
		'ID'          => $pid,
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
		'post_content' => '',
	) );
	update_post_meta( $pid, '_elementor_edit_mode', 'builder' );
	update_post_meta( $pid, '_elementor_template_type', 'wp_page' );
	update_post_meta( $pid, '_elementor_version', '4.1.4' );
	update_post_meta( $pid, '_wp_page_template', 'default' );
	update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $sections ) ) );
	return $pid;
}

/* Reusable bits */
function lc_hero( $eyebrow, $h2, $lead ) {
	return lc_el_section( array( lc_el_col( 100, array(
		lc_el_widget( 'heading', array( 'title' => $eyebrow, 'header_size' => 'div' ) ),
		lc_el_widget( 'heading', array( 'title' => $h2, 'header_size' => 'h1' ) ),
		lc_el_widget( 'text-editor', array( 'editor' => '<p>' . $lead . '</p>' ) ),
	) ) ), array( 'padding' => array( 'unit' => 'px', 'top' => '170', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false ) ) );
}
function lc_cta( $title, $lead, $btn, $url ) {
	return lc_el_section( array( lc_el_col( 100, array(
		lc_el_widget( 'heading', array( 'title' => $title, 'header_size' => 'h2' ) ),
		lc_el_widget( 'text-editor', array( 'editor' => '<p>' . $lead . '</p>' ) ),
		lc_el_widget( 'button', array( 'text' => $btn, 'align' => 'center', 'link' => array( 'url' => $url, 'is_external' => '', 'nofollow' => '', 'custom_attributes' => '' ) ) ),
	) ) ), array( 'background_background' => 'classic', 'background_color' => '#121215', 'padding' => array( 'unit' => 'px', 'top' => '90', 'right' => '0', 'bottom' => '90', 'left' => '0', 'isLinked' => false ) ) );
}
function lc_iconbox( $icon, $title, $desc ) {
	return lc_el_widget( 'icon-box', array(
		'selected_icon'  => array( 'value' => $icon, 'library' => 'fa-solid' ),
		'title_text'     => $title,
		'description_text' => $desc,
	) );
}

/* ----------------------------- ABOUT ----------------------------- */
$about = array(
	lc_hero( 'About', 'A quietly considered stay', 'Linden Court began with a simple idea: that a serviced apartment should feel less like a transaction and more like a home you actually want to be in.' ),
	lc_el_section( array( lc_el_col( 100, array(
		lc_el_widget( 'heading', array( 'title' => 'Built around the detail', 'header_size' => 'h2' ) ),
		lc_el_widget( 'text-editor', array( 'editor' => '<p>Every apartment is finished with natural materials, linen bedding and considered lighting. Kitchens are built to cook in. Workspaces are made for long days. And the whole place is house-kept to a standard you can feel the moment you walk in.</p><p>We’re a short walk from Touchwood, Solihull station and the town’s best restaurants — but once you’re upstairs, the city goes quiet. That balance, between being in the middle of everything and feeling entirely private, is what we design for.</p>' ) ),
	) ) ) ),
	lc_el_section( array(
		lc_el_col( 33, array( lc_el_widget( 'heading', array( 'title' => '5★', 'header_size' => 'h2' ) ), lc_el_widget( 'text-editor', array( 'editor' => '<p>Average guest rating</p>' ) ) ) ),
		lc_el_col( 33, array( lc_el_widget( 'heading', array( 'title' => '24', 'header_size' => 'h2' ) ), lc_el_widget( 'text-editor', array( 'editor' => '<p>Apartments across Solihull</p>' ) ) ) ),
		lc_el_col( 33, array( lc_el_widget( 'heading', array( 'title' => '2 min', 'header_size' => 'h2' ) ), lc_el_widget( 'text-editor', array( 'editor' => '<p>From the station &amp; shopping</p>' ) ) ) ),
	), array( 'background_background' => 'classic', 'background_color' => '#121215' ) ),
	lc_cta( 'Come and stay', 'Five apartments, one standard. Find the one that fits your trip.', 'View apartments', '/accommodation/' ),
);
echo "  About page: #" . lc_el_page( 'about', 'About', $about ) . "\n";

/* --------------------------- AMENITIES --------------------------- */
$amenities = array(
	lc_hero( 'Amenities', 'Everything, included', 'No surprise fees and no missing basics — just a stay where the essentials are done properly.' ),
	lc_el_section( array(
		lc_el_col( 25, array( lc_iconbox( 'fas fa-wifi', 'Gigabit Wi-Fi', 'Fast, reliable, in every room.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-utensils', 'Full Kitchen', 'Cook, dine, and settle in.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-broom', 'House-Keeping', 'Weekly service and fresh linen.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-map-marker-alt', 'Central', 'Minutes from everything.' ) ) ),
	) ),
	lc_el_section( array(
		lc_el_col( 25, array( lc_iconbox( 'fas fa-car', 'On-Site Parking', 'Secure, available on request.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-laptop-house', 'Workspace', 'A proper desk in every unit.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-soap', 'Laundry', 'In-apartment washer/dryer.' ) ) ),
		lc_el_col( 25, array( lc_iconbox( 'fas fa-concierge-bell', '24/7 Support', 'A real person, any time.' ) ) ),
	), array( 'background_background' => 'classic', 'background_color' => '#121215' ) ),
	lc_cta( 'See the apartments', 'Each apartment has its own character. Explore them all.', 'Browse apartments', '/accommodation/' ),
);
echo "  Amenities page: #" . lc_el_page( 'amenities', 'Amenities', $amenities ) . "\n";

/* ----------------------------- CONTACT --------------------------- */
$contact = array(
	lc_hero( 'Contact', 'Get in touch', 'Questions about a stay, a corporate rate, or a longer booking? We reply quickly.' ),
	lc_el_section( array(
		lc_el_col( 50, array(
			lc_el_widget( 'heading', array( 'title' => 'Reach us directly', 'header_size' => 'h2' ) ),
			lc_el_widget( 'text-editor', array( 'editor' => '<p><strong>Phone:</strong> +44 121 555 0142<br><strong>Email:</strong> stay@lindencourt.co.uk<br><strong>Address:</strong> 14 Linden Walk, Solihull, B91 3RR</p><p>Check-in from 3pm · Check-out by 11am. Flexible cancellation on every booking.</p>' ) ),
			lc_el_widget( 'button', array( 'text' => 'Email the team', 'link' => array( 'url' => 'mailto:stay@lindencourt.co.uk', 'is_external' => '', 'nofollow' => '', 'custom_attributes' => '' ) ) ),
		) ),
		lc_el_col( 50, array(
			lc_el_widget( 'heading', array( 'title' => 'Check live availability', 'header_size' => 'h2' ) ),
			lc_el_widget( 'shortcode', array( 'shortcode' => '[mphb_availability_search adults="2" template="horizontal"]' ) ),
		) ),
	), array( 'background_background' => 'classic', 'background_color' => '#121215' ) ),
);
echo "  Contact page: #" . lc_el_page( 'contact', 'Contact', $contact ) . "\n";

/* --------------------------- TESTIMONIALS ------------------------ */
$testimonials = array(
	array( 'James Whitfield', 5, 'Stayed in the One-Bedroom Suite', 'Spotless, quiet and genuinely comfortable. The kitchen meant I could actually unwind after work instead of eating out every night. This is how business travel should feel.' ),
	array( 'Priya Nair', 5, 'Stayed in the Penthouse', 'We booked the penthouse for a long weekend and didn’t want to leave. Beautifully finished, faultless service, and two minutes from everything in Solihull.' ),
	array( 'Marco Rossi', 5, 'Stayed in the Linden Studio', 'Booked last-minute and it exceeded every hotel I’ve used locally. Thoughtful touches everywhere. Linden Court is my default from now on.' ),
	array( 'Aisha Karim', 5, 'Stayed in the Two-Bedroom Apartment', 'Travelled with the kids and it just worked — space, a real kitchen, and a calm feel. We’ll absolutely be back.' ),
);
// clear old testimonials
foreach ( (array) get_posts( array( 'post_type' => 'lc_testimonial', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $tid ) {
	wp_delete_post( $tid, true );
}
foreach ( $testimonials as $t ) {
	$pid = wp_insert_post( array( 'post_type' => 'lc_testimonial', 'post_status' => 'publish', 'post_title' => $t[0], 'post_content' => $t[3] ) );
	if ( function_exists( 'update_field' ) ) {
		update_field( 'rating', $t[1], $pid );
		update_field( 'stay', $t[2], $pid );
	}
}
echo "  Testimonials: " . count( $testimonials ) . " created\n";

/* ----------------------------- SERVICES -------------------------- */
$services = array(
	array( 'Welcome Hamper', 25, 'Locally sourced treats waiting on arrival.' ),
	array( 'Late Check-Out (2pm)', 20, 'Keep your apartment until the afternoon.' ),
	array( 'Grocery Pre-Stock', 30, 'Tell us your list; the kitchen’s full before you arrive.' ),
	array( 'Extra Deep Clean', 15, 'A full mid-stay refresh, on request.' ),
);
foreach ( (array) get_posts( array( 'post_type' => 'mphb_room_service', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $sid ) {
	wp_delete_post( $sid, true );
}
foreach ( $services as $s ) {
	$sid = wp_insert_post( array( 'post_type' => 'mphb_room_service', 'post_status' => 'publish', 'post_title' => $s[0] ) );
	update_post_meta( $sid, 'mphb_price', $s[1] );
	update_post_meta( $sid, 'mphb_price_periodicity', 'once' );
	update_post_meta( $sid, 'mphb_description', $s[2] );
}
echo "  Services: " . count( $services ) . " created\n";

/* --------------------- MOTOPRESS SYSTEM PAGES --------------------- */
// MotoPress needs Checkout / Search Results / Confirmation / etc. pages
// configured with the right shortcodes; without them the booking flow 500s
// (null page lookup → "headers already sent"). Create + wire the options.
$mphb_pages = array(
	array( 'Checkout', 'mphb-checkout', 'mphb_checkout_page', '[mphb_checkout]' ),
	array( 'Search Results', 'mphb-search-results', 'mphb_search_results_page', '[mphb_search_results]' ),
	array( 'Booking Confirmation', 'mphb-booking-confirmation', 'mphb_booking_confirmation_page', '[mphb_booking_confirmation]' ),
	array( 'Booking Cancellation', 'mphb-booking-cancellation', 'mphb_booking_cancellation_page', '[mphb_booking_cancellation]' ),
	array( 'Payment Successful', 'mphb-payment-successful', 'mphb_payment_success_page', '<p>Thank you — your payment was received and your booking is confirmed.</p>' ),
	array( 'Payment Failed', 'mphb-payment-failed', 'mphb_payment_failed_page', '<p>Your payment did not go through. Please try again or contact us.</p>' ),
);
foreach ( $mphb_pages as $mp ) {
	if ( get_option( $mp[2] ) ) { continue; } // already configured
	$ex   = get_page_by_path( $mp[1] );
	$pid  = $ex ? $ex->ID : wp_insert_post( array(
		'post_type' => 'page', 'post_status' => 'publish',
		'post_title' => $mp[0], 'post_name' => $mp[1], 'post_content' => $mp[3],
	) );
	update_option( $mp[2], $pid );
}
echo "  MotoPress system pages: configured\n";

/* ---------------------------- NAV MENU --------------------------- */
$menu = wp_get_nav_menu_object( 'Primary' );
if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( 'Primary' );
	$menu = wp_get_nav_menu_object( $menu_id );
}
if ( $menu ) {
	$menu_id = (int) $menu->term_id;
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	$items = array(
		array( 'Apartments', '/accommodation/' ),
		array( 'Amenities', '/amenities/' ),
		array( 'About', '/about/' ),
		array( 'Contact', '/contact/' ),
	);
	foreach ( $items as $it ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $it[0],
			'menu-item-url'    => home_url( $it[1] ),
			'menu-item-status' => 'publish',
		) );
	}
	$locs = (array) get_theme_mod( 'nav_menu_locations' );
	$locs['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locs );
	echo "  Nav menu 'Primary' set (#{$menu_id})\n";
}

echo "\nDone.\n";
