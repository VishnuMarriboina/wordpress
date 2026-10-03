<?php
/**
 * Founder / students / colleges / partners content: loading, placeholder filtering,
 * the reusable social-links component and avatar/logo images.
 */

defined( 'ABSPATH' ) || exit;

/** Content arrays. wp-content/industrial-training/content.php overrides the bundled sample file. */
function itp_content(): array {
	static $data = null;
	if ( null === $data ) {
		$custom = WP_CONTENT_DIR . '/industrial-training/content.php';
		$data   = include ( is_readable( $custom ) ? $custom : ITP_DIR . 'content/data.php' );
		$data   = is_array( $data ) ? $data : [];
		/** Filter: modify or replace the content arrays. */
		$data = (array) apply_filters( 'itp_content', $data );
	}
	return $data;
}

function itp_is_editor(): bool {
	return current_user_can( 'edit_pages' );
}

/** Whether sample entries render: only while the "show samples" setting is on (for everyone, so editors see what visitors see). */
function itp_preview_mode(): bool {
	return (bool) apply_filters( 'itp_preview_placeholders', ! empty( itp_settings()['samples']['show'] ) );
}

/** Entries that should render for the current viewer. */
function itp_items( string $key ): array {
	$items   = itp_content()[ $key ] ?? [];
	$preview = itp_preview_mode();
	return array_values( array_filter( is_array( $items ) ? $items : [], static fn( $i ) => is_array( $i ) && ( empty( $i['placeholder'] ) || $preview ) ) );
}

function itp_is_placeholder( array $item ): bool {
	return ! empty( $item['placeholder'] );
}

/** Verification label, only for verified, non-placeholder entries. */
function itp_badge( array $item, string $label ): string {
	if ( itp_is_placeholder( $item ) ) {
		return '<span class="itp-tag itp-tag-sample">' . esc_html__( 'Sample', 'industrial-training' ) . '</span>';
	}
	if ( ! empty( $item['verified'] ) && '' !== $label ) {
		return '<span class="itp-tag itp-tag-ok"><span aria-hidden="true">✓</span> ' . esc_html( $label ) . '</span>';
	}
	return '';
}

/** Real http(s) URL, or '' for empty / "#" / anything else. */
function itp_real_url( $url ): string {
	$url = is_string( $url ) ? trim( $url ) : '';
	return preg_match( '#^https?://[^\s/]+\.[^\s]+#i', $url ) ? $url : '';
}

/** URL of an image bundled with the plugin ('assets/images/x.jpg'), or '' if the file isn't there. */
function itp_local_url( $path ): string {
	$path = is_string( $path ) ? ltrim( trim( $path ), '/' ) : '';
	if ( '' === $path || str_contains( $path, '..' ) || ! preg_match( '/\.(jpe?g|png|webp|avif|gif|svg)$/i', $path ) || ! is_file( ITP_DIR . $path ) ) {
		return '';
	}
	return ITP_URL . $path . '?v=' . filemtime( ITP_DIR . $path );
}

function itp_social_networks(): array {
	// Stroke icons, 24×24, currentColor.
	return [
		'linkedin'  => [ 'LinkedIn', '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>' ],
		'instagram' => [ 'Instagram', '<rect width="20" height="20" x="2" y="2" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>' ],
		'facebook'  => [ 'Facebook', '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>' ],
		'twitter'   => [ 'X (Twitter)', '<path d="M4 4l11.7 16H20L8.3 4z"/><path d="M4 20l6.8-7.5M13.2 11.5 20 4"/>' ],
		'youtube'   => [ 'YouTube', '<path d="M2.5 17a24 24 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.6 49.6 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24 24 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.6 49.6 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>' ],
		'github'    => [ 'GitHub', '<path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.1-1.3-.3-2.5-1-3.5.3-1.2.3-2.4 0-3.5 0 0-1 0-3 1.5a13.4 13.4 0 0 0-8 0C6 2 5 2 5 2c-.3 1.2-.3 2.4 0 3.5A5.4 5.4 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.4.5-.7 1-.9 1.7-.2.6-.2 1.2-.1 1.8v4"/><path d="M9 18c-4.5 2-5-2-7-2"/>' ],
		'website'   => [ 'Website', '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/>' ],
	];
}

/**
 * Reusable social-links list. Renders only networks with a real URL; returns '' when there are none.
 *
 * @param array  $social [ network => url ].
 * @param string $owner  Whose profiles these are, for accessible labels ("Y Arun Kumar Reddy on LinkedIn").
 */
function itp_social_links( $social, string $owner, string $class = '' ): string {
	$items = '';
	foreach ( itp_social_networks() as $key => [ $label, $icon ] ) {
		$url = itp_real_url( $social[ $key ] ?? '' );
		if ( '' === $url ) {
			continue;
		}
		/* translators: 1: person or organisation, 2: network name */
		$aria   = sprintf( __( '%1$s on %2$s (opens in a new tab)', 'industrial-training' ), $owner, $label );
		$items .= sprintf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s" title="%3$s"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg></a></li>',
			esc_url( $url ),
			esc_attr( $aria ),
			esc_attr( $label ),
			$icon // Static markup from itp_social_networks().
		);
	}
	return $items ? '<ul class="itp-social ' . esc_attr( $class ) . '" role="list">' . $items . '</ul>' : '';
}

/**
 * Photo or logo: attachment ID, URL, a file inside the plugin (e.g. 'assets/images/x.jpg'),
 * or an initials avatar when empty.
 *
 * @param mixed  $src  Attachment ID, URL, plugin-relative path or ''.
 * @param string $alt  Alt text.
 * @param string $name Used for the initials fallback.
 * @param int    $size Rendered size in px (square).
 */
function itp_avatar( $src, string $alt, string $name, int $size, string $class = 'itp-avatar' ): string {
	if ( is_numeric( $src ) && (int) $src > 0 && wp_attachment_is_image( (int) $src ) ) {
		return wp_get_attachment_image( (int) $src, [ $size * 2, $size * 2 ], false, [ 'class' => $class, 'alt' => $alt, 'sizes' => $size . 'px', 'loading' => 'lazy', 'decoding' => 'async' ] );
	}
	$url = itp_real_url( $src ) ?: itp_local_url( $src );
	if ( '' !== $url ) {
		return sprintf( '<img class="%1$s" src="%2$s" alt="%3$s" width="%4$d" height="%4$d" loading="lazy" decoding="async">', esc_attr( $class ), esc_url( $url ), esc_attr( $alt ), $size );
	}
	$words    = preg_split( '/\s+/', trim( preg_replace( '/[^\p{L}\p{N}\s]/u', '', $name ) ) );
	$initials = mb_strtoupper( mb_substr( $words[0] ?? '', 0, 1 ) . ( count( $words ) > 1 ? mb_substr( end( $words ), 0, 1 ) : '' ) );
	return sprintf( '<span class="%1$s itp-initials" aria-hidden="true">%2$s</span>', esc_attr( $class ), esc_html( $initials ?: '?' ) );
}

/**
 * Internship period for a student card: "Jan 2025 → Jun 2025", a Completed / In progress badge with the
 * duration, and a small bar. Dates are "Mon YYYY"; an empty 'end' means the internship is still running.
 */
function itp_internship_period( array $st ): string {
	$start = trim( (string) ( $st['start'] ?? '' ) );
	$end   = trim( (string) ( $st['end'] ?? '' ) );
	if ( '' === $start ) {
		return '';
	}
	$months = '';
	$from   = DateTime::createFromFormat( '!M Y', $start );
	$to     = '' !== $end ? DateTime::createFromFormat( '!M Y', $end ) : new DateTime( 'first day of this month' );
	if ( $from && $to && $to >= $from ) {
		$diff = $from->diff( $to );
		$n    = $diff->y * 12 + $diff->m; // May → November = 6 months.
		/* translators: %d: number of months */
		$months = sprintf( _n( '%d month', '%d months', $n, 'industrial-training' ), $n );
	}
	$done  = '' !== $end;
	$label = $done ? __( 'Completed', 'industrial-training' ) : __( 'In progress', 'industrial-training' );
	return sprintf(
		'<div class="itp-period%1$s"><p class="itp-period-dates"><span aria-hidden="true">🗓️</span> <span class="itp-sr">%2$s </span>%3$s <span aria-hidden="true">→</span><span class="itp-sr"> %4$s </span> %5$s</p><span class="itp-period-bar" aria-hidden="true"><span></span></span><p class="itp-period-badge">%6$s%7$s</p></div>',
		$done ? ' is-done' : '',
		esc_html__( 'Internship from', 'industrial-training' ),
		esc_html( $start ),
		esc_html__( 'to', 'industrial-training' ),
		esc_html( $done ? $end : __( 'present', 'industrial-training' ) ),
		$done ? '<span aria-hidden="true">✓</span> ' . esc_html( $label ) : esc_html( $label ),
		$months ? ' · ' . esc_html( $months ) : ''
	);
}

/** Monogram for an organisation: first letters of up to 3 words, skipping "of", "and", "for", "the". */
function itp_initials( string $name ): string {
	$words = preg_split( '/\s+/', trim( preg_replace( '/[^\p{L}\p{N}\s]/u', '', $name ) ) );
	$words = array_filter( $words, static fn( $w ) => '' !== $w && ! in_array( strtolower( $w ), [ 'of', 'and', 'for', 'the' ], true ) );
	return mb_strtoupper( implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_slice( array_values( $words ), 0, 3 ) ) ) );
}

/** Stat with count-up markup (animated span hidden from screen readers, final value in .itp-sr). */
function itp_stat_value( string $value ): string {
	if ( ! preg_match( '/\d/', $value ) ) {
		return esc_html( $value );
	}
	return '<span class="itp-count" aria-hidden="true">' . esc_html( $value ) . '</span><span class="itp-sr">' . esc_html( $value ) . '</span>';
}

/** Notice above sections that contain sample entries (wording differs for editors and visitors). */
function itp_preview_note( array $items ): string {
	foreach ( $items as $item ) {
		if ( itp_is_placeholder( $item ) ) {
			$text = itp_is_editor()
				? ( empty( itp_settings()['samples']['show'] )
					? __( 'Editor preview: cards labelled “Sample” are invented test content and are hidden from visitors. Replace them with verified details in the content file, then set placeholder to false.', 'industrial-training' )
					: __( 'Editor note: cards labelled “Sample” are invented test content and are currently visible to visitors. Replace them with real entries, or switch off “Show sample content to visitors” before launch.', 'industrial-training' ) )
				: __( 'Sample content shown for demonstration.', 'industrial-training' );
			return '<p class="itp-preview-note' . ( itp_is_editor() ? '' : ' itp-preview-public' ) . '" role="note">' . esc_html( $text ) . '</p>';
		}
	}
	return '';
}

/** Star rating (1–5). Purely visual stars with a text equivalent. */
function itp_stars( $rating ): string {
	// Half steps (4.5 etc.): rounded to the nearest 0.5, between 0 and 5.
	$rating = max( 0, min( 5, round( (float) $rating * 2 ) / 2 ) );
	if ( ! $rating ) {
		return '';
	}
	$shown = rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' ); // 4.5, 5, 4
	/* translators: %s: rating out of 5, e.g. 4.5 */
	$label = sprintf( __( 'Rated %s out of 5', 'industrial-training' ), $shown );
	// Five grey stars with five coloured stars on top, clipped to the rating's share of the width.
	return '<p class="itp-stars" role="img" aria-label="' . esc_attr( $label ) . '">'
		. '<span class="itp-stars-row" aria-hidden="true" style="--fill:' . esc_attr( $rating / 5 * 100 ) . '%">'
		. '<span class="itp-stars-off">★★★★★</span><span class="itp-stars-on">★★★★★</span></span>'
		. '<span class="itp-stars-num" aria-hidden="true">' . esc_html( $shown ) . '</span></p>';
}

/** Carousel controls (prev/next + dots container filled by JS). */
function itp_carousel_controls( string $label ): string {
	return '<div class="itp-car-nav">'
		. '<button type="button" class="itp-car-btn" data-itp-prev aria-label="' . esc_attr( sprintf( /* translators: %s: carousel name */ __( 'Previous: %s', 'industrial-training' ), $label ) ) . '"><span aria-hidden="true">←</span></button>'
		. '<div class="itp-car-dots" data-itp-dots></div>'
		. '<button type="button" class="itp-car-btn" data-itp-next aria-label="' . esc_attr( sprintf( /* translators: %s: carousel name */ __( 'Next: %s', 'industrial-training' ), $label ) ) . '"><span aria-hidden="true">→</span></button>'
		. '</div>';
}
