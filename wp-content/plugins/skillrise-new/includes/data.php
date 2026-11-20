<?php
/**
 * View data for /new. Everything is read (never written) from the Industrial Training plugin:
 * itp_settings() (Settings → Industrial Training) and itp_content() (content/data.php).
 * No company facts are invented here; FAQ answers are assembled from that same content.
 */

defined( 'ABSPATH' ) || exit;

/** Strips a leading emoji/symbol ("💰 Stipend: …" → "Stipend: …"). */
function srn_strip_icon( string $text ): string {
	return trim( (string) preg_replace( '/^[^\p{L}\p{N}₹]+/u', '', $text ) );
}

/** Logo URL from the Industrial Training settings (bundled file or URL). */
function srn_logo_url(): string {
	$s = itp_settings();
	return itp_real_url( $s['brand_logo'] ?? '' ) ?: itp_local_url( $s['brand_logo'] ?? '' );
}

/** An offer card from the settings, looked up by its photo key (safety, certificate, placement, stay…). */
function srn_offer( string $key ): array {
	foreach ( (array) ( itp_settings()['offers']['items'] ?? [] ) as $item ) {
		if ( ( $item['photo'] ?? '' ) === $key ) {
			return $item;
		}
	}
	return [];
}

/** Icon id (from templates/icons.php) for a known content key; '' falls back to the emoji in the settings. */
function srn_icon_for( string $key ): string {
	$map = [
		// Offer cards (photo key).
		'safety' => 'shield', 'certificate' => 'award', 'placement' => 'briefcase', 'stay' => 'home',
		'everyone' => 'users', 'criteria' => 'clipboard', 'colleges' => 'school', 'naps' => 'cap',
		// Track photos.
		'cnc' => 'gear', 'plc' => 'bolt', 'iot' => 'chip', 'civil' => 'building', 'web' => 'code',
		// Steps (photo key).
		'step-register' => 'edit', 'step-call' => 'phone', 'step-train' => 'tool', 'step-career' => 'cap',
	];
	return $map[ $key ] ?? '';
}

/** Highlight ("Why train with us") icons by position, matching the default content. */
function srn_highlight_icon( int $i ): string {
	return [ 'factory', 'mentor', 'award', 'briefcase' ][ $i ] ?? 'spark';
}

function srn_about_icon( int $i ): string {
	return [ 'target', 'handshake', 'chart' ][ $i ] ?? 'spark';
}

/**
 * Skills grouped by track, taken from each track's "skills" field.
 *
 * @return array<int, array{track: string, icon: string, skills: string[]}>
 */
function srn_skill_groups(): array {
	$groups = [];
	foreach ( (array) ( itp_settings()['tracks']['items'] ?? [] ) as $track ) {
		$skills = itp_skills( (string) ( $track['skills'] ?? '' ) );
		if ( $skills ) {
			$groups[] = [ 'track' => (string) $track['name'], 'icon' => srn_icon_for( (string) ( $track['photo'] ?? '' ) ) ?: 'spark', 'skills' => $skills ];
		}
	}
	return $groups;
}

/**
 * FAQ built only from existing content (settings + content file). An entry whose source text is empty is left out.
 * Filter `srn_faq` to edit, add or remove questions.
 *
 * @return array<int, array{q: string, a: string}>
 */
function srn_faq(): array {
	$s     = itp_settings();
	$faq   = [];
	$add   = static function ( string $q, array $parts ) use ( &$faq ) {
		$a = trim( implode( ' ', array_filter( array_map( static fn( $p ) => trim( (string) $p ), $parts ) ) ) );
		if ( '' !== $a ) {
			$faq[] = [ 'q' => $q, 'a' => $a ];
		}
	};
	$about  = $s['about']['points'] ?? [];
	$naps   = $s['offers']['naps'] ?? [];
	$steps  = $s['steps']['items'] ?? [];
	$tracks = itp_track_names();

	$add( __( 'Who can join the Industrial Training Program?', 'skillrise-new' ), [
		$about[1]['text'] ?? '',
		srn_offer( 'everyone' )['text'] ?? '',
	] );
	$add( __( 'Is there a minimum pass percentage or college restriction?', 'skillrise-new' ), [
		srn_offer( 'criteria' )['text'] ?? '', srn_offer( 'criteria' )['note'] ?? '',
		srn_offer( 'colleges' )['text'] ?? '',
	] );
	if ( ! empty( $s['hero']['eligible_title'] ) ) {
		$add( __( 'Can students below 18 join?', 'skillrise-new' ), [
			srn_strip_icon( (string) ( $naps['eligible'] ?? '' ) ) . ( empty( $naps['eligible'] ) ? '' : '.' ),
			$naps['text'] ?? '', $naps['note'] ?? '',
		] );
	}
	$add( __( 'How long is the training and when do batches start?', 'skillrise-new' ), [
		/* translators: %s: duration, e.g. "6 months" */
		empty( $s['batches']['duration'] ) ? '' : sprintf( __( 'Every training track runs for %s.', 'skillrise-new' ), $s['batches']['duration'] ),
		$steps[1]['text'] ?? '',
	] );
	if ( ! empty( $s['offers']['stipend'] ) ) {
		$add( __( 'Is there a stipend?', 'skillrise-new' ), [ srn_strip_icon( (string) $s['offers']['stipend'] ) . '.' ] );
	}
	$add( __( 'Will I get a certificate?', 'skillrise-new' ), [ srn_offer( 'certificate' )['text'] ?? '' ] );
	$add( __( 'Do you help with careers and placements?', 'skillrise-new' ), [ srn_offer( 'placement' )['text'] ?? '' ] );
	$add( __( 'Is accommodation available during the training?', 'skillrise-new' ), [ srn_offer( 'stay' )['text'] ?? '', srn_offer( 'stay' )['note'] ?? '' ] );
	if ( $tracks ) {
		/* translators: %s: list of track names */
		$add( __( 'Which training tracks can I choose from?', 'skillrise-new' ), [ sprintf( __( 'You can choose from: %s.', 'skillrise-new' ), implode( ', ', $tracks ) ), $s['tracks']['lead'] ?? '' ] );
	}
	$add( __( 'What happens after I register?', 'skillrise-new' ), [ $steps[1]['text'] ?? '', $s['contact']['cta_text'] ?? '' ] );

	return (array) apply_filters( 'srn_faq', $faq );
}

/** WhatsApp link with the same pre-filled message as the classic site. */
function srn_whatsapp_url(): string {
	$digits = preg_replace( '/\D/', '', (string) ( itp_settings()['contact']['whatsapp'] ?? '' ) );
	$digits = 10 === strlen( $digits ) ? '91' . $digits : $digits;
	return $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode( __( 'Hi, I would like to know more about the Industrial Training program.', 'industrial-training' ) ) : '';
}

/** Footer legal links: only ones that exist. Privacy = WordPress's Privacy Policy page; Terms via the `srn_terms_url` filter. */
function srn_legal_links(): array {
	$links   = [];
	$privacy = get_privacy_policy_url();
	if ( $privacy ) {
		$links[] = [ __( 'Privacy Policy', 'skillrise-new' ), $privacy ];
	}
	$terms = (string) apply_filters( 'srn_terms_url', '' );
	if ( '' !== $terms ) {
		$links[] = [ __( 'Terms and Conditions', 'skillrise-new' ), $terms ];
	}
	return $links;
}
