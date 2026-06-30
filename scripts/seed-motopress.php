<?php
/**
 * Linden Court — MotoPress seed.
 * Creates: an all-year season, 5 room types, bookable room units, and active
 * rates (via the MotoPress facade API, so pricing/bookability are genuine).
 *
 * Idempotent: wipes existing room types / rooms / rates on each run.
 * Usage: docker compose exec wordpress wp eval-file /tmp/seed-motopress.php --allow-root
 */

echo "== MotoPress seed ==\n";

/* 1. All-year season --------------------------------------------------- */
$season_id = (int) get_option( 'lc_seed_season_id' );
if ( ! $season_id || ! get_post( $season_id ) ) {
	$season_id = wp_insert_post( array(
		'post_type'   => 'mphb_season',
		'post_status' => 'publish',
		'post_title'  => 'All Year',
	) );
	update_post_meta( $season_id, 'mphb_start_date', '2025-01-01' );
	update_post_meta( $season_id, 'mphb_end_date',   '2030-12-31' );
	update_post_meta( $season_id, 'mphb_days',       array( 0, 1, 2, 3, 4, 5, 6 ) ); // 7 => no weekday filter
	update_post_meta( $season_id, 'mphb_repeat_period', 'none' );
	update_option( 'lc_seed_season_id', $season_id );
}
$season = MPHB()->getSeasonRepository()->findById( $season_id );
echo "Season #{$season_id} resolves: " . ( $season ? 'yes' : 'NO — rates will be inactive!' ) . "\n";

/* 2. Full reset of bookable entities ----------------------------------- */
foreach ( array( 'mphb_room', 'mphb_rate', 'mphb_room_type' ) as $type ) {
	foreach ( (array) get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $pid ) {
		wp_delete_post( $pid, true );
	}
}

/* 3. Apartments -------------------------------------------------------- */
$apartments = array(
	array( 'title' => 'The Linden Studio', 'slug' => 'linden-studio', 'cap' => 1, 'adults' => 1, 'children' => 0, 'size' => 28, 'price' => 95,  'units' => 3,
		'desc' => 'A calm, light-filled studio with a proper kitchen, linen bedding and a quiet workspace. Designed for solo travellers and short stays.' ),
	array( 'title' => 'One-Bedroom Suite', 'slug' => 'one-bedroom-suite', 'cap' => 2, 'adults' => 2, 'children' => 0, 'size' => 42, 'price' => 135, 'units' => 3,
		'desc' => 'A roomy one-bedroom apartment with a full kitchen, a separate living area and a dedicated workspace — ideal for longer stays.' ),
	array( 'title' => 'Two-Bedroom Apartment', 'slug' => 'two-bedroom-apartment', 'cap' => 4, 'adults' => 3, 'children' => 1, 'size' => 64, 'price' => 185, 'units' => 2,
		'desc' => 'A generous two-bedroom apartment built for families and small groups, with two bathrooms and an open-plan kitchen and living space.' ),
	array( 'title' => 'The Penthouse', 'slug' => 'the-penthouse', 'cap' => 4, 'adults' => 4, 'children' => 0, 'size' => 95, 'price' => 265, 'units' => 1,
		'desc' => 'Our top-floor, two-bedroom penthouse with floor-to-ceiling glass, a designer kitchen and a private landscaped terrace.' ),
	array( 'title' => 'Ground-Floor Accessible Studio', 'slug' => 'accessible-studio', 'cap' => 2, 'adults' => 2, 'children' => 0, 'size' => 30, 'price' => 105, 'units' => 2,
		'desc' => 'A step-free, fully accessible studio with a roll-in shower, wide doorways and the same considered finish as every Linden Court apartment.' ),
);

$facade  = mphb_prices_facade();
$order   = 0;
$created = array();

foreach ( $apartments as $a ) {
	$order++;
	$rt = wp_insert_post( array(
		'post_type'    => 'mphb_room_type',
		'post_status'  => 'publish',
		'post_title'   => $a['title'],
		'post_name'    => $a['slug'],
		'post_content' => $a['desc'],
		'menu_order'   => $order,
	) );
	update_post_meta( $rt, 'mphb_adults_capacity',   $a['adults'] );
	update_post_meta( $rt, 'mphb_children_capacity', $a['children'] );
	update_post_meta( $rt, 'mphb_total_capacity',    $a['cap'] );
	update_post_meta( $rt, 'mphb_size',              $a['size'] );

	/* bookable units */
	for ( $i = 1; $i <= $a['units']; $i++ ) {
		$rm = wp_insert_post( array(
			'post_type'   => 'mphb_room',
			'post_status' => 'publish',
			'post_title'  => $a['title'] . ' — ' . sprintf( '%02d', $i ),
		) );
		update_post_meta( $rm, 'mphb_room_type_id', $rt );
	}

	/* active rate — create the rate post directly, mirroring
	   RateRepository::mapEntityToPostData() (the shape duplicate() relies on),
	   since the facade saveRate() does not insert brand-new rates. */
	$sp = \MPHB\Entities\SeasonPrice::create( array( 'id' => 0, 'season_id' => $season_id, 'price' => $a['price'] ) );
	if ( $sp ) {
		$seasonPricesMeta = array(
			array(
				'id'     => $sp->getId(),
				'season' => $sp->getSeasonId(),
				'price'  => $sp->getPricesAndVariations(),
			),
		);
		$rateId = wp_insert_post( array(
			'post_type'   => 'mphb_rate',
			'post_status' => 'publish',
			'post_title'  => 'Standard Rate',
		) );
		update_post_meta( $rateId, 'mphb_room_type_id',  $rt );
		update_post_meta( $rateId, 'mphb_description',   '' );
		update_post_meta( $rateId, 'mphb_season_prices', $seasonPricesMeta );
	}

	$active = $facade->isRoomTypeHasActiveRate( $rt, new \DateTime( '+1 day' ), new \DateTime( '+4 days' ) );
	$created[] = array( 'id' => $rt, 'title' => $a['title'], 'price' => $a['price'], 'active' => $active, 'units' => $a['units'] );
	echo "  - {$a['title']} (rt={$rt}): active=" . ( $active ? 'yes' : 'NO' ) . "  £{$a['price']}  units={$a['units']}\n";
}

echo "\nDone. " . count( $created ) . " room types seeded.\n";
