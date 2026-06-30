<?php
/**
 * Footer — brand, quick links, contact (from Customizer), socials, legal.
 */
$yr = gmdate( 'Y' );
?>
</main><!-- #lc-main -->

<footer class="lc-footer" id="colophon">
	<div class="container">
		<div class="lc-footer__grid">
			<div class="lc-footer__about">
				<a class="lc-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="lc-brand__name">Linden Court</span>
					<span class="lc-brand__dot" aria-hidden="true"></span>
				</a>
				<p><?php echo esc_html( lc_setting( 'tagline' ) ); ?>. Quietly considered stays for business and leisure, with the warmth of a home and the standard of a hotel.</p>
				<div class="lc-social">
					<a href="<?php echo esc_url( lc_setting( 'insta' ) ); ?>" aria-label="Instagram" target="_blank" rel="noopener">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
					</a>
					<a href="<?php echo esc_url( lc_setting( 'facebook' ) ); ?>" aria-label="Facebook" target="_blank" rel="noopener">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3l.4-3H14V4.5c0-.9.3-1.5 1.6-1.5H17V.2C16.6.1 15.5 0 14.3 0 11.8 0 10 1.5 10 4.2V6H7v3h3v9h4z"/></svg>
					</a>
				</div>
			</div>

			<div>
				<h4>Explore</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Apartments</a></li>
					<li><a href="<?php echo esc_url( home_url( '/amenities/' ) ); ?>">Amenities</a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				</ul>
			</div>

			<div class="lc-footer__contact">
				<h4>Contact</h4>
				<ul>
					<li><?php echo esc_html( lc_setting( 'address' ) ); ?></li>
					<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', lc_setting( 'phone' ) ) ); ?>"><?php echo esc_html( lc_setting( 'phone' ) ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( lc_setting( 'email' ) ); ?>"><?php echo esc_html( lc_setting( 'email' ) ); ?></a></li>
				</ul>
			</div>

			<div>
				<h4>Good to Know</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Check-in from 3pm</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Flexible cancellation</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Corporate rates</a></li>
				</ul>
			</div>
		</div>

		<div class="lc-footer__bottom">
			<span>© <?php echo esc_html( $yr ); ?> Linden Court Serviced Apartments. All rights reserved.</span>
			<span>Solihull · West Midlands · United Kingdom</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
