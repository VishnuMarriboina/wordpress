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
// wa.me needs the number as digits with country code; a bare 10-digit number is taken as Indian.
$wa_digits = preg_replace( '/\D/', '', $contact['whatsapp'] ?? '' );
$wa_digits = 10 === strlen( $wa_digits ) ? '91' . $wa_digits : $wa_digits;
$wa_url    = $wa_digits ? 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode( __( 'Hi, I would like to know more about the Industrial Training program.', 'industrial-training' ) ) : '';
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
$select = static function ( string $name, string $placeholder, array $options, bool $required = true ) {
	$html = sprintf( '<select id="itp-%1$s" name="%1$s"%3$s aria-describedby="itp-err-%1$s"><option value="">%2$s</option>', esc_attr( $name ), esc_html( $placeholder ), $required ? ' required' : '' );
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
				<?php $logo = itp_real_url( $s['brand_logo'] ) ?: itp_local_url( $s['brand_logo'] ); ?>
				<?php if ( $logo ) : ?>
					<img class="itp-brand-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $s['brand_name'] ); ?>" width="117" height="52" decoding="async">
				<?php else : ?>
					<span class="itp-mark" aria-hidden="true"><?php echo esc_html( $s['brand_mark'] ); ?></span>
					<span><?php echo esc_html( $s['brand_name'] ); ?></span>
				<?php endif; ?>
			</a>
			<nav class="itp-nav" aria-label="<?php esc_attr_e( 'Page sections', 'industrial-training' ); ?>">
				<ul role="list">
					<li><a href="#about"><?php echo esc_html( $s['nav']['about'] ); ?></a></li>
					<li><a href="#offers"><?php echo esc_html( $s['nav']['offers'] ); ?></a></li>
					<li><a href="#tracks"><?php echo esc_html( $s['nav']['tracks'] ); ?></a></li>
					<?php if ( ! empty( itp_content()['ourStudents'] ) ) : ?>
						<li><a href="#students"><?php esc_html_e( 'Students', 'industrial-training' ); ?></a></li>
					<?php elseif ( itp_items( 'studentsData' ) ) : ?>
						<li><a href="#reviews"><?php echo esc_html( $s['nav']['reviews'] ); ?></a></li>
					<?php endif; ?>
					<li><a href="#founder"><?php echo esc_html( $s['nav']['founder'] ); ?></a></li>
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
					<?php if ( ! empty( $hero['eligible_title'] ) ) : ?>
						<div class="itp-eligible itp-rise" style="--i:3">
							<span class="itp-eligible-icon" aria-hidden="true">🎉</span>
							<div>
								<p class="itp-eligible-title"><?php echo esc_html( $hero['eligible_title'] ); ?></p>
								<?php if ( ! empty( $hero['eligible_text'] ) ) : ?>
									<p class="itp-eligible-text"><?php echo esc_html( $hero['eligible_text'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $hero['eligible_note'] ) ) : ?>
									<p class="itp-eligible-note"><?php echo esc_html( $hero['eligible_note'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
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

		<?php $about = $s['about']; ?>
		<section class="itp-section itp-about-sec" id="about" aria-labelledby="itp-about-title" data-itp-spy>
			<div class="itp-container itp-about">
				<div class="itp-about-visual itp-reveal">
					<div class="itp-about-card">
						<?php $logo = itp_real_url( $s['brand_logo'] ) ?: itp_local_url( $s['brand_logo'] ); ?>
						<?php if ( $logo ) : ?>
							<img class="itp-about-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $s['brand_name'] ); ?>" width="234" height="104" loading="lazy" decoding="async">
						<?php endif; ?>
						<p class="itp-about-tag"><?php esc_html_e( 'Empowering your potential', 'industrial-training' ); ?></p>
						<dl class="itp-about-stats">
							<?php foreach ( $s['stats'] as $stat ) : ?>
								<div><dt><?php echo esc_html( $stat['label'] ); ?></dt><dd><?php echo itp_stat_value( (string) $stat['value'] ); // phpcs:ignore ?></dd></div>
							<?php endforeach; ?>
						</dl>
					</div>
					<span class="itp-about-ring" aria-hidden="true"></span>
				</div>
				<div class="itp-about-copy">
					<p class="itp-eyebrow itp-reveal"><?php echo esc_html( $about['eyebrow'] ); ?></p>
					<h2 class="itp-h2 itp-reveal" id="itp-about-title"><?php echo esc_html( $about['title'] ); ?></h2>
					<p class="itp-lead itp-reveal"><?php echo esc_html( $about['text'] ); ?></p>
					<?php if ( $about['text2'] ) : ?>
						<p class="itp-about-text itp-reveal"><?php echo esc_html( $about['text2'] ); ?></p>
					<?php endif; ?>
					<ul class="itp-about-points" role="list">
						<?php foreach ( $about['points'] as $i => $pt ) : ?>
							<li class="itp-reveal">
								<span class="itp-about-icon" aria-hidden="true"><?php echo esc_html( $pt['icon'] ); ?></span>
								<span><strong><?php echo esc_html( $pt['title'] ); ?></strong> <?php echo esc_html( $pt['text'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>

		<section class="itp-section itp-alt" id="highlights" aria-labelledby="itp-highlights-title" data-itp-spy>
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

		<?php $offers = $s['offers']; ?>
		<section class="itp-section" id="offers" aria-labelledby="itp-offers-title" data-itp-spy>
			<div class="itp-container">
				<div class="itp-head">
					<h2 class="itp-h2 itp-reveal" id="itp-offers-title"><?php echo esc_html( $offers['title'] ); ?></h2>
					<p class="itp-lead itp-reveal"><?php echo esc_html( $offers['lead'] ); ?></p>
				</div>
				<?php if ( ! empty( $offers['stipend'] ) || ! empty( $offers['age'] ) ) : ?>
					<div class="itp-pills itp-reveal">
						<?php if ( ! empty( $offers['stipend'] ) ) : ?>
							<p class="itp-stipend"><?php echo esc_html( $offers['stipend'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $offers['age'] ) ) : ?>
							<p class="itp-stipend itp-age"><?php echo esc_html( $offers['age'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php
				$naps = $offers['naps'] ?? [];
				if ( ! empty( $naps['title'] ) ) {
					// Shown as the banner below, so it isn't repeated as a card.
					$offers['items'] = array_values( array_filter( $offers['items'], static fn( $o ) => 'naps' !== ( $o['photo'] ?? '' ) ) );
				}
				?>
				<?php if ( ! empty( $naps['title'] ) ) : ?>
					<div class="itp-naps itp-reveal">
						<span class="itp-naps-icon" aria-hidden="true">👨‍🎓</span>
						<div class="itp-naps-body">
							<p class="itp-naps-kicker"><?php esc_html_e( 'Government apprenticeship scheme', 'industrial-training' ); ?></p>
							<h3><?php echo esc_html( $naps['title'] ); ?></h3>
							<p><?php echo esc_html( $naps['text'] ); ?></p>
							<?php if ( ! empty( $naps['eligible'] ) ) : ?>
								<p class="itp-naps-eligible"><?php echo esc_html( $naps['eligible'] ); ?></p>
							<?php endif; ?>
							<?php $ages = array_filter( (array) ( $naps['ages'] ?? [] ), static fn( $a ) => ! empty( $a['value'] ) ); ?>
							<?php if ( $ages ) : ?>
								<dl class="itp-naps-ages" aria-label="<?php esc_attr_e( 'Age requirements', 'industrial-training' ); ?>">
									<?php foreach ( $ages as $age ) : ?>
										<div><dt><?php echo esc_html( $age['label'] ); ?></dt><dd><?php echo esc_html( $age['value'] ); ?></dd></div>
									<?php endforeach; ?>
								</dl>
							<?php endif; ?>
							<?php if ( ! empty( $naps['note'] ) ) : ?>
								<p class="itp-naps-note"><?php echo esc_html( $naps['note'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php // At-a-glance chips: jump straight to the card. ?>
				<ul class="itp-offer-nav itp-reveal" role="list">
					<?php foreach ( $offers['items'] as $i => $item ) : ?>
						<li><a href="#itp-offer-<?php echo (int) $i; ?>"><span aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span> <?php echo esc_html( $item['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<ul class="itp-offers" role="list">
					<?php foreach ( $offers['items'] as $i => $item ) : ?>
						<li class="itp-card itp-offer itp-reveal" id="itp-offer-<?php echo (int) $i; ?>">
							<div class="itp-offer-media">
								<?php echo itp_image( (int) $item['image'], (string) $item['photo'], [ 16, 10 ], '(max-width: 560px) 92vw, (max-width: 1000px) 46vw, 280px' ); // phpcs:ignore ?>
							</div>
							<div class="itp-offer-body">
								<span class="itp-offer-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
								<h3><?php echo esc_html( $item['title'] ); ?></h3>
								<p><?php echo esc_html( $item['text'] ); ?></p>
								<?php if ( '' !== trim( $item['note'] ) ) : ?>
									<p class="itp-offer-note"><?php echo esc_html( $item['note'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="itp-offer-cta itp-reveal">
					<div>
						<h3><?php echo esc_html( $offers['cta_title'] ); ?></h3>
						<p><?php echo esc_html( $offers['cta_text'] ); ?></p>
					</div>
					<?php $opener( $offers['cta_button'], 'itp-btn-primary itp-shine', '', ' ' . $arrow ); ?>
				</div>
			</div>
		</section>

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
								<span class="itp-weeks"><?php echo esc_html( $s['batches']['duration'] ); ?></span>
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

		<section class="itp-section itp-steps-sec" aria-labelledby="itp-steps-title">
			<div class="itp-container">
				<div class="itp-head">
					<h2 class="itp-h2 itp-reveal" id="itp-steps-title"><?php echo esc_html( $s['steps']['title'] ); ?></h2>
					<?php if ( ! empty( $s['steps']['lead'] ) ) : ?>
						<p class="itp-lead itp-reveal"><?php echo esc_html( $s['steps']['lead'] ); ?></p>
					<?php endif; ?>
				</div>
				<ol class="itp-steps itp-reveal" role="list" style="--n:<?php echo count( $s['steps']['items'] ); ?>">
					<?php foreach ( $s['steps']['items'] as $i => $step ) : ?>
						<li class="itp-step" style="--i:<?php echo (int) $i; ?>">
							<div class="itp-step-media">
								<?php echo itp_image( (int) ( $step['image'] ?? 0 ), (string) ( $step['photo'] ?? '' ), [ 4, 3 ], '(max-width: 760px) 92vw, (max-width: 1000px) 46vw, 270px' ); // phpcs:ignore ?>
								<span class="itp-step-icon" aria-hidden="true"><?php echo esc_html( $step['icon'] ?? '' ); ?></span>
							</div>
							<span class="itp-step-num" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
							<h3><?php echo esc_html( $step['title'] ); ?></h3>
							<p><?php echo esc_html( $step['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
				<div class="itp-steps-cta itp-reveal"><?php $opener( $s['nav']['register'], 'itp-btn-primary itp-shine', '', ' ' . $arrow ); ?></div>
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
							<?php if ( $wa_url ) : ?>
							<li><span class="itp-ci-icon" aria-hidden="true">💬</span><span><span class="itp-ci-label"><?php esc_html_e( 'WhatsApp', 'industrial-training' ); ?></span><a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $contact['whatsapp'] ); ?></a></span></li>
							<?php endif; ?>
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
						$field( 'phone', __( 'Phone', 'industrial-training' ), $input( 'phone', 'tel', [ 'maxlength' => 16, 'autocomplete' => 'tel-national', 'inputmode' => 'numeric', 'placeholder' => __( '10-digit mobile number', 'industrial-training' ) ] ) );
						$field( 'college', __( 'College / Polytechnic', 'industrial-training' ), $input( 'college', 'text', [ 'maxlength' => 150, 'autocomplete' => 'organization' ] ), true );
						$field( 'branch', __( 'Branch', 'industrial-training' ), $select( 'branch', __( 'Select your branch', 'industrial-training' ), itp_branches() ), true );
						?>
						<?php // Not shown: records which track card's "Register for this track" button opened the form (empty otherwise). ?>
						<input type="hidden" id="itp-track" name="track" value="">
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

	<?php if ( $wa_url ) : ?>
	<a class="itp-wa-float" href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'industrial-training' ); ?>">
		<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.04 3C8.86 3 3.03 8.82 3.03 16c0 2.3.6 4.53 1.74 6.5L3 29l6.68-1.75A12.95 12.95 0 0 0 16.04 29C23.2 29 29 23.18 29 16S23.2 3 16.04 3Zm0 23.74c-2 0-3.95-.54-5.66-1.55l-.4-.24-3.97 1.04 1.06-3.86-.26-.4A10.7 10.7 0 0 1 5.3 16c0-5.92 4.82-10.74 10.75-10.74 5.92 0 10.73 4.82 10.73 10.74 0 5.93-4.81 10.74-10.73 10.74Zm5.89-8.04c-.32-.16-1.91-.94-2.2-1.05-.3-.11-.51-.16-.73.16-.21.32-.83 1.05-1.02 1.27-.19.21-.38.24-.7.08-.32-.16-1.36-.5-2.59-1.6-.96-.85-1.6-1.9-1.79-2.22-.19-.32-.02-.5.14-.66.15-.14.32-.38.48-.56.16-.19.21-.32.32-.54.1-.21.05-.4-.03-.56-.08-.16-.72-1.74-.99-2.38-.26-.62-.52-.54-.72-.55h-.62c-.21 0-.56.08-.85.4-.3.32-1.12 1.1-1.12 2.68s1.15 3.1 1.31 3.32c.16.21 2.26 3.45 5.47 4.84.77.33 1.36.53 1.83.68.77.24 1.47.21 2.02.13.62-.09 1.91-.78 2.18-1.53.27-.75.27-1.4.19-1.53-.08-.14-.3-.22-.62-.38Z"/></svg>
	</a>
	<?php endif; ?>
</div>
