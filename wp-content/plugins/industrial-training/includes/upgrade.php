<?php
/**
 * One-time settings upgrades. Activation saves every default into the database, so later changes to
 * defaults.php never reach existing sites. Each step here replaces a value only while it still holds the
 * old placeholder, so anything an admin typed in is left alone.
 */

defined( 'ABSPATH' ) || exit;

const ITP_SETTINGS_VERSION = 4;

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
			[ [ 'contact', 'phone' ], [ '+91 98765 43210', '' ] ],
			[ [ 'form', 'notify_email' ], [ '' ] ],
			[ [ 'footer', 'copyright' ], [ '© {year} Industrial Training Program. All rights reserved.' ] ],
			[ [ 'seo', 'title' ], [ 'Industrial Training Program for Diploma Students' ] ],
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
		// Invented sample students, colleges and partners must not be public on a live site.
		$saved['samples']['show'] = 0;
		update_option( ITP_OPTION, $saved );
	}
	update_option( 'itp_settings_version', ITP_SETTINGS_VERSION );
}
