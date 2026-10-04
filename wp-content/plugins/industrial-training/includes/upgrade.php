<?php
/**
 * One-time settings upgrades. Activation saves every default into the database, so later changes to
 * defaults.php never reach existing sites. Each step here replaces a value only while it still holds the
 * old placeholder, so anything an admin typed in is left alone.
 */

defined( 'ABSPATH' ) || exit;

const ITP_SETTINGS_VERSION = 17;

add_action( 'plugins_loaded', 'itp_upgrade_settings' );

function itp_upgrade_settings(): void {
	if ( (int) get_option( 'itp_settings_version', 0 ) >= ITP_SETTINGS_VERSION ) {
		return;
	}
	$saved = get_option( ITP_OPTION );
	if ( is_array( $saved ) ) {
		$new = itp_defaults();
		// [ path, old placeholder values ] — replaced with the current default.
		$steps = [
			[ [ 'brand_mark' ], [ 'IT' ] ],
			[ [ 'brand_name' ], [ 'Industrial Training' ] ],
			[ [ 'contact', 'email' ], [ 'training@example.com', '' ] ],
			[ [ 'contact', 'address' ], [ 'Training Centre, Industrial Estate, Hyderabad, Telangana', '' ] ],
			// Phone and WhatsApp are both +91 86393 54430; 93929 20858 was the earlier WhatsApp number.
			[ [ 'contact', 'phone' ], [ '+91 98765 43210', '+91 93929 20858', '' ] ],
			[ [ 'contact', 'whatsapp' ], [ '+91 93929 20858' ] ],
			[ [ 'form', 'notify_email' ], [ '', 'info@skillrisetechnologies.com, arunreddy@skillrisetechnologies.com' ] ],
			[ [ 'footer', 'copyright' ], [ '© {year} Industrial Training Program. All rights reserved.' ] ],
			[ [ 'seo', 'title' ], [ 'Industrial Training Program for Diploma Students' ] ],
			[ [ 'form', 'note' ], [ 'Year of study and training track are optional; all other fields are required.' ] ], // Year and track fields were removed from the form.
			[ [ 'hero', 'badge' ], [ 'Admissions open · Next batch starts soon' ] ],
			[ [ 'hero', 'lead' ], [ 'Six to eight weeks of hands-on work in real plants, labs and project teams. Learn the tools industry uses, build a portfolio, and leave with a certificate and placement support.' ] ],
			[ [ 'offers', 'age' ], [ '🎂 Age: 14+ years (18+ for hazardous trades)' ] ],
			[ [ 'footer', 'credits' ], [ 'Photos: <a href="https://unsplash.com/">Unsplash</a> contributors, used under the <a href="https://unsplash.com/license">Unsplash License</a>.' ] ],
		];
		foreach ( $steps as [ $path, $old ] ) {
			$ref = &$saved;
			foreach ( array_slice( $path, 0, -1 ) as $key ) {
				if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
					continue 2;
				}
				$ref = &$ref[ $key ];
			}
			$last = end( $path );
			if ( ! array_key_exists( $last, $ref ) || in_array( (string) $ref[ $last ], $old, true ) ) {
				$value = $new;
				foreach ( $path as $key ) {
					$value = $value[ $key ];
				}
				$ref[ $last ] = $value;
			}
			unset( $ref );
		}
		// Lists are saved whole, so new defaults (4 photo steps, real hero figures) replace them only
		// while they still hold the original placeholder text.
		if ( 3 === count( $saved['steps']['items'] ?? [] ) && 'Fill the short form. It takes under two minutes.' === ( $saved['steps']['items'][0]['text'] ?? '' ) && 'Join the next batch, work on real projects and earn your certificate.' === ( $saved['steps']['items'][2]['text'] ?? '' ) ) {
			$saved['steps']['items'] = $new['steps']['items'];
		}
		if ( '2,400+' === ( $saved['stats'][0]['value'] ?? '' ) && '85%' === ( $saved['stats'][2]['value'] ?? '' ) ) {
			$saved['stats'] = $new['stats'];
		}
		// Stipend shown in full ("₹17,000 – ₹21,500") and first, in its own tile.
		if ( in_array( '₹17K–21.5K', array_column( (array) ( $saved['stats'] ?? [] ), 'value' ), true ) ) {
			$saved['stats'] = $new['stats'];
		}
		// Company figures confirmed as 10000+ students, 50+ colleges, 6+ years: replace the earlier 10,000+ / 200+ set.
		$values = array_column( (array) ( $saved['stats'] ?? [] ), 'value' );
		if ( in_array( '10,000+', $values, true ) || in_array( '200+', $values, true ) ) {
			$saved['stats'] = $new['stats'];
		}
		if ( str_contains( (string) ( $saved['about']['text2'] ?? '' ), '10,000+ students and work with 200+ colleges' ) ) {
			$saved['about']['text2'] = $new['about']['text2'];
		}
		if ( 'Every batch is guided by engineers with 10+ years on the job.' === ( $saved['highlights']['items'][1]['text'] ?? '' ) ) {
			$saved['highlights']['items'][1]['text'] = $new['highlights']['items'][1]['text'];
		}
		// Stats: drop the "years of experience" tile (years are shown in the founder section) and use clearer labels.
		if ( is_array( $saved['stats'] ?? null ) ) {
			$saved['stats'] = array_values( array_filter( $saved['stats'], static fn( $st ) => ! str_contains( strtolower( (string) ( $st['label'] ?? '' ) ), 'years' ) ) );
			$labels = [
				'monthly stipend'    => 'Monthly stipend during the internship',
				'students trained'   => 'Students trained so far',
				'colleges connected' => 'Partner colleges',
			];
			foreach ( $saved['stats'] as &$st ) {
				$st['label'] = $labels[ strtolower( trim( (string) ( $st['label'] ?? '' ) ) ) ] ?? $st['label'];
			}
			unset( $st );
		}
		// The separate "6 months · May–Nov or Nov–May" banner was removed.
		unset( $saved['offers']['batch'] );
		// Six-month batches (May–Nov, Nov–May): update the step texts that still hold the old wording.
		foreach ( [ 1 => 'Our team calls within 2 working days to confirm your track and batch.', 2 => 'Work on real machines and projects with industry mentors, and earn a stipend of ₹17,000 – ₹21,500 per month.' ] as $i => $old_text ) {
			if ( $old_text === ( $saved['steps']['items'][ $i ]['text'] ?? null ) ) {
				$saved['steps']['items'][ $i ]['text'] = $new['steps']['items'][ $i ]['text'];
			}
		}
		// Placement card note: "does not guarantee employment" became "and guarantee employment".
		foreach ( (array) ( $saved['offers']['items'] ?? [] ) as $i => $item ) {
			if ( 'Placement assistance does not guarantee employment.' === ( $item['note'] ?? '' ) ) {
				$saved['offers']['items'][ $i ]['note'] = 'Placement assistance and guarantee employment.';
			}
		}
		// Electronics track renamed: "Embedded & IoT" → "PCB and electronic Components" for ECE students, with PCB summary and skills.
		foreach ( (array) ( $saved['tracks']['items'] ?? [] ) as $i => $item ) {
			if ( 'Embedded & IoT' === ( $item['name'] ?? '' ) ) {
				$saved['tracks']['items'][ $i ]['name'] = 'PCB and electronic Components';
			}
			if ( 'Electronics · Computer Science' === ( $item['branch'] ?? '' ) ) {
				$saved['tracks']['items'][ $i ]['branch'] = 'ELECTRONICS AND COMMUNICATION';
			}
			if ( 'Build sensor boards, flash microcontrollers and send live machine data to a cloud dashboard.' === ( $item['summary'] ?? '' ) ) {
				$saved['tracks']['items'][ $i ]['summary'] = $new['tracks']['items'][2]['summary'];
			}
			if ( 'Arduino & ESP32, Sensor interfacing, MQTT' === ( $item['skills'] ?? '' ) ) {
				$saved['tracks']['items'][ $i ]['skills'] = $new['tracks']['items'][2]['skills'];
			}
		}
		// Invented sample students, colleges and partners must not be public on a live site.
		$saved['samples']['show'] = 0;
		update_option( ITP_OPTION, $saved );
	}
	update_option( 'itp_settings_version', ITP_SETTINGS_VERSION );
}
