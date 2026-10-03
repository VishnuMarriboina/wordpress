<?php
/**
 * One-time settings upgrades. Activation saves every default into the database, so later changes to
 * defaults.php never reach existing sites. Each step here replaces a value only while it still holds the
 * old placeholder, so anything an admin typed in is left alone.
 */

defined( 'ABSPATH' ) || exit;

const ITP_SETTINGS_VERSION = 2;

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
		// Invented sample students, colleges and partners must not be public on a live site.
		$saved['samples']['show'] = 0;
		update_option( ITP_OPTION, $saved );
	}
	update_option( 'itp_settings_version', ITP_SETTINGS_VERSION );
}
