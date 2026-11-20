<?php
/**
 * /new — standalone document (no theme header/footer/styles). All content comes from the
 * Industrial Training plugin; the form posts to its existing REST endpoint.
 */

defined( 'ABSPATH' ) || exit;

require_once SRN_DIR . 'templates/icons.php';

$s        = itp_settings();
$c        = itp_content();
$hero     = $s['hero'];
$about    = $s['about'];
$contact  = $s['contact'];
$form     = $s['form'];
$offers   = $s['offers'];
$tracks   = array_values( (array) $s['tracks']['items'] );
$steps    = array_values( (array) $s['steps']['items'] );
$logo     = srn_logo_url();
$brand    = (string) $s['brand_name'];
$founder  = (array) ( $c['founderData'] ?? [] );
$impact   = (array) ( $c['impactData'] ?? [] );
$students = array_values( array_filter( (array) ( $c['ourStudents'] ?? [] ), static fn( $st ) => is_array( $st ) && ! empty( $st['name'] ) && ! empty( $st['comment'] ) ) );
$colleges = array_values( array_filter( (array) ( $c['ourColleges'] ?? [] ), static fn( $co ) => is_array( $co ) && ! empty( $co['name'] ) ) );
$social   = itp_social_links( $c['companySocial'] ?? [], $brand, 'srn-social' );
$wa_url   = srn_whatsapp_url();
$map_url  = itp_real_url( $contact['map_url'] ?? '' );
$map_q    = trim( (string) ( $contact['map_query'] ?? '' ) );
$faq      = srn_faq();
$groups   = srn_skill_groups();
$duration = (string) ( $s['batches']['duration'] ?? '' );
$tel      = preg_replace( '/[^0-9+]/', '', (string) $contact['phone'] );
$email    = antispambot( (string) $contact['email'] );

// NAPS is shown as its own banner (as on the classic site), so its card is left out of the grid.
$naps        = (array) ( $offers['naps'] ?? [] );
$offer_items = array_values( array_filter( (array) $offers['items'], static fn( $o ) => empty( $naps['title'] ) || 'naps' !== ( $o['photo'] ?? '' ) ) );

$nav = [
	'home'     => __( 'Home', 'skillrise-new' ),
	'about'    => __( 'About Us', 'skillrise-new' ),
	'programs' => __( 'Training Programs', 'skillrise-new' ),
	'why'      => __( 'Why Choose Us', 'skillrise-new' ),
	'benefits' => __( 'Benefits', 'skillrise-new' ),
	'faq'      => __( 'FAQ', 'skillrise-new' ),
	'contact'  => __( 'Contact', 'skillrise-new' ),
];
$arrow = srn_icon( 'arrow', 'srn-ico srn-ico-arrow' );

$logo_img = static function ( string $class, int $w, int $h, bool $lazy = true ) use ( $logo, $brand ) {
	if ( '' === $logo ) {
		return '<span class="' . esc_attr( $class ) . ' srn-logo-text">' . esc_html( $brand ) . '</span>';
	}
	return sprintf( '<img class="%1$s" src="%2$s" alt="%3$s" width="%4$d" height="%5$d" decoding="async"%6$s>', esc_attr( $class ), esc_url( $logo ), esc_attr( $brand ), $w, $h, $lazy ? ' loading="lazy"' : '' );
};
$stars = static function ( $rating ): string {
	$rating = max( 0, min( 5, round( (float) $rating * 2 ) / 2 ) );
	if ( ! $rating ) {
		return '';
	}
	$shown = rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' );
	/* translators: %s: rating out of 5 */
	return '<p class="srn-stars" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'industrial-training' ), $shown ) ) . '"><span style="--fill:' . esc_attr( (string) ( $rating / 5 * 100 ) ) . '%" aria-hidden="true"></span></p>';
};
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<style>html,body{margin:0;padding:0;background:#070b1a}</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'srn-canvas' ); ?>>
<?php wp_body_open(); ?>
<?php echo srn_icon_sprite(); // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
<div class="skillrise-new" data-srn>
	<a class="srn-skip" href="#srn-main"><?php esc_html_e( 'Skip to content', 'industrial-training' ); ?></a>

	<!-- 1. Navigation -->
	<header class="srn-header" data-srn-header>
		<div class="srn-wrap srn-header-in">
			<a class="srn-brand" href="#home" aria-label="<?php echo esc_attr( $brand . ' — ' . __( 'Home', 'skillrise-new' ) ); ?>">
				<?php echo $logo_img( 'srn-brand-logo', 117, 52, false ); // phpcs:ignore ?>
			</a>
			<nav class="srn-nav" id="srn-nav" aria-label="<?php esc_attr_e( 'Main', 'skillrise-new' ); ?>">
				<ul role="list">
					<?php foreach ( $nav as $id => $label ) : ?>
						<li><a href="#<?php echo esc_attr( $id ); ?>" data-srn-link><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<a class="srn-btn srn-btn-primary srn-nav-cta-mobile" href="#register"><?php esc_html_e( 'Register Now', 'skillrise-new' ); ?> <?php echo $arrow; // phpcs:ignore ?></a>
			</nav>
			<a class="srn-btn srn-btn-primary srn-btn-sm srn-nav-cta" href="#register"><?php esc_html_e( 'Register Now', 'skillrise-new' ); ?></a>
			<button class="srn-burger" type="button" aria-expanded="false" aria-controls="srn-nav" data-srn-burger>
				<span class="srn-sr" data-srn-burger-label><?php esc_html_e( 'Open menu', 'skillrise-new' ); ?></span>
				<span class="srn-burger-lines" aria-hidden="true"><span></span><span></span><span></span></span>
			</button>
		</div>
		<span class="srn-progress" aria-hidden="true"></span>
	</header>

	<main id="srn-main" tabindex="-1">

		<!-- 2. Hero -->
		<section class="srn-hero srn-dark" id="home" aria-labelledby="srn-hero-title" data-srn-spy>
			<div class="srn-hero-bg" aria-hidden="true">
				<span class="srn-grid"></span>
				<span class="srn-glow srn-glow-a"></span>
				<span class="srn-glow srn-glow-b"></span>
			</div>
			<div class="srn-hero-stage" aria-hidden="true" data-srn-stage>
				<img class="srn-hero-fallback" src="<?php echo esc_url( srn_asset_url( 'assets/images/hero-orbit.svg' ) ); ?>" alt="" width="640" height="640" decoding="async" fetchpriority="high">
			</div>
			<div class="srn-wrap srn-hero-in">
				<div class="srn-hero-copy">
					<?php if ( ! empty( $hero['badge'] ) ) : ?>
						<p class="srn-badge srn-intro" style="--i:0"><span class="srn-pulse" aria-hidden="true"></span><?php echo esc_html( $hero['badge'] ); ?></p>
					<?php endif; ?>
					<h1 class="srn-h1 srn-intro" id="srn-hero-title" style="--i:1">
						<?php esc_html_e( 'Build Your Future with', 'skillrise-new' ); ?>
						<span class="srn-gradient-text"><?php esc_html_e( 'Industry-Ready Skills', 'skillrise-new' ); ?></span>
					</h1>
					<p class="srn-hero-lead srn-intro" style="--i:2"><?php esc_html_e( 'Transform your technical knowledge into real-world experience through practical industrial training designed for the next generation of professionals.', 'skillrise-new' ); ?></p>
					<div class="srn-actions srn-intro" style="--i:3">
						<a class="srn-btn srn-btn-primary srn-btn-lg" href="#programs"><?php esc_html_e( 'Explore Training Programs', 'skillrise-new' ); ?> <?php echo $arrow; // phpcs:ignore ?></a>
						<a class="srn-btn srn-btn-glass srn-btn-lg" href="#register"><?php esc_html_e( 'Register Now', 'skillrise-new' ); ?></a>
					</div>
					<?php if ( ! empty( $hero['eligible_title'] ) ) : ?>
						<p class="srn-eligible srn-intro" style="--i:4"><?php echo srn_icon( 'cake' ); // phpcs:ignore ?> <?php echo esc_html( $hero['eligible_title'] ); ?></p>
					<?php endif; ?>
				</div>
				<dl class="srn-hero-stats srn-intro" style="--i:5">
					<?php foreach ( (array) $s['stats'] as $stat ) : ?>
						<div class="srn-glass">
							<dt><?php echo esc_html( $stat['label'] ); ?></dt>
							<dd><?php echo itp_stat_value( (string) $stat['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in itp_stat_value(). ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
			<ul class="srn-hero-chips" role="list" aria-hidden="true">
				<?php if ( $duration ) : ?>
					<li class="srn-chip-float srn-chip-a"><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><span><strong><?php echo esc_html( $duration ); ?></strong><?php esc_html_e( 'Industrial training', 'skillrise-new' ); ?></span></li>
				<?php endif; ?>
				<?php if ( ! empty( $hero['float_title'] ) ) : ?>
					<li class="srn-chip-float srn-chip-b"><?php echo srn_icon( 'tool' ); // phpcs:ignore ?><span><strong><?php echo esc_html( $hero['float_title'] ); ?></strong><?php echo esc_html( (string) $hero['float_text'] ); ?></span></li>
				<?php endif; ?>
			</ul>
			<a class="srn-scroll-cue" href="#about"><span class="srn-sr"><?php echo esc_html( $hero['scroll_label'] ?? __( 'Scroll down', 'skillrise-new' ) ); ?></span><span class="srn-mouse" aria-hidden="true"></span></a>
		</section>

		<!-- 3. About -->
		<section class="srn-section srn-light srn-about" id="about" aria-labelledby="srn-about-title" data-srn-spy>
			<div class="srn-wrap srn-split">
				<div class="srn-about-visual" data-srn-reveal>
					<div class="srn-about-card srn-tilt" data-srn-tilt>
						<span class="srn-card-shine" aria-hidden="true"></span>
						<div class="srn-about-logo"><?php echo $logo_img( '', 234, 104 ); // phpcs:ignore ?></div>
						<?php if ( ! empty( $founder['name'] ) ) : ?>
							<div class="srn-founder">
								<span class="srn-founder-ring"><span class="srn-founder-orbit" aria-hidden="true"><i></i><i></i></span><?php echo itp_avatar( $founder['photo'] ?? '', (string) ( $founder['photoAlt'] ?? $founder['name'] ), (string) $founder['name'], 72, 'srn-founder-photo' ); // phpcs:ignore ?></span>
								<div>
									<p class="srn-founder-name"><?php echo esc_html( $founder['name'] ); ?></p>
									<p class="srn-founder-role"><?php echo esc_html( (string) ( $founder['designation'] ?? '' ) ); ?></p>
								</div>
							</div>
							<?php if ( ! empty( $founder['tagline'] ) ) : ?>
								<blockquote class="srn-founder-quote">“<?php echo esc_html( $founder['tagline'] ); ?>”</blockquote>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( $impact ) : ?>
							<dl class="srn-impact">
								<?php foreach ( $impact as $m ) : ?>
									<div><dd><?php echo itp_stat_value( (string) $m['value'] ); // phpcs:ignore ?></dd><dt><?php echo esc_html( $m['label'] ); ?></dt></div>
								<?php endforeach; ?>
							</dl>
							<p class="srn-fine"><?php esc_html_e( 'Figures provided by the company.', 'industrial-training' ); ?></p>
						<?php endif; ?>
					</div>
					<span class="srn-ring srn-ring-a" aria-hidden="true"></span>
					<span class="srn-ring srn-ring-b" aria-hidden="true"></span>
				</div>
				<div class="srn-about-copy">
					<p class="srn-eyebrow" data-srn-reveal><?php echo esc_html( $about['eyebrow'] ); ?></p>
					<h2 class="srn-h2" id="srn-about-title" data-srn-reveal><?php echo esc_html( $about['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( $about['text'] ); ?></p>
					<?php if ( ! empty( $about['text2'] ) ) : ?>
						<p data-srn-reveal><?php echo esc_html( $about['text2'] ); ?></p>
					<?php endif; ?>
					<ul class="srn-points" role="list">
						<?php foreach ( (array) $about['points'] as $i => $pt ) : ?>
							<li data-srn-reveal>
								<span class="srn-icon-tile"><?php echo srn_icon( srn_about_icon( (int) $i ) ); // phpcs:ignore ?></span>
								<span><strong><?php echo esc_html( $pt['title'] ); ?></strong><?php echo esc_html( $pt['text'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>

		<!-- 4. Training programs -->
		<section class="srn-section srn-dark srn-programs" id="programs" aria-labelledby="srn-programs-title" data-srn-spy>
			<div class="srn-section-bg" aria-hidden="true"><span class="srn-grid"></span><span class="srn-glow srn-glow-c"></span></div>
			<div class="srn-wrap">
				<header class="srn-head">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Training Programs', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-programs-title" data-srn-reveal><?php echo esc_html( $s['tracks']['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( $s['tracks']['lead'] ); ?></p>
					<?php if ( ! empty( $offers['stipend'] ) || ! empty( $offers['age'] ) ) : ?>
						<ul class="srn-pills" role="list" data-srn-reveal>
							<?php if ( ! empty( $offers['stipend'] ) ) : ?>
								<li><?php echo srn_icon( 'rupee' ); // phpcs:ignore ?><?php echo esc_html( srn_strip_icon( (string) $offers['stipend'] ) ); ?></li>
							<?php endif; ?>
							<?php if ( $duration ) : ?>
								<li><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><?php /* translators: %s: e.g. 6 months */ echo esc_html( sprintf( __( 'Duration: %s', 'skillrise-new' ), $duration ) ); ?></li>
							<?php endif; ?>
							<?php if ( ! empty( $offers['age'] ) ) : ?>
								<li><?php echo srn_icon( 'cake' ); // phpcs:ignore ?><?php echo esc_html( srn_strip_icon( (string) $offers['age'] ) ); ?></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</header>
				<ul class="srn-tracks" role="list">
					<?php foreach ( $tracks as $i => $track ) : ?>
						<li class="srn-track" id="track-<?php echo (int) $i; ?>" data-srn-reveal>
							<article class="srn-track-card srn-tilt" data-srn-tilt aria-labelledby="srn-track-<?php echo (int) $i; ?>">
								<div class="srn-track-media">
									<?php
									// A real photo chosen in Settings (Media Library) wins; otherwise the animated scene; otherwise the default photo.
									$scene = (int) ( $track['image'] ?? 0 ) ? '' : srn_track_scene( (string) ( $track['photo'] ?? '' ) );
									echo $scene ?: itp_image( (int) ( $track['image'] ?? 0 ), (string) ( $track['photo'] ?? '' ), [ 16, 10 ], '(max-width: 640px) 92vw, (max-width: 1100px) 46vw, 380px' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG / escaped image.
									?>
									<?php if ( $duration ) : ?>
										<span class="srn-track-duration"><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><?php echo esc_html( $duration ); ?></span>
									<?php endif; ?>
								</div>
								<div class="srn-track-body">
									<span class="srn-track-icon"><?php echo $scene ? srn_icon( srn_icon_for( (string) ( $track['photo'] ?? '' ) ) ) : srn_track_anim( (string) ( $track['photo'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?></span>
									<h3 id="srn-track-<?php echo (int) $i; ?>"><?php echo esc_html( $track['name'] ); ?></h3>
									<p><?php echo esc_html( $track['summary'] ); ?></p>
									<dl class="srn-track-meta">
										<div><dt><?php esc_html_e( 'Eligible branches', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( $track['branch'] ); ?></dd></div>
										<div><dt><?php esc_html_e( 'Skills & technologies', 'skillrise-new' ); ?></dt>
											<dd><ul class="srn-tags" role="list">
												<?php foreach ( itp_skills( (string) $track['skills'] ) as $skill ) : ?>
													<li><?php echo esc_html( $skill ); ?></li>
												<?php endforeach; ?>
											</ul></dd>
										</div>
									</dl>
									<a class="srn-btn srn-btn-outline srn-btn-block" href="#register"><?php echo esc_html( $s['tracks']['button'] ); ?><span class="srn-sr">: <?php echo esc_html( $track['name'] ); ?></span> <?php echo $arrow; // phpcs:ignore ?></a>
								</div>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<!-- 5. Why choose Skillrise -->
		<section class="srn-section srn-light srn-why" id="why" aria-labelledby="srn-why-title" data-srn-spy>
			<div class="srn-wrap">
				<header class="srn-head">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Why Choose Skillrise?', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-why-title" data-srn-reveal><?php echo esc_html( $s['highlights']['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( $s['highlights']['lead'] ); ?></p>
				</header>
				<ul class="srn-why-grid" role="list">
					<?php foreach ( (array) $s['highlights']['items'] as $i => $item ) : ?>
						<li class="srn-why-card srn-tilt" data-srn-tilt data-srn-reveal>
							<span class="srn-why-num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="srn-icon-tile srn-icon-tile-lg"><?php echo srn_icon( srn_highlight_icon( (int) $i ) ); // phpcs:ignore ?></span>
							<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<p><?php echo esc_html( $item['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<!-- 6. Learning journey -->
		<section class="srn-section srn-dark srn-journey" id="journey" aria-labelledby="srn-journey-title">
			<div class="srn-section-bg" aria-hidden="true"><span class="srn-glow srn-glow-d"></span></div>
			<div class="srn-wrap">
				<header class="srn-head">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Learning Journey', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-journey-title" data-srn-reveal><?php echo esc_html( $s['steps']['title'] ); ?></h2>
					<?php if ( ! empty( $s['steps']['lead'] ) ) : ?>
						<p class="srn-lead" data-srn-reveal><?php echo esc_html( $s['steps']['lead'] ); ?></p>
					<?php endif; ?>
				</header>
				<ol class="srn-timeline" role="list" data-srn-timeline>
					<li class="srn-timeline-rail" aria-hidden="true"><span class="srn-timeline-fill" data-srn-fill></span></li>
					<?php foreach ( $steps as $i => $step ) : ?>
						<li class="srn-step" data-srn-step>
							<span class="srn-step-node" aria-hidden="true"><?php echo srn_icon( srn_icon_for( (string) ( $step['photo'] ?? '' ) ) ); // phpcs:ignore ?></span>
							<div class="srn-step-card srn-glass" data-srn-reveal>
								<div class="srn-step-media">
									<?php echo itp_image( (int) ( $step['image'] ?? 0 ), (string) ( $step['photo'] ?? '' ), [ 4, 3 ], '(max-width: 760px) 88vw, 300px' ); // phpcs:ignore ?>
								</div>
								<div class="srn-step-body">
									<?php /* translators: %d: step number */ ?>
									<p class="srn-step-num"><?php echo esc_html( sprintf( __( 'Step %d', 'skillrise-new' ), $i + 1 ) ); ?></p>
									<h3><?php echo esc_html( $step['title'] ); ?></h3>
									<p><?php echo esc_html( $step['text'] ); ?></p>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>

		<!-- 7. Technologies & skills -->
		<?php if ( $groups ) : ?>
		<section class="srn-section srn-dark srn-skills" id="skills" aria-labelledby="srn-skills-title">
			<div class="srn-wrap srn-split srn-split-rev">
				<div class="srn-orbit-wrap" aria-hidden="true" data-srn-reveal>
					<span class="srn-orbit-core"><?php echo srn_icon( 'spark' ); // phpcs:ignore ?></span>
					<div class="srn-orbit" data-srn-orbit>
						<?php
						$all = [];
						foreach ( $groups as $g ) {
							foreach ( $g['skills'] as $sk ) {
								$all[] = [ $sk, $g['icon'] ];
							}
						}
						$n = count( $all );
						foreach ( $all as $k => [ $sk, $ic ] ) :
							// Fibonacci sphere: even spread of chips over the sphere surface.
							$y   = 1 - ( 2 * ( $k + 0.5 ) / $n );
							$lat = rad2deg( asin( $y ) );
							$lon = fmod( $k * 137.508, 360 );
							?>
							<span class="srn-orbit-chip" style="--lat:<?php echo esc_attr( round( $lat, 2 ) ); ?>deg;--lon:<?php echo esc_attr( round( $lon, 2 ) ); ?>deg"><span><?php echo srn_icon( $ic ); // phpcs:ignore ?><?php echo esc_html( $sk ); ?></span></span>
						<?php endforeach; ?>
					</div>
				</div>
				<div>
					<header class="srn-head srn-head-left">
						<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Technologies & Skills', 'skillrise-new' ); ?></p>
						<h2 class="srn-h2" id="srn-skills-title" data-srn-reveal><?php esc_html_e( 'The tools you will actually use', 'skillrise-new' ); ?></h2>
						<p class="srn-lead" data-srn-reveal><?php esc_html_e( 'Every skill below comes straight from a training track, practised hands-on on real machines and projects.', 'skillrise-new' ); ?></p>
					</header>
					<ul class="srn-skill-groups" role="list">
						<?php foreach ( $groups as $g ) : ?>
							<li class="srn-skill-group srn-glass" data-srn-reveal>
								<h3><span class="srn-icon-tile"><?php echo srn_icon( $g['icon'] ); // phpcs:ignore ?></span><?php echo esc_html( $g['track'] ); ?></h3>
								<ul class="srn-tags" role="list">
									<?php foreach ( $g['skills'] as $sk ) : ?>
										<li><?php echo esc_html( $sk ); ?></li>
									<?php endforeach; ?>
								</ul>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- 8. Student benefits -->
		<section class="srn-section srn-light srn-benefits" id="benefits" aria-labelledby="srn-benefits-title" data-srn-spy>
			<div class="srn-wrap">
				<header class="srn-head">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Student Benefits', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-benefits-title" data-srn-reveal><?php echo esc_html( $offers['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( $offers['lead'] ); ?></p>
				</header>
				<ul class="srn-benefit-grid" role="list">
					<?php foreach ( $offer_items as $i => $item ) : ?>
						<li class="srn-benefit" data-srn-reveal>
							<div class="srn-benefit-media">
								<?php echo itp_image( (int) ( $item['image'] ?? 0 ), (string) ( $item['photo'] ?? '' ), [ 16, 10 ], '(max-width: 640px) 92vw, (max-width: 1100px) 46vw, 300px' ); // phpcs:ignore ?>
							</div>
							<div class="srn-benefit-body">
								<span class="srn-icon-tile"><?php echo srn_icon( srn_icon_for( (string) ( $item['photo'] ?? '' ) ) ); // phpcs:ignore ?></span>
								<h3><?php echo esc_html( $item['title'] ); ?></h3>
								<p><?php echo esc_html( $item['text'] ); ?></p>
								<?php if ( '' !== trim( (string) ( $item['note'] ?? '' ) ) ) : ?>
									<p class="srn-fine"><?php echo esc_html( $item['note'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( ! empty( $naps['title'] ) ) : ?>
					<div class="srn-naps" data-srn-reveal>
						<span class="srn-icon-tile srn-icon-tile-lg"><?php echo srn_icon( 'cap' ); // phpcs:ignore ?></span>
						<div>
							<p class="srn-eyebrow"><?php esc_html_e( 'Government apprenticeship scheme', 'industrial-training' ); ?></p>
							<h3><?php echo esc_html( $naps['title'] ); ?></h3>
							<p><?php echo esc_html( (string) ( $naps['text'] ?? '' ) ); ?></p>
							<?php if ( ! empty( $naps['eligible'] ) ) : ?>
								<p class="srn-naps-ok"><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( srn_strip_icon( (string) $naps['eligible'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $naps['note'] ) ) : ?>
								<p class="srn-fine"><?php echo esc_html( $naps['note'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $offers['cta_title'] ) ) : ?>
					<div class="srn-cta-band" data-srn-reveal>
						<div>
							<h3><?php echo esc_html( srn_strip_icon( (string) $offers['cta_title'] ) ); ?></h3>
							<p><?php echo esc_html( (string) $offers['cta_text'] ); ?></p>
						</div>
						<a class="srn-btn srn-btn-light srn-btn-lg" href="#register"><?php echo esc_html( (string) $offers['cta_button'] ); ?> <?php echo $arrow; // phpcs:ignore ?></a>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- Student voices (real, company-provided reviews from the content file) -->
		<?php if ( $students ) : ?>
		<section class="srn-section srn-light srn-alt srn-voices" id="voices" aria-labelledby="srn-voices-title">
			<div class="srn-wrap">
				<header class="srn-head srn-head-row">
					<div>
						<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Student Voices', 'skillrise-new' ); ?></p>
						<h2 class="srn-h2" id="srn-voices-title" data-srn-reveal><?php esc_html_e( 'What Our Students Say', 'industrial-training' ); ?></h2>
					</div>
					<div class="srn-car-nav" data-srn-reveal>
						<button type="button" class="srn-round" data-srn-prev aria-controls="srn-voices-track"><span class="srn-sr"><?php esc_html_e( 'Previous reviews', 'skillrise-new' ); ?></span><?php echo srn_icon( 'arrow', 'srn-ico srn-flip' ); // phpcs:ignore ?></button>
						<button type="button" class="srn-round" data-srn-next aria-controls="srn-voices-track"><span class="srn-sr"><?php esc_html_e( 'Next reviews', 'skillrise-new' ); ?></span><?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></button>
					</div>
				</header>
				<ul class="srn-voices-track" id="srn-voices-track" role="list" tabindex="0" aria-label="<?php esc_attr_e( 'Student reviews', 'skillrise-new' ); ?>" data-srn-carousel>
					<?php foreach ( $students as $st ) : ?>
						<li class="srn-voice">
							<figure>
								<?php echo srn_icon( 'quote', 'srn-ico srn-voice-q' ); // phpcs:ignore ?>
								<?php echo $stars( $st['rating'] ?? 0 ); // phpcs:ignore ?>
								<blockquote><p><?php echo esc_html( $st['comment'] ); ?></p></blockquote>
								<figcaption>
									<?php echo itp_avatar( '', '', (string) $st['name'], 44, 'srn-voice-avatar' ); // phpcs:ignore ?>
									<span><strong><?php echo esc_html( $st['name'] ); ?></strong><?php echo esc_html( (string) ( $st['college'] ?? '' ) ); ?>
									<?php if ( ! empty( $st['start'] ) ) : ?>
										<small><?php echo esc_html( $st['start'] . ( empty( $st['end'] ) ? '' : ' – ' . $st['end'] ) ); ?></small>
									<?php endif; ?></span>
								</figcaption>
							</figure>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $colleges ) : ?>
					<div class="srn-colleges" data-srn-reveal>
						<p><?php esc_html_e( 'Our students come from colleges including', 'skillrise-new' ); ?></p>
						<ul role="list">
							<?php foreach ( $colleges as $co ) : ?>
								<li title="<?php echo esc_attr( $co['name'] ); ?>">
									<?php $co_logo = itp_local_url( $co['logo'] ?? '' ) ?: itp_real_url( $co['logo'] ?? '' ); ?>
									<?php if ( $co_logo ) : ?>
										<img src="<?php echo esc_url( $co_logo ); ?>" alt="<?php echo esc_attr( $co['name'] ); ?>" width="64" height="64" loading="lazy" decoding="async">
									<?php endif; ?>
									<span><?php echo esc_html( $co['name'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- 9. Registration (existing API) -->
		<section class="srn-section srn-dark srn-register" id="register" aria-labelledby="srn-register-title">
			<div class="srn-section-bg" aria-hidden="true"><span class="srn-grid"></span><span class="srn-glow srn-glow-a"></span><span class="srn-glow srn-glow-b"></span></div>
			<div class="srn-wrap srn-split srn-register-in">
				<div class="srn-register-copy">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Register Now', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-register-title" data-srn-reveal><?php echo esc_html( $form['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( (string) $contact['cta_text'] ); ?></p>
					<ul class="srn-checks" role="list" data-srn-reveal>
						<?php if ( $duration ) : ?>
							<?php /* translators: %s: duration */ ?>
							<li><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( sprintf( __( '%s of hands-on industrial training', 'skillrise-new' ), $duration ) ); ?></li>
						<?php endif; ?>
						<?php if ( ! empty( $offers['stipend'] ) ) : ?>
							<li><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( srn_strip_icon( (string) $offers['stipend'] ) ); ?></li>
						<?php endif; ?>
						<?php if ( ! empty( $steps[1]['title'] ) ) : ?>
							<li><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( $steps[1]['text'] ); ?></li>
						<?php endif; ?>
					</ul>
					<p class="srn-register-help" data-srn-reveal>
						<?php esc_html_e( 'Prefer to talk first?', 'skillrise-new' ); ?>
						<a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $contact['phone'] ); ?></a>
					</p>
				</div>

				<div class="srn-form-card" data-srn-reveal
					data-success-title="<?php echo esc_attr( $form['success_title'] ); ?>"
					data-success-text="<?php echo esc_attr( $form['success_text'] ); ?>">
					<div class="srn-form-view">
						<p class="srn-form-note"><?php echo esc_html( $form['note'] ); ?></p>
						<form class="srn-form" novalidate data-srn-form>
							<fieldset class="srn-fields">
								<legend class="srn-sr"><?php echo esc_html( $form['title'] ); ?></legend>
								<div class="srn-field srn-field-full">
									<label for="srn-fullName"><?php esc_html_e( 'Full name', 'industrial-training' ); ?></label>
									<input type="text" id="srn-fullName" name="fullName" required maxlength="100" autocomplete="name" aria-describedby="srn-err-fullName">
									<p class="srn-error" id="srn-err-fullName"></p>
								</div>
								<div class="srn-field">
									<label for="srn-email"><?php esc_html_e( 'Email', 'industrial-training' ); ?></label>
									<input type="email" id="srn-email" name="email" required maxlength="254" autocomplete="email" inputmode="email" spellcheck="false" aria-describedby="srn-err-email">
									<p class="srn-error" id="srn-err-email"></p>
								</div>
								<div class="srn-field">
									<label for="srn-phone"><?php esc_html_e( 'Phone', 'industrial-training' ); ?></label>
									<input type="tel" id="srn-phone" name="phone" required maxlength="16" autocomplete="tel-national" inputmode="numeric" placeholder="<?php esc_attr_e( '10-digit mobile number', 'industrial-training' ); ?>" aria-describedby="srn-err-phone">
									<p class="srn-error" id="srn-err-phone"></p>
								</div>
								<div class="srn-field srn-field-full">
									<label for="srn-college"><?php esc_html_e( 'College / Polytechnic', 'industrial-training' ); ?></label>
									<input type="text" id="srn-college" name="college" required maxlength="150" autocomplete="organization" aria-describedby="srn-err-college">
									<p class="srn-error" id="srn-err-college"></p>
								</div>
								<div class="srn-field srn-field-full">
									<label for="srn-branch"><?php esc_html_e( 'Branch', 'industrial-training' ); ?></label>
									<div class="srn-select">
										<select id="srn-branch" name="branch" required aria-describedby="srn-err-branch">
											<option value=""><?php esc_html_e( 'Select your branch', 'industrial-training' ); ?></option>
											<?php foreach ( itp_branches() as $value => $label ) : ?>
												<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<p class="srn-error" id="srn-err-branch"></p>
								</div>
								<?php // Honeypot, same name as the classic form; the existing API ignores submissions that fill it. ?>
								<div class="srn-hp" aria-hidden="true">
									<label for="srn-website"><?php esc_html_e( 'Leave this field empty', 'industrial-training' ); ?></label>
									<input type="text" id="srn-website" name="website" tabindex="-1" autocomplete="off">
								</div>
							</fieldset>
							<div class="srn-form-error" role="alert"></div>
							<button type="submit" class="srn-btn srn-btn-primary srn-btn-lg srn-btn-block srn-submit"><span class="srn-spinner" aria-hidden="true"></span><span class="srn-submit-label"><?php esc_html_e( 'Submit registration', 'industrial-training' ); ?></span></button>
						</form>
					</div>
					<div class="srn-success" hidden>
						<span class="srn-success-check" aria-hidden="true"><?php echo srn_icon( 'check' ); // phpcs:ignore ?></span>
						<h3 class="srn-success-title" tabindex="-1"></h3>
						<p class="srn-success-text"></p>
						<button type="button" class="srn-btn srn-btn-glass" data-srn-again><?php esc_html_e( 'Register another student', 'skillrise-new' ); ?></button>
					</div>
				</div>
			</div>
		</section>

		<!-- 10. FAQ -->
		<?php if ( $faq ) : ?>
		<section class="srn-section srn-light srn-faq" id="faq" aria-labelledby="srn-faq-title" data-srn-spy>
			<div class="srn-wrap srn-faq-in">
				<header class="srn-head srn-head-left">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'FAQ', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-faq-title" data-srn-reveal><?php esc_html_e( 'Frequently asked questions', 'skillrise-new' ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( (string) $contact['lead'] ); ?></p>
					<a class="srn-btn srn-btn-outline-dark" href="#contact" data-srn-reveal><?php esc_html_e( 'Ask a question', 'skillrise-new' ); ?> <?php echo $arrow; // phpcs:ignore ?></a>
				</header>
				<div class="srn-accordion" data-srn-accordion>
					<?php foreach ( $faq as $i => $item ) : ?>
						<div class="srn-acc-item" data-srn-reveal>
							<h3>
								<button type="button" class="srn-acc-btn" id="srn-faq-q<?php echo (int) $i; ?>" aria-expanded="false" aria-controls="srn-faq-a<?php echo (int) $i; ?>">
									<span><?php echo esc_html( $item['q'] ); ?></span>
									<span class="srn-acc-icon" aria-hidden="true"><?php echo srn_icon( 'plus' ); // phpcs:ignore ?></span>
								</button>
							</h3>
							<div class="srn-acc-panel" id="srn-faq-a<?php echo (int) $i; ?>" role="region" aria-labelledby="srn-faq-q<?php echo (int) $i; ?>" hidden>
								<div><p><?php echo esc_html( $item['a'] ); ?></p></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- 11. Contact -->
		<section class="srn-section srn-light srn-alt srn-contact" id="contact" aria-labelledby="srn-contact-title" data-srn-spy>
			<div class="srn-wrap">
				<header class="srn-head">
					<p class="srn-eyebrow" data-srn-reveal><?php esc_html_e( 'Contact', 'skillrise-new' ); ?></p>
					<h2 class="srn-h2" id="srn-contact-title" data-srn-reveal><?php echo esc_html( $contact['title'] ); ?></h2>
					<p class="srn-lead" data-srn-reveal><?php echo esc_html( $contact['lead'] ); ?></p>
				</header>
				<div class="srn-contact-grid">
					<address class="srn-contact-cards">
						<a class="srn-contact-card" href="tel:<?php echo esc_attr( $tel ); ?>" data-srn-reveal>
							<span class="srn-icon-tile"><?php echo srn_icon( 'phone' ); // phpcs:ignore ?></span>
							<span><small><?php esc_html_e( 'Phone', 'industrial-training' ); ?></small><strong><?php echo esc_html( $contact['phone'] ); ?></strong></span>
						</a>
						<?php if ( $wa_url ) : ?>
							<a class="srn-contact-card" href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener noreferrer" data-srn-reveal>
								<span class="srn-icon-tile"><?php echo srn_icon( 'chat' ); // phpcs:ignore ?></span>
								<span><small><?php esc_html_e( 'WhatsApp', 'industrial-training' ); ?></small><strong><?php echo esc_html( $contact['whatsapp'] ); ?></strong></span>
								<span class="srn-sr"><?php esc_html_e( '(opens in a new tab)', 'skillrise-new' ); ?></span>
							</a>
						<?php endif; ?>
						<a class="srn-contact-card" href="mailto:<?php echo esc_attr( $email ); ?>" data-srn-reveal>
							<span class="srn-icon-tile"><?php echo srn_icon( 'mail' ); // phpcs:ignore ?></span>
							<span><small><?php esc_html_e( 'Email', 'industrial-training' ); ?></small><strong><?php echo esc_html( $email ); ?></strong></span>
						</a>
						<div class="srn-contact-card" data-srn-reveal>
							<span class="srn-icon-tile"><?php echo srn_icon( 'clock' ); // phpcs:ignore ?></span>
							<span><small><?php esc_html_e( 'Hours', 'industrial-training' ); ?></small><strong><?php echo esc_html( $contact['hours'] ); ?></strong></span>
						</div>
						<div class="srn-contact-card srn-contact-wide" data-srn-reveal>
							<span class="srn-icon-tile"><?php echo srn_icon( 'pin' ); // phpcs:ignore ?></span>
							<span><small><?php esc_html_e( 'Address', 'industrial-training' ); ?></small><strong><?php echo esc_html( $contact['address'] ); ?></strong>
							<?php if ( $map_url ) : ?>
								<a class="srn-link" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get directions', 'industrial-training' ); ?> <?php echo srn_icon( 'external' ); // phpcs:ignore ?><span class="srn-sr"> <?php esc_html_e( '(opens in a new tab)', 'skillrise-new' ); ?></span></a>
							<?php endif; ?>
							</span>
						</div>
					</address>
					<div class="srn-map" data-srn-reveal>
						<?php if ( $map_q ) : ?>
							<?php /* translators: %s: brand name */ ?>
							<div class="srn-map-facade" data-srn-map="<?php echo esc_url( 'https://www.google.com/maps?output=embed&q=' . rawurlencode( $map_q ) ); ?>" data-srn-map-title="<?php echo esc_attr( sprintf( __( 'Map showing the location of %s', 'industrial-training' ), $brand ) ); ?>">
								<span class="srn-map-pin" aria-hidden="true"><?php echo srn_icon( 'pin' ); // phpcs:ignore ?></span>
								<p><strong><?php echo esc_html( $brand ); ?></strong><?php echo esc_html( $contact['address'] ); ?></p>
								<button type="button" class="srn-btn srn-btn-primary" data-srn-map-load><?php echo srn_icon( 'map' ); // phpcs:ignore ?> <?php esc_html_e( 'Show interactive map', 'skillrise-new' ); ?></button>
							</div>
						<?php endif; ?>
						<?php if ( $social ) : ?>
							<div class="srn-contact-social">
								<p><?php esc_html_e( 'Follow us for batch announcements, student projects and training updates.', 'industrial-training' ); ?></p>
								<?php echo $social; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in itp_social_links(). ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
	</main>

	<!-- 12. Footer -->
	<footer class="srn-footer srn-dark">
		<div class="srn-wrap srn-footer-grid">
			<div class="srn-footer-brand">
				<a class="srn-brand" href="#home"><?php echo $logo_img( 'srn-brand-logo', 117, 52 ); // phpcs:ignore ?></a>
				<p><?php echo esc_html( $about['text'] ); ?></p>
				<?php echo $social; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<nav aria-label="<?php esc_attr_e( 'Footer', 'skillrise-new' ); ?>">
				<h2><?php esc_html_e( 'Explore', 'skillrise-new' ); ?></h2>
				<ul role="list">
					<?php foreach ( $nav as $id => $label ) : ?>
						<li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="#register"><?php esc_html_e( 'Register Now', 'skillrise-new' ); ?></a></li>
				</ul>
			</nav>
			<nav aria-label="<?php esc_attr_e( 'Training programs', 'skillrise-new' ); ?>">
				<h2><?php esc_html_e( 'Programs', 'skillrise-new' ); ?></h2>
				<ul role="list">
					<?php foreach ( $tracks as $i => $track ) : ?>
						<li><a href="#track-<?php echo (int) $i; ?>"><?php echo esc_html( $track['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div>
				<h2><?php esc_html_e( 'Contact', 'skillrise-new' ); ?></h2>
				<ul class="srn-footer-contact" role="list">
					<li><?php echo srn_icon( 'phone' ); // phpcs:ignore ?><a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $contact['phone'] ); ?></a></li>
					<li><?php echo srn_icon( 'mail' ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<li><?php echo srn_icon( 'pin' ); // phpcs:ignore ?><span><?php echo esc_html( $contact['address'] ); ?></span></li>
				</ul>
			</div>
		</div>
		<div class="srn-wrap srn-footer-bottom">
			<p><?php echo esc_html( itp_fill( (string) $s['footer']['copyright'], [ 'year' => wp_date( 'Y' ) ] ) ); ?></p>
			<ul role="list">
				<?php foreach ( srn_legal_links() as [ $label, $url ] ) : ?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Classic website', 'skillrise-new' ); ?></a></li>
			</ul>
		</div>
		<?php if ( ! empty( $s['footer']['credits'] ) ) : ?>
			<p class="srn-wrap srn-credits"><?php echo wp_kses( $s['footer']['credits'], [ 'a' => [ 'href' => true, 'rel' => true, 'target' => true ] ] ); ?></p>
		<?php endif; ?>
	</footer>

	<a class="srn-to-top" href="#home" data-srn-top><span class="srn-sr"><?php esc_html_e( 'Back to top', 'industrial-training' ); ?></span><?php echo srn_icon( 'arrow-up' ); // phpcs:ignore ?></a>
</div>
<?php wp_footer(); ?>
</body>
</html>
