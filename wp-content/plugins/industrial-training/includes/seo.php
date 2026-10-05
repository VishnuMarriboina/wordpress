<?php
/**
 * Title, meta description, Open Graph and JSON-LD for the landing page.
 * Title/description/OG are skipped when Yoast SEO or Rank Math is active (they own those tags).
 */

defined( 'ABSPATH' ) || exit;

function itp_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

add_filter( 'pre_get_document_title', function ( $title ) {
	$seo = itp_settings()['seo'];
	if ( ! empty( $seo['enabled'] ) && $seo['title'] && itp_is_landing() && ! itp_seo_plugin_active() ) {
		return $seo['title'];
	}
	return $title;
}, 20 );

// Skillrise favicon on the landing page, unless a Site Icon is set under Appearance → Customize.
add_action( 'wp_head', function () {
	if ( has_site_icon() || ! itp_is_landing() ) {
		return;
	}
	printf( "<link rel=\"icon\" type=\"image/png\" sizes=\"64x64\" href=\"%s\">\n", esc_url( ITP_URL . 'assets/images/skillrise-icon-64.png' ) );
	printf( "<link rel=\"apple-touch-icon\" href=\"%s\">\n", esc_url( ITP_URL . 'assets/images/skillrise-icon-180.png' ) );
}, 5 );

add_action( 'wp_head', function () {
	$seo = itp_settings()['seo'];
	if ( empty( $seo['enabled'] ) || ! itp_is_landing() ) {
		return;
	}

	if ( ! itp_seo_plugin_active() ) {
		$url = get_permalink( get_queried_object_id() );
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $seo['description'] ) );
		printf( "<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $seo['title'] ) );
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $seo['description'] ) );
		printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( itp_hero_image_url() ) );
		echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
	}

	$s       = itp_settings();
	$contact = $s['contact'];
	$org     = array_filter( [
		'@type'     => 'EducationalOrganization',
		'@id'       => home_url( '/#itp-organization' ),
		'name'      => $s['brand_name'],
		'url'       => get_permalink( get_queried_object_id() ),
		'logo'      => itp_real_url( $s['brand_logo'] ) ?: itp_local_url( $s['brand_logo'] ),
		'telephone' => $contact['phone'],
		'email'     => $contact['email'],
		'address'   => [ '@type' => 'PostalAddress', 'streetAddress' => $contact['address'], 'addressCountry' => 'IN' ],
		'hasMap'    => itp_real_url( $contact['map_url'] ?? '' ),
		'sameAs'    => array_values( array_filter( array_map( 'itp_real_url', (array) ( itp_content()['companySocial'] ?? [] ) ) ) ),
	] );
	$founder = itp_content()['founderData'] ?? [];
	if ( ! empty( $founder['name'] ) ) {
		$org['founder'] = array_filter( [
			'@type'    => 'Person',
			'name'     => $founder['name'],
			'jobTitle' => $founder['designation'] ?? '',
			'sameAs'   => array_values( array_filter( array_map( 'itp_real_url', (array) ( $founder['social'] ?? [] ) ) ) ),
		] );
	}
	$graph   = [ $org ];
	foreach ( $s['tracks']['items'] as $track ) {
		$graph[] = [
			'@type'               => 'Course',
			'name'                => $track['name'],
			'description'         => $track['summary'],
			'provider'            => [ '@id' => $org['@id'] ],
			'educationalLevel'    => 'Diploma',
			'teaches'             => itp_skills( (string) $track['skills'] ),
			'timeRequired'        => 'P6M', // Every internship is six months.
			'hasCourseInstance'   => [ '@type' => 'CourseInstance', 'courseMode' => 'Onsite', 'courseWorkload' => 'P6M' ],
		];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}, 5 );
