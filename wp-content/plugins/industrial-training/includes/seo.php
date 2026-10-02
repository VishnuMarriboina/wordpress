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
	$org     = [
		'@type'     => 'EducationalOrganization',
		'@id'       => home_url( '/#itp-organization' ),
		'name'      => $s['seo']['title'],
		'url'       => get_permalink( get_queried_object_id() ),
		'telephone' => $contact['phone'],
		'email'     => $contact['email'],
		'address'   => [ '@type' => 'PostalAddress', 'streetAddress' => $contact['address'], 'addressCountry' => 'IN' ],
	];
	$graph   = [ $org ];
	foreach ( $s['tracks']['items'] as $track ) {
		$graph[] = [
			'@type'               => 'Course',
			'name'                => $track['name'],
			'description'         => $track['summary'],
			'provider'            => [ '@id' => $org['@id'] ],
			'educationalLevel'    => 'Diploma',
			'teaches'             => itp_skills( (string) $track['skills'] ),
			'timeRequired'        => 'P' . (int) $track['weeks'] . 'W',
			'hasCourseInstance'   => [ '@type' => 'CourseInstance', 'courseMode' => 'Onsite', 'courseWorkload' => 'P' . (int) $track['weeks'] . 'W' ],
		];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}, 5 );
