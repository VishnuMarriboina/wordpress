<?php
/**
 * Landing page markup. Variables: $s (settings), $atts (shortcode attributes).
 *
 * @var array $s
 * @var array $atts
 */

defined( 'ABSPATH' ) || exit;

$hero     = $s['hero'];
$contact  = $s['contact'];
$form     = $s['form'];
$tracks   = $s['tracks']['items'];
$main_tag = $atts['main'] ? 'main' : 'div';
$classes  = 'itp' . ( in_array( $atts['align'], [ 'wide', 'full' ], true ) ? ' align' . $atts['align'] : '' );

$opener = static function ( string $label, string $class, string $track = '', string $extra = '' ) {
	printf(
		'<button type="button" class="itp-btn %1$s" data-itp-open aria-haspopup="dialog" aria-controls="itp-register"%2$s>%3$s%4$s</button>',
		esc_attr( $class ),
		$track ? ' data-track="' . esc_attr( $track ) . '"' : '',
		esc_html( $label ),
		$extra // Pre-escaped markup.
	);
};
$arrow = '<span class="itp-arrow" aria-hidden="true">→</span>';

$field = static function ( string $name, string $label, string $control, bool $full = false ) {
	?>
	<div class="itp-field<?php echo $full ? ' itp-field-full' : ''; ?>">
		<label for="itp-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<?php echo $control; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts below. ?>
		<p class="itp-error" id="itp-err-<?php echo esc_attr( $name ); ?>"></p>
	</div>
	<?php
};
$input = static fn( string $name, string $type, array $attr ) => sprintf(
	'<input type="%1$s" id="itp-%2$s" name="%2$s" required aria-describedby="itp-err-%2$s"%3$s>',
	esc_attr( $type ),
	esc_attr( $name ),
	implode( '', array_map( static fn( $k, $v ) => ' ' . $k . '="' . esc_attr( $v ) . '"', array_keys( $attr ), $attr ) )
);
$select = static function ( string $name, string $placeholder, array $options ) {
	$html = sprintf( '<select id="itp-%1$s" name="%1$s" required aria-describedby="itp-err-%1$s"><option value="">%2$s</option>', esc_attr( $name ), esc_html( $placeholder ) );
	foreach ( $options as $value => $label ) {
		$html .= sprintf( '<option value="%s">%s</option>', esc_attr( $value ), esc_html( $label ) );
	}
	return $html . '</select>';
};
?>
<div class="<?php echo esc_attr( $classes ); ?>" data-itp>
	<a class="itp-skip" href="#itp-main"><?php esc_html_e( 'Skip to content', 'industrial-training' ); ?></a>

	<header class="itp-header">
		<div class="itp-container itp-header-in">
			<a class="itp-brand" href="#itp-top">
				<span class="itp-mark" aria-hidden="true"><?php echo esc_html( $s['brand_mark'] ); ?></span>
				<span><?php echo esc_html( $s['brand_name'] ); ?></span>
			</a>
			<nav class="itp-nav" aria-label="<?php esc_attr_e( 'Page sections', 'industrial-training' ); ?>">
				<ul role="list">
					<li><a href="#highlights"><?php echo esc_html( $s['nav']['highlights'] ); ?></a></li>
					<li><a href="#founder"><?php echo esc_html( $s['nav']['founder'] ); ?></a></li>
					<li><a href="#tracks"><?php echo esc_html( $s['nav']['tracks'] ); ?></a></li>
					<li><a href="#contact"><?php echo esc_html( $s['nav']['contact'] ); ?></a></li>
				</ul>
			</nav>
			<?php $opener( $s['nav']['register'], 'itp-btn-primary itp-btn-sm itp-shine' ); ?>
		</div>
		<span class="itp-progress" aria-hidden="true"></span>
	</header>

	<<?php echo esc_html( $main_tag ); ?> id="itp-main" class="itp-main" tabindex="-1">

		<section class="itp-hero" id="itp-top" aria-labelledby="itp-hero-title">
			<div class="itp-hero-bg" aria-hidden="true"><span class="itp-orb"></span></div>
			<div class="itp-container itp-hero-grid">
				<div class="itp-hero-copy">
					<p class="itp-badge itp-rise" style="--i:0"><span class="itp-dot" aria-hidden="true"></span><?php echo esc_html( $hero['badge'] ); ?></p>
					<h1 class="itp-h1 itp-rise" id="itp-hero-title" style="--i:1">
						<?php echo esc_html( $hero['title'] ); ?>
						<span class="itp-h1-accent"><?php echo esc_html( $hero['title_accent'] ); ?></span>
					</h1>
					<p class="itp-hero-lead itp-rise" style="--i:2"><?php echo esc_html( $hero['lead'] ); ?></p>
					<div class="itp-actions itp-rise" style="--i:3">
						<?php $opener( $hero['cta_primary'], 'itp-btn-primary itp-shine' ); ?>
						<a class="itp-btn itp-btn-ghost" href="#tracks"><?php echo esc_html( $hero['cta_secondary'] ); ?> <?php echo $arrow; // phpcs:ignore ?></a>
					</div>
					<dl class="itp-stats itp-rise" style="--i:4">
						<?php foreach ( $s['stats'] as $stat ) : ?>
							<div class="itp-stat">
								<dt><?php echo esc_html( $stat['label'] ); ?></dt>
								<dd><span class="itp-count" aria-hidden="true"><?php echo esc_html( $stat['value'] ); ?></span><span class="itp-sr"><?php echo esc_html( $stat['value'] ); ?></span></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>

				<div class="itp-collage" data-itp-parallax>
					<div class="itp-photo-main">
						<?php echo itp_image( (int) $hero['image'], 'hero', [ 5, 4 ], '(max-width: 1000px) 92vw, 560px', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'auto' ] ); // phpcs:ignore ?>
					</div>
					<div class="itp-photo-inset">
						<?php echo itp_image( (int) $hero['inset_image'], 'inset', [ 4, 3 ], '(max-width: 1000px) 40vw, 250px' ); // phpcs:ignore ?>
					</div>
					<p class="itp-float">
						<span class="itp-float-icon" aria-hidden="true"><?php echo esc_html( $hero['float_icon'] ); ?></span>
						<span><strong><?php echo esc_html( $hero['float_title'] ); ?></strong><span><?php echo esc_html( $hero['float_text'] ); ?></span></span>
					</p>
				</div>
			</div>
			<a class="itp-scroll" href="#highlights" aria-label="<?php echo esc_attr( $hero['scroll_label'] ); ?>"><span class="itp-mouse"><span class="itp-wheel"></span></span></a>
		</section>

		<section class="itp-section" id="highlights" aria-labelledby="itp-highlights-title" data-itp-spy>
			<div class="itp-container">
				<div class="itp-head">
					<h2 class="itp-h2 itp-reveal" id="itp-highlights-title"><?php echo esc_html( $s['highlights']['title'] ); ?></h2>
					<p class="itp-lead itp-reveal"><?php echo esc_html( $s['highlights']['lead'] ); ?></p>
				</div>
				<ul class="itp-grid itp-grid-4" role="list">
					<?php foreach ( $s['highlights']['items'] as $item ) : ?>
						<li class="itp-card itp-hl itp-reveal">
							<span class="itp-hl-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
							<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<p><?php echo esc_html( $item['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<?php include ITP_DIR . 'templates/founder.php'; ?>

		<section class="itp-section itp-alt" id="tracks" aria-labelledby="itp-tracks-title" data-itp-spy>
			<div class="itp-container">
				<div class="itp-head">
					<h2 class="itp-h2 itp-reveal" id="itp-tracks-title"><?php echo esc_html( $s['tracks']['title'] ); ?></h2>
					<p class="itp-lead itp-reveal"><?php echo esc_html( $s['tracks']['lead'] ); ?></p>
				</div>
				<ul class="itp-grid itp-grid-3" role="list">
					<?php foreach ( $tracks as $track ) : ?>
						<li class="itp-card itp-track itp-reveal">
							<div class="itp-track-media">
								<?php echo itp_image( (int) $track['image'], (string) ( $track['photo'] ?? '' ), [ 16, 10 ], '(max-width: 560px) 92vw, (max-width: 1000px) 46vw, 360px' ); // phpcs:ignore ?>
								<span class="itp-weeks">
									<?php /* translators: %d: number of weeks */ echo esc_html( sprintf( _n( '%d week', '%d weeks', (int) $track['weeks'], 'industrial-training' ), (int) $track['weeks'] ) ); ?>
								</span>
							</div>
							<div class="itp-track-body">
								<p class="itp-branch"><?php echo esc_html( $track['branch'] ); ?></p>
								<h3><?php echo esc_html( $track['name'] ); ?></h3>
								<p><?php echo esc_html( $track['summary'] ); ?></p>
								<ul class="itp-chips" role="list" aria-label="<?php esc_attr_e( 'Skills', 'industrial-training' ); ?>">
									<?php foreach ( itp_skills( (string) $track['skills'] ) as $skill ) : ?>
										<li><?php echo esc_html( $skill ); ?></li>
									<?php endforeach; ?>
								</ul>
								<?php $opener( $s['tracks']['button'], 'itp-btn-outline itp-btn-block', $track['name'], '<span class="itp-sr">: ' . esc_html( $track['name'] ) . '</span>' ); ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<section class="itp-section" aria-labelledby="itp-steps-title">
			<div class="itp-container">
				<div class="itp-head">
					<h2 class="itp-h2 itp-reveal" id="itp-steps-title"><?php echo esc_html( $s['steps']['title'] ); ?></h2>
				</div>
				<ol class="itp-steps itp-reveal" role="list">
					<?php foreach ( $s['steps']['items'] as $i => $step ) : ?>
						<li class="itp-step" style="--i:<?php echo (int) $i; ?>">
							<span class="itp-step-num" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
							<h3><?php echo esc_html( $step['title'] ); ?></h3>
							<p><?php echo esc_html( $step['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>

		<?php include ITP_DIR . 'templates/outcomes.php'; ?>

		<section class="itp-section itp-alt" id="contact" aria-labelledby="itp-contact-title" data-itp-spy>
			<div class="itp-container itp-contact">
				<div class="itp-contact-info itp-reveal">
					<h2 class="itp-h2" id="itp-contact-title"><?php echo esc_html( $contact['title'] ); ?></h2>
					<p class="itp-lead"><?php echo esc_html( $contact['lead'] ); ?></p>
					<address class="itp-address">
						<ul role="list">
							<li><span class="itp-ci-icon" aria-hidden="true">📞</span><span><span class="itp-ci-label"><?php esc_html_e( 'Phone', 'industrial-training' ); ?></span><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $contact['phone'] ) ); ?>"><?php echo esc_html( $contact['phone'] ); ?></a></span></li>
							<li><span class="itp-ci-icon" aria-hidden="true">✉️</span><span><span class="itp-ci-label"><?php esc_html_e( 'Email', 'industrial-training' ); ?></span><a href="mailto:<?php echo esc_attr( antispambot( $contact['email'] ) ); ?>"><?php echo esc_html( antispambot( $contact['email'] ) ); ?></a></span></li>
							<li><span class="itp-ci-icon" aria-hidden="true">📍</span><span><span class="itp-ci-label"><?php esc_html_e( 'Address', 'industrial-training' ); ?></span><?php echo esc_html( $contact['address'] ); ?></span></li>
							<li><span class="itp-ci-icon" aria-hidden="true">🕘</span><span><span class="itp-ci-label"><?php esc_html_e( 'Hours', 'industrial-training' ); ?></span><?php echo esc_html( $contact['hours'] ); ?></span></li>
						</ul>
					</address>
				</div>
				<div class="itp-cta itp-reveal">
					<?php echo itp_image( (int) $contact['image'], 'contact', [ 4, 3 ], '(max-width: 760px) 92vw, 540px', [ 'class' => 'itp-cta-img' ] ); // phpcs:ignore ?>
					<div class="itp-cta-body">
						<h3><?php echo esc_html( $contact['cta_title'] ); ?></h3>
						<p><?php echo esc_html( $contact['cta_text'] ); ?></p>
						<?php $opener( $contact['cta_button'], 'itp-btn-primary itp-btn-block itp-shine', '', ' ' . $arrow ); ?>
					</div>
				</div>
			</div>
		</section>
	</<?php echo esc_html( $main_tag ); ?>>

	<footer class="itp-footer">
		<div class="itp-container">
			<p><?php echo esc_html( itp_fill( $s['footer']['copyright'], [ 'year' => wp_date( 'Y' ) ] ) ); ?></p>
			<?php if ( $s['footer']['credits'] ) : ?>
				<p class="itp-credits"><?php echo wp_kses( $s['footer']['credits'], [ 'a' => [ 'href' => true, 'rel' => true, 'target' => true ] ] ); ?></p>
			<?php endif; ?>
		</div>
	</footer>

	<a class="itp-top" href="#itp-top" aria-label="<?php esc_attr_e( 'Back to top', 'industrial-training' ); ?>"><span aria-hidden="true">↑</span></a>

	<dialog class="itp-modal" id="itp-register" aria-labelledby="itp-modal-title"
		data-success-title="<?php echo esc_attr( $form['success_title'] ); ?>"
		data-success-text="<?php echo esc_attr( $form['success_text'] ); ?>">
		<div class="itp-modal-in">
			<button type="button" class="itp-close" data-itp-close aria-label="<?php esc_attr_e( 'Close registration form', 'industrial-training' ); ?>"><span aria-hidden="true">×</span></button>

			<div class="itp-form-view">
				<h2 class="itp-modal-title" id="itp-modal-title"><?php echo esc_html( $form['title'] ); ?></h2>
				<p class="itp-note"><?php echo esc_html( $form['note'] ); ?></p>

				<form class="itp-form" novalidate>
					<fieldset class="itp-fields">
						<?php
						$field( 'fullName', __( 'Full name', 'industrial-training' ), $input( 'fullName', 'text', [ 'maxlength' => 100, 'autocomplete' => 'name' ] ), true );
						$field( 'email', __( 'Email', 'industrial-training' ), $input( 'email', 'email', [ 'maxlength' => 254, 'autocomplete' => 'email', 'inputmode' => 'email', 'spellcheck' => 'false' ] ) );
						$field( 'phone', __( 'Phone', 'industrial-training' ), $input( 'phone', 'tel', [ 'maxlength' => 20, 'autocomplete' => 'tel', 'inputmode' => 'tel', 'placeholder' => '+91 98765 43210' ] ) );
						$field( 'college', __( 'College / Polytechnic', 'industrial-training' ), $input( 'college', 'text', [ 'maxlength' => 150, 'autocomplete' => 'organization' ] ), true );
						$field( 'branch', __( 'Branch', 'industrial-training' ), $select( 'branch', __( 'Select your branch', 'industrial-training' ), itp_branches() ) );
						$field( 'year', __( 'Year of study', 'industrial-training' ), $select( 'year', __( 'Select your year', 'industrial-training' ), itp_years() ) );
						$names = itp_track_names();
						$field( 'track', __( 'Training track', 'industrial-training' ), $select( 'track', __( 'Select a track', 'industrial-training' ), array_combine( $names, $names ) ), true );
						?>
						<div class="itp-hp" aria-hidden="true">
							<label for="itp-website"><?php esc_html_e( 'Leave this field empty', 'industrial-training' ); ?></label>
							<input type="text" id="itp-website" name="website" tabindex="-1" autocomplete="off">
						</div>
					</fieldset>

					<div class="itp-form-error" role="alert"></div>

					<div class="itp-form-actions">
						<button type="button" class="itp-btn itp-btn-quiet" data-itp-close><?php esc_html_e( 'Cancel', 'industrial-training' ); ?></button>
						<button type="submit" class="itp-btn itp-btn-primary itp-submit"><span class="itp-spinner" aria-hidden="true"></span><span class="itp-submit-label"><?php esc_html_e( 'Submit registration', 'industrial-training' ); ?></span></button>
					</div>
				</form>
			</div>

			<div class="itp-success" hidden>
				<div class="itp-check" aria-hidden="true">✓</div>
				<h2 class="itp-modal-title itp-success-title" tabindex="-1"></h2>
				<p class="itp-success-text"></p>
				<button type="button" class="itp-btn itp-btn-primary" data-itp-close><?php esc_html_e( 'Done', 'industrial-training' ); ?></button>
			</div>
		</div>
	</dialog>
</div>
