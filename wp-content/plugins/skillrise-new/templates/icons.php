<?php
/**
 * Inline SVG sprite (24×24 stroke icons, currentColor). Use: srn_icon( 'gear' ).
 * Drawn in the style of the Lucide set (ISC licence).
 */

defined( 'ABSPATH' ) || exit;

function srn_icons(): array {
	return [
		'shield'    => '<path d="M20 13c0 5-3.5 7.5-7.7 9a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1.2 1.2 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
		'award'     => '<circle cx="12" cy="8" r="6"/><path d="M15.5 12.9 17 22l-5-3-5 3 1.5-9.1"/>',
		'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
		'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9v12h14V9"/><path d="M10 21v-6h4v6"/>',
		'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
		'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
		'school'    => '<path d="M14 22v-4a2 2 0 1 0-4 0v4"/><path d="m18 10 4 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8l4-2"/><path d="M18 5v17M6 5v17"/><path d="m4 6 8-4 8 4"/><circle cx="12" cy="9" r="2"/>',
		'cap'       => '<path d="M22 10 12 5 2 10l10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/><path d="M22 10v6"/>',
		'gear'      => '<path d="M12.2 2h-.4a2 2 0 0 0-2 2v.2a2 2 0 0 1-1 1.7l-.4.3a2 2 0 0 1-2 0l-.2-.1a2 2 0 0 0-2.7.7l-.2.4a2 2 0 0 0 .7 2.7l.2.1a2 2 0 0 1 1 1.7v.5a2 2 0 0 1-1 1.8l-.2.1a2 2 0 0 0-.7 2.7l.2.4a2 2 0 0 0 2.7.7l.2-.1a2 2 0 0 1 2 0l.4.3a2 2 0 0 1 1 1.7v.2a2 2 0 0 0 2 2h.4a2 2 0 0 0 2-2v-.2a2 2 0 0 1 1-1.7l.4-.3a2 2 0 0 1 2 0l.2.1a2 2 0 0 0 2.7-.7l.2-.4a2 2 0 0 0-.7-2.7l-.2-.1a2 2 0 0 1-1-1.8v-.5a2 2 0 0 1 1-1.7l.2-.1a2 2 0 0 0 .7-2.7l-.2-.4a2 2 0 0 0-2.7-.7l-.2.1a2 2 0 0 1-2 0l-.4-.3a2 2 0 0 1-1-1.7V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
		'bolt'      => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
		'chip'      => '<rect width="14" height="14" x="5" y="5" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>',
		'building'  => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
		'code'      => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
		'edit'      => '<path d="M12 20h9"/><path d="M16.4 3.6a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/>',
		'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'tool'      => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/>',
		'factory'   => '<path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M17 18h1M12 18h1M7 18h1"/>',
		'mentor'    => '<circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/><path d="m12 13 1.5 3-1.5 2-1.5-2z"/>',
		'target'    => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
		'handshake' => '<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.9-3.9a3 3 0 0 0-4.2 0l-.9.9a1 1 0 1 1-3-3l2.8-2.8a5.8 5.8 0 0 1 7.1-.9l.5.3a4 4 0 0 0 3 .5L21 4"/><path d="m21 3 1 11h-2M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3M3 4h8"/>',
		'chart'     => '<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 6-7"/><path d="M15 7h5v5"/>',
		'spark'     => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
		'mail'      => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'pin'       => '<path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
		'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'chat'      => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22z"/>',
		'arrow'     => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'arrow-up'  => '<path d="m5 12 7-7 7 7M12 19V5"/>',
		'external'  => '<path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
		'check'     => '<path d="M20 6 9 17l-5-5"/>',
		'plus'      => '<path d="M12 5v14M5 12h14"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'     => '<path d="M18 6 6 18M6 6l12 12"/>',
		'calendar'  => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'rupee'     => '<path d="M6 3h12M6 8h12M6 13l8.5 8M6 13h3a5 5 0 0 0 0-10"/>',
		'quote'     => '<path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2zM5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>',
		'map'       => '<path d="M14.1 5.1 9.9 2.9a2 2 0 0 0-1.8 0L3.6 5.2A1 1 0 0 0 3 6v14a1 1 0 0 0 1.4.9l3.7-1.9a2 2 0 0 1 1.8 0l4.2 2.1a2 2 0 0 0 1.8 0l4.5-2.2A1 1 0 0 0 21 18V4a1 1 0 0 0-1.4-.9L15.9 5a2 2 0 0 1-1.8 0z"/><path d="M15 5.8v15M9 3.2v15"/>',
		'cake'      => '<path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1M2 21h20M7 8v3M12 8v3M17 8v3M7 4h.01M12 4h.01M17 4h.01"/>',
	];
}

/** <svg> referencing a symbol in the sprite. Decorative by default. */
function srn_icon( string $id, string $class = 'srn-ico' ): string {
	if ( '' === $id || ! isset( srn_icons()[ $id ] ) ) {
		$id = 'spark';
	}
	return '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"><use href="#srn-i-' . esc_attr( $id ) . '"></use></svg>';
}

/** The sprite itself, printed once at the top of <body>. */
function srn_icon_sprite(): string {
	$out = '<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true" focusable="false">';
	foreach ( srn_icons() as $id => $paths ) {
		$out .= '<symbol id="srn-i-' . esc_attr( $id ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</symbol>';
	}
	return $out . '</svg>';
}

/**
 * Animated badge for a training track (inline SVG, animated with CSS in skillrise-new.css → "Track badges").
 * The resting state of each drawing is complete, so it reads the same with reduced motion.
 * Unknown tracks (e.g. one added later in Settings) fall back to the static icon.
 *
 * @param string $key Track photo key: cnc, plc, iot, civil, web.
 */
function srn_track_anim( string $key ): string {
	$svg = [
		// Manufacturing & CNC: gear turning while a cutting tool feeds in; chips fly off.
		'cnc'   => '<g class="srn-a-gear"><circle cx="26" cy="34" r="13" fill="none" stroke="currentColor" stroke-width="6" stroke-dasharray="4.1 4.1"/><circle cx="26" cy="34" r="9" fill="none" stroke="currentColor" stroke-width="2.5"/><circle cx="26" cy="34" r="3" fill="currentColor"/></g>'
			. '<g class="srn-a-tool"><path d="M56 22 44 30l3 4 12-5z" fill="#fde68a"/><path d="M58 16v8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></g>'
			. '<g class="srn-a-chips" fill="#fde68a"><circle cx="44" cy="40" r="1.6"/><circle cx="47" cy="44" r="1.2"/><circle cx="42" cy="45" r="1"/></g>',
		// Electrical & PLC: current runs along the circuit, the switch closes, the lamp lights.
		'plc'   => '<path d="M10 46V20h16M38 20h16v26H40" fill="none" stroke="currentColor" stroke-opacity=".45" stroke-width="2.5" stroke-linejoin="round"/>'
			. '<path class="srn-a-current" d="M10 46V20h16M38 20h16v26H40" fill="none" stroke="#fde68a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="6 10"/>'
			. '<circle cx="26" cy="20" r="2.5" fill="currentColor"/><circle cx="38" cy="20" r="2.5" fill="currentColor"/><path class="srn-a-switch" d="M26 20l12 0" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>'
			. '<g class="srn-a-lamp"><circle cx="30" cy="46" r="8" fill="#fde68a"/><path d="M27 46h6M30 43v6" stroke="#b45309" stroke-width="1.6" stroke-linecap="round"/></g><path d="M10 46h12" stroke="currentColor" stroke-width="2.5"/>',
		// PCB & Electronics: signals travel along the traces from the chip; pads blink.
		'iot'   => '<g fill="none" stroke="currentColor" stroke-opacity=".45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 26H14V14M40 26h10V12M24 38H12v10M40 38h12v12M32 22V8M32 42v14"/></g>'
			. '<g class="srn-a-trace" fill="none" stroke="#67e8f9" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" pathLength="100" stroke-dasharray="18 82"><path pathLength="100" d="M24 26H14V14"/><path pathLength="100" d="M40 26h10V12"/><path pathLength="100" d="M24 38H12v10"/><path pathLength="100" d="M40 38h12v12"/><path pathLength="100" d="M32 22V8"/><path pathLength="100" d="M32 42v14"/></g>'
			. '<rect x="22" y="22" width="20" height="20" rx="3" fill="currentColor"/><rect x="27" y="27" width="10" height="10" rx="1.5" fill="#7c4dff"/>'
			. '<g class="srn-a-pads" fill="#67e8f9"><circle cx="14" cy="14" r="2.2"/><circle cx="50" cy="12" r="2.2"/><circle cx="12" cy="48" r="2.2"/><circle cx="52" cy="50" r="2.2"/><circle cx="32" cy="8" r="2.2"/><circle cx="32" cy="56" r="2.2"/></g>',
		// Civil & AutoCAD: the building rises floor by floor on its plot, a crane swings.
		'civil' => '<path d="M6 56h52" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>'
			. '<g class="srn-a-floors" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"><rect class="srn-a-f1" x="14" y="46" width="22" height="10"/><rect class="srn-a-f2" x="14" y="36" width="22" height="10"/><rect class="srn-a-f3" x="14" y="26" width="22" height="10"/><rect class="srn-a-f4" x="14" y="16" width="22" height="10"/></g>'
			. '<g fill="#fde68a"><rect class="srn-a-f1" x="18" y="49" width="4" height="4"/><rect class="srn-a-f2" x="28" y="39" width="4" height="4"/><rect class="srn-a-f3" x="18" y="29" width="4" height="4"/><rect class="srn-a-f4" x="28" y="19" width="4" height="4"/></g>'
			. '<path d="M48 56V10" stroke="currentColor" stroke-width="2.5"/><g class="srn-a-crane"><path d="M48 10H30M48 10h8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><path d="M32 10v7" stroke="#fde68a" stroke-width="1.6"/><rect x="29.5" y="17" width="5" height="4" fill="#fde68a"/></g>',
		// Software & Web: code lines type out and a cursor blinks between the brackets.
		'web'   => '<path d="M18 18 8 32l10 14M46 18l10 14-10 14" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>'
			. '<g class="srn-a-code" fill="#67e8f9"><rect class="srn-a-l1" x="22" y="22" width="16" height="3.5" rx="1.75"/><rect class="srn-a-l2" x="26" y="30" width="14" height="3.5" rx="1.75" fill="#fde68a"/><rect class="srn-a-l3" x="26" y="38" width="10" height="3.5" rx="1.75"/></g>'
			. '<rect class="srn-a-cursor" x="38" y="37" width="2.4" height="6" fill="currentColor"/>',
	];
	if ( ! isset( $svg[ $key ] ) ) {
		return srn_icon( srn_icon_for( $key ) );
	}
	return '<svg class="srn-track-anim srn-anim-' . esc_attr( $key ) . '" viewBox="0 0 64 64" aria-hidden="true" focusable="false">' . $svg[ $key ] . '</svg>';
}

/**
 * Full animated scene for a track card's media area (16:10), used instead of a photo.
 * The track drawing sits in a nested <svg> so its own coordinates (and rotation origins) stay intact.
 * Returns '' for tracks without a drawing, so the caller can show the photo instead.
 */
function srn_track_scene( string $key ): string {
	$art = srn_track_anim( $key );
	if ( ! str_contains( $art, 'srn-track-anim' ) ) {
		return '';
	}
	$inner = preg_replace( '#^<svg[^>]*>|</svg>$#', '', $art );
	return '<div class="srn-track-scene srn-scene-' . esc_attr( $key ) . '" aria-hidden="true">'
		. '<svg class="srn-scene-svg srn-anim-' . esc_attr( $key ) . '" viewBox="0 0 160 100" preserveAspectRatio="xMidYMid meet" focusable="false">'
		. '<circle class="srn-s-glow" cx="80" cy="48" r="34"/>'
		. '<circle class="srn-s-ring" cx="80" cy="48" r="42" fill="none"/>'
		. '<g class="srn-s-dots"><circle cx="22" cy="24" r="1.6"/><circle cx="138" cy="20" r="1.3"/><circle cx="146" cy="70" r="1.8"/><circle cx="16" cy="76" r="1.2"/><circle cx="118" cy="88" r="1.4"/><circle cx="40" cy="90" r="1.1"/></g>'
		. '<svg x="46" y="12" width="68" height="68" viewBox="0 0 64 64" overflow="visible">' . $inner . '</svg>'
		. '</svg></div>';
}
