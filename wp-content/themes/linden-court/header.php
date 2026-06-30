<?php
/**
 * Header — fixed translucent bar (solidifies on scroll), fullscreen mobile menu.
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#lc-main">Skip to content</a>

<header class="lc-header" id="masthead">
	<div class="container lc-header__inner">
		<a class="lc-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Linden Court — home">
			<span class="lc-brand__name">Linden Court</span>
			<span class="lc-brand__dot" aria-hidden="true"></span>
			<span class="lc-brand__sub">Solihull</span>
		</a>

		<nav class="lc-nav" aria-label="Primary">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false ) );
			} else {
				?>
				<a href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Apartments</a>
				<a href="<?php echo esc_url( home_url( '/amenities/' ) ); ?>">Amenities</a>
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
				<?php
			}
			?>
			<a class="lc-btn" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Book Your Stay</a>
		</nav>

		<button class="lc-burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="lc-mobile-menu">
			<span></span><span></span><span></span>
		</button>
	</div>
</header>

<div id="lc-mobile-menu" aria-label="Mobile menu">
	<a href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Apartments</a>
	<a href="<?php echo esc_url( home_url( '/amenities/' ) ); ?>">Amenities</a>
	<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
	<a href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>" style="color:var(--brass-2)">Book Your Stay</a>
</div>

<main id="lc-main">
