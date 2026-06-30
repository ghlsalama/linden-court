<?php
/**
 * Linden Court — theme functions.
 *
 * Dark-luxury child theme on top of Hello Elementor.
 * Carries: asset loading, menus, Testimonials CPT, ACF local-json sync,
 * and brand settings surfaced in the WP Customizer.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'LC_VERSION', '1.0.0' );
define( 'LC_DIR', get_stylesheet_directory() );
define( 'LC_URI', get_stylesheet_directory_uri() );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	// Google Fonts: Cormorant Garamond (display, incl. italic) + Inter (UI/body).
	wp_enqueue_style(
		'lc-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@300;400;500;600&display=swap',
		array(),
		null
	);

	// Parent theme stylesheet first, then child, then our design system.
	wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css', array(), '3.4.9' );
	wp_enqueue_style( 'lc-style', get_stylesheet_uri(), array( 'hello-elementor-parent' ), LC_VERSION );
	wp_enqueue_style( 'lc-main', LC_URI . '/css/linden.css', array( 'lc-style' ), LC_VERSION );

	wp_enqueue_script( 'lc-nav', LC_URI . '/js/nav.js', array(), LC_VERSION, true );
}, 20 );

/* -------------------------------------------------------------------------
 * Theme setup
 * ---------------------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	add_image_size( 'lc-room', 900, 675, true ); // 4:3 room card crop.
	add_image_size( 'lc-wide', 1600, 900, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'linden' ),
		'footer'  => __( 'Footer Menu', 'linden' ),
	) );
} );

/* -------------------------------------------------------------------------
 * Testimonials CPT (paired with an ACF field group via acf-json)
 * ---------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_type( 'lc_testimonial', array(
		'labels' => array(
			'name'               => 'Testimonials',
			'singular_name'      => 'Testimonial',
			'add_new_item'       => 'Add New Testimonial',
			'edit_item'          => 'Edit Testimonial',
			'new_item'           => 'New Testimonial',
			'all_items'          => 'All Testimonials',
			'not_found'          => 'No testimonials yet.',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-format-quote',
		'menu_position'   => 27,
		'supports'        => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'     => false,
	) );
} );

/* -------------------------------------------------------------------------
 * ACF local JSON sync (free-tier, version-controllable field groups)
 * ---------------------------------------------------------------------- */
add_filter( 'acf/settings/save_json', function ( $path ) {
	return LC_DIR . '/acf-json';
} );
add_filter( 'acf/settings/load_json', function ( $paths ) {
	$paths[] = LC_DIR . '/acf-json';
	return $paths;
} );

/* -------------------------------------------------------------------------
 * Brand settings — editable in Appearance → Customize → Linden Court Brand
 * ---------------------------------------------------------------------- */
function lc_setting( $key, $default = '' ) {
	$defaults = array(
		'phone'   => '+44 121 555 0142',
		'email'   => 'stay@lindencourt.co.uk',
		'address' => '14 Linden Walk, Solihull, West Midlands, B91 3RR',
		'insta'   => 'https://instagram.com',
		'facebook'=> 'https://facebook.com',
		'tagline' => 'Serviced apartments in the heart of Solihull',
	);
	$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $default;
	return get_theme_mod( 'lc_' . $key, $default );
}

add_action( 'customize_register', function ( $wp ) {
	$wp->add_section( 'lc_brand', array(
		'title'    => 'Linden Court Brand',
		'priority' => 30,
	) );
	$fields = array(
		'phone'    => 'Phone',
		'email'    => 'Email',
		'address'  => 'Address',
		'insta'    => 'Instagram URL',
		'facebook' => 'Facebook URL',
		'tagline'  => 'Tagline',
	);
	foreach ( $fields as $key => $label ) {
		$wp->add_setting( 'lc_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp->add_control( 'lc_' . $key, array(
			'label'   => $label,
			'section' => 'lc_brand',
			'type'    => 'text',
		) );
	}
} );

/* -------------------------------------------------------------------------
 * Small template helpers
 * ---------------------------------------------------------------------- */

/** Brass eyebrow label. */
function lc_eyebrow( $text, $echo = true ) {
	$html = '<span class="lc-eyebrow">' . esc_html( $text ) . '</span>';
	if ( $echo ) { echo $html; }
	return $html;
}

/** Star rating (1–5) as accessible markup. */
function lc_stars( $rating = 5 ) {
	$out  = '<span class="lc-stars" aria-label="' . sprintf( esc_attr__( '%s out of 5 stars', 'linden' ), $rating ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<span class="' . ( $i <= $rating ? 'on' : 'off' ) . '">★</span>';
	}
	$out .= '</span>';
	return $out;
}

/**
 * "From £X / night" line for a MotoPress room type.
 * Uses the MotoPress prices facade to pull the live minimum base price for a
 * near-future date; wrapped so it never fatals if the API or data is missing.
 */
function lc_room_price( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( function_exists( 'mphb_prices_facade' ) ) {
		try {
			$min = mphb_prices_facade()->getRoomTypeMinBasePriceForDate( $post_id, new \DateTime( '+1 day' ) );
			if ( is_numeric( $min ) && (float) $min > 0 ) {
				return 'From £' . number_format_i18n( (float) $min ) . ' <span>/ night</span>';
			}
		} catch ( \Throwable $e ) {
			// fall through
		}
	}
	return '';
}

/**
 * Card image for a room — featured attachment if set, else a deterministic
 * placeholder keyed by slug (replaced with real photography later).
 */
function lc_room_card_image( $post_id ) {
	$slug = get_post_field( 'post_name', $post_id );
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, 'lc-room', array( 'alt' => esc_attr( get_the_title( $post_id ) ) ) );
	}
	return '<img src="https://picsum.photos/seed/' . esc_attr( $slug ? $slug : 'lc-room' ) . '/900/675" alt="' . esc_attr( get_the_title( $post_id ) ) . '" loading="lazy">';
}

/** Short meta line (guests · size) for a room card. */
function lc_room_card_meta( $post_id ) {
	$cap  = (int) get_post_meta( $post_id, 'mphb_total_capacity', true );
	$size = get_post_meta( $post_id, 'mphb_size', true );
	$out  = array();
	if ( $cap ) {
		$out[] = '<span>' . esc_html( $cap ) . ' guest' . ( $cap > 1 ? 's' : '' ) . '</span>';
	}
	if ( $size ) {
		$out[] = '<span>' . esc_html( $size ) . ' m²</span>';
	}
	return $out ? implode( '', $out ) : '<span>Serviced apartment</span>';
}
