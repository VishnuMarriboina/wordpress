<?php
/**
 * Student stories, testimonials, colleges, leadership, partners, community and final CTA.
 * Sections without any entry visible to the current viewer are skipped.
 *
 * @var array    $s      Settings.
 * @var callable $opener Renders a registration-modal button.
 */

defined( 'ABSPATH' ) || exit;

$c        = itp_content();
$students = itp_items( 'studentsData' );
$colleges = itp_items( 'collegeTestimonials' );
$leaders  = itp_items( 'leadershipTestimonials' );
$partners = itp_items( 'partnerOrganizations' );
$community = itp_social_links( $c['companySocial'] ?? [], $s['brand_name'], 'itp-social-lg' );
$cta      = $c['ctaData'] ?? [];

// Alternate backgrounds across whichever sections render (the section before is "How it works", plain).
$alt = true;
$bg  = static function () use ( &$alt ) {
	$class = $alt ? ' itp-alt' : '';
	$alt   = ! $alt;
	return $class;
};
// Dashed outline marks samples for editors only; visitors see the normal card with its "Sample" label.
$sample = static fn( array $item ) => itp_is_placeholder( $item ) && itp_is_editor() ? ' itp-is-sample' : '';
$quote  = static function ( string $text ) {
	echo '<blockquote class="itp-quote"><p>' . esc_html( $text ) . '</p></blockquote>';
};
$slide_label = static fn( int $i, int $n ) => sprintf( /* translators: 1: slide number, 2: total */ __( '%1$d of %2$d', 'industrial-training' ), $i + 1, $n );
$our_students = array_values( array_filter( (array) ( $c['ourStudents'] ?? [] ), static fn( $x ) => ! empty( $x['name'] ) ) );
$our_colleges = array_values( array_filter( (array) ( $c['ourColleges'] ?? [] ), static fn( $x ) => ! empty( $x['name'] ) ) );
?>

<?php if ( $our_students || $our_colleges ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" id="students" aria-labelledby="itp-students-title" data-itp-spy>
	<div class="itp-container">
		<div class="itp-head">
			<p class="itp-eyebrow itp-reveal"><?php esc_html_e( 'Our students', 'industrial-training' ); ?></p>
			<h2 class="itp-h2 itp-reveal" id="itp-students-title"><?php esc_html_e( 'Students Who Trained With Us', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php esc_html_e( 'Students from different colleges who gained hands-on industry experience with Skillrise Technologies.', 'industrial-training' ); ?></p>
			<?php if ( array_filter( $our_students, 'itp_is_placeholder' ) ) : ?>
				<p class="itp-preview-note itp-preview-public" role="note"><?php esc_html_e( 'Sample content shown for demonstration.', 'industrial-training' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		// Students with a comment get a wide testimonial card first; the rest follow as compact cards.
		usort( $our_students, static fn( $a, $b ) => (int) ( '' === trim( (string) ( $a['comment'] ?? '' ) ) ) <=> (int) ( '' === trim( (string) ( $b['comment'] ?? '' ) ) ) );
		$groups = [ 'itp-voices' => [], 'itp-people' => [] ];
		foreach ( $our_students as $st ) {
			$groups[ '' !== trim( (string) ( $st['comment'] ?? '' ) ) ? 'itp-voices' : 'itp-people' ][] = $st;
		}
		?>
		<?php foreach ( array_filter( $groups ) as $list_class => $list ) : ?>
			<ul class="<?php echo esc_attr( $list_class ); ?>" role="list">
				<?php foreach ( $list as $i => $st ) : ?>
					<?php $has_comment = 'itp-voices' === $list_class; ?>
					<li class="itp-person<?php echo $has_comment ? ' has-comment' : ''; ?> itp-reveal" style="--i:<?php echo (int) $i; ?>">
						<?php echo itp_avatar( $st['photo'] ?? '', (string) $st['name'], (string) $st['name'], 80, 'itp-avatar itp-person-avatar' ); // phpcs:ignore ?>
						<p class="itp-person-name"><?php echo esc_html( $st['name'] ); ?> <?php echo itp_badge( $st, '' ); // phpcs:ignore ?></p>
						<p class="itp-person-meta"><?php echo esc_html( implode( ' · ', array_filter( [ $st['track'] ?? '', $st['year'] ?? '' ] ) ) ?: __( 'Industrial Training', 'industrial-training' ) ); ?></p>
						<?php echo itp_internship_period( $st ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( ! empty( $st['college'] ) ) : ?>
							<p class="itp-person-college"><span aria-hidden="true">🏫</span> <?php echo esc_html( $st['college'] ); ?></p>
						<?php endif; ?>
						<?php echo itp_stars( $st['rating'] ?? 0 ); // phpcs:ignore ?>
						<?php if ( $has_comment ) : ?>
							<blockquote class="itp-person-quote"><p><?php echo esc_html( $st['comment'] ); ?></p></blockquote>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
		<?php if ( $our_colleges ) : ?>
			<h3 class="itp-subhead itp-reveal"><?php esc_html_e( 'Colleges our students come from', 'industrial-training' ); ?></h3>
			<ul class="itp-campus-grid" role="list">
				<?php foreach ( $our_colleges as $i => $col ) : ?>
					<?php
					$img  = itp_real_url( $col['image'] ?? '' ) ?: itp_local_url( $col['image'] ?? '' );
					$logo = $img ? '' : ( itp_real_url( $col['logo'] ?? '' ) ?: itp_local_url( $col['logo'] ?? '' ) );
					$site = itp_real_url( $col['website'] ?? '' );
					$poly = false !== stripos( (string) ( $col['type'] ?? '' ), 'polytechnic' );
					?>
					<li class="itp-card itp-campus itp-reveal" style="--i:<?php echo (int) $i; ?>">
						<div class="itp-campus-media<?php echo $logo ? ' is-logo' : ( $poly ? ' is-poly' : '' ); ?>">
							<?php if ( $logo ) : ?>
								<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: college */ __( '%s logo', 'industrial-training' ), $col['name'] ) ); ?>" width="96" height="96" loading="lazy" decoding="async">
							<?php elseif ( $img ) : ?>
								<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: college */ __( '%s campus', 'industrial-training' ), $col['name'] ) ); ?>" width="640" height="360" loading="lazy" decoding="async">
							<?php else : ?>
								<span class="itp-campus-mono" aria-hidden="true"><?php echo esc_html( itp_initials( (string) $col['name'] ) ); ?></span>
								<span class="itp-campus-icon" aria-hidden="true"><?php echo $poly ? '🛠️' : '🎓'; ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $col['type'] ) ) : ?>
								<span class="itp-campus-type"><?php echo esc_html( $col['type'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="itp-campus-body">
							<h4><?php echo esc_html( $col['name'] ); ?></h4>
							<p class="itp-campus-place"><span aria-hidden="true">📍</span> <?php echo esc_html( implode( ', ', array_filter( [ $col['location'] ?? '', $col['district'] ?? '' ] ) ) ); ?></p>
							<dl class="itp-campus-facts">
								<?php if ( ! empty( $col['established'] ) ) : ?>
									<div><dt><?php esc_html_e( 'Established', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $col['established'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $col['approvals'] ) ) : ?>
									<div><dt><?php esc_html_e( 'Recognition', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $col['approvals'] ); ?></dd></div>
								<?php endif; ?>
							</dl>
							<?php if ( ! empty( $col['courses'] ) ) : ?>
								<ul class="itp-chips" role="list" aria-label="<?php esc_attr_e( 'Courses', 'industrial-training' ); ?>">
									<?php foreach ( (array) $col['courses'] as $course ) : ?><li><?php echo esc_html( $course ); ?></li><?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( $site ) : ?>
								<a class="itp-btn itp-btn-outline itp-btn-sm" href="<?php echo esc_url( $site ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'College website', 'industrial-training' ); ?><span class="itp-sr"> <?php esc_html_e( '(opens in a new tab)', 'industrial-training' ); ?></span> <span aria-hidden="true">↗</span></a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $students ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" id="stories" aria-labelledby="itp-stories-title" data-itp-spy>
	<div class="itp-container">
		<div class="itp-head">
			<h2 class="itp-h2 itp-reveal" id="itp-stories-title"><?php esc_html_e( 'Student Success Stories', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php esc_html_e( 'Where our students went after training — from college, through hands-on work, to their current roles.', 'industrial-training' ); ?></p>
			<?php echo itp_preview_note( $students ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="itp-car itp-reveal" data-itp-carousel role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'industrial-training' ); ?>" aria-label="<?php esc_attr_e( 'Student success stories', 'industrial-training' ); ?>">
			<ul class="itp-car-track" role="list">
				<?php foreach ( $students as $i => $st ) : ?>
					<li class="itp-car-slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'industrial-training' ); ?>" aria-label="<?php echo esc_attr( $slide_label( $i, count( $students ) ) ); ?>">
						<article class="itp-card itp-person<?php echo esc_attr( $sample( $st ) ); ?>">
							<header class="itp-person-head">
								<?php /* translators: %s: student name */ echo itp_avatar( $st['photo'] ?? '', (string) ( $st['photoAlt'] ?? '' ) ?: sprintf( __( 'Photo of %s', 'industrial-training' ), $st['name'] ), $st['name'], 56 ); // phpcs:ignore ?>
								<div>
									<h3><?php echo esc_html( $st['name'] ); ?></h3>
									<p><?php echo esc_html( implode( ' · ', array_filter( [ $st['course'] ?? '', $st['college'] ?? '' ] ) ) ); ?></p>
								</div>
								<?php echo itp_badge( $st, __( 'Verified Student', 'industrial-training' ) ); // phpcs:ignore ?>
							</header>
							<ol class="itp-journey" role="list" aria-label="<?php esc_attr_e( 'Career journey', 'industrial-training' ); ?>">
								<li><span class="itp-journey-k"><?php esc_html_e( 'College', 'industrial-training' ); ?></span><?php echo esc_html( $st['college'] ?? '' ); ?></li>
								<li><span class="itp-journey-k"><?php esc_html_e( 'Training', 'industrial-training' ); ?></span><?php echo esc_html( implode( ' – ', array_filter( [ $st['program'] ?? '', $st['trainingYear'] ?? '' ] ) ) ); ?></li>
								<?php if ( ! empty( $st['skills'] ) ) : ?>
									<li><span class="itp-journey-k"><?php esc_html_e( 'Skills developed', 'industrial-training' ); ?></span>
										<ul class="itp-chips" role="list"><?php foreach ( (array) $st['skills'] as $skill ) : ?><li><?php echo esc_html( $skill ); ?></li><?php endforeach; ?></ul>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $st['opportunity'] ) ) : ?>
									<li><span class="itp-journey-k"><?php esc_html_e( 'Opportunity', 'industrial-training' ); ?></span><?php echo esc_html( $st['opportunity'] ); ?></li>
								<?php endif; ?>
								<li class="itp-journey-now"><span class="itp-journey-k"><?php esc_html_e( 'Current role', 'industrial-training' ); ?></span>
									<?php /* translators: 1: role, 2: company */ echo esc_html( sprintf( __( '%1$s at %2$s', 'industrial-training' ), $st['currentRole'] ?? '', $st['currentCompany'] ?? '' ) ); ?>
									<?php if ( ! empty( $st['location'] ) ) : ?><span class="itp-muted"> · <?php echo esc_html( $st['location'] ); ?></span><?php endif; ?>
								</li>
							</ol>
							<footer><?php echo itp_social_links( $st['social'] ?? [], $st['name'] ); // phpcs:ignore ?></footer>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php echo itp_carousel_controls( __( 'student success stories', 'industrial-training' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php $with_quotes = array_filter( $students, static fn( $st ) => ! empty( $st['testimonial'] ) ); ?>
	<?php if ( $with_quotes ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" id="reviews" aria-labelledby="itp-says-title" data-itp-spy>
	<div class="itp-container">
		<div class="itp-head">
			<p class="itp-eyebrow itp-reveal"><?php esc_html_e( 'Student reviews & feedback', 'industrial-training' ); ?></p>
			<h2 class="itp-h2 itp-reveal" id="itp-says-title"><?php esc_html_e( 'What Our Students Say', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php esc_html_e( 'Past students describe their training experience in their own words.', 'industrial-training' ); ?></p>
			<?php echo itp_preview_note( $with_quotes ); // phpcs:ignore ?>
		</div>
		<?php $with_quotes = array_values( $with_quotes ); ?>
		<div class="itp-car itp-reveal" data-itp-carousel role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'industrial-training' ); ?>" aria-labelledby="itp-says-title">
		<ul class="itp-car-track" role="list">
			<?php foreach ( $with_quotes as $i => $st ) : ?>
				<li class="itp-car-slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'industrial-training' ); ?>" aria-label="<?php echo esc_attr( $slide_label( $i, count( $with_quotes ) ) ); ?>">
					<article class="itp-card itp-testimonial<?php echo esc_attr( $sample( $st ) ); ?>">
						<?php echo itp_stars( $st['rating'] ?? 0 ); // phpcs:ignore ?>
						<?php $quote( $st['testimonial'] ); ?>
						<footer class="itp-person-head">
							<?php echo itp_avatar( $st['photo'] ?? '', (string) ( $st['photoAlt'] ?? '' ), $st['name'], 48 ); // phpcs:ignore ?>
							<div>
								<p class="itp-person-name"><?php echo esc_html( $st['name'] ); ?> <?php echo itp_badge( $st, __( 'Verified Student', 'industrial-training' ) ); // phpcs:ignore ?></p>
								<p><?php echo esc_html( implode( ', ', array_filter( [ $st['course'] ?? '', $st['college'] ?? '' ] ) ) ); ?></p>
								<?php /* translators: 1: year, 2: program */ ?>
								<p><?php echo esc_html( sprintf( __( 'Trained in %1$s · %2$s', 'industrial-training' ), $st['trainingYear'] ?? '', $st['program'] ?? '' ) ); ?></p>
								<?php /* translators: 1: role, 2: company */ ?>
								<p><?php echo esc_html( sprintf( __( 'Currently: %1$s at %2$s', 'industrial-training' ), $st['currentRole'] ?? '', $st['currentCompany'] ?? '' ) ); ?></p>
								<?php echo itp_social_links( $st['social'] ?? [], $st['name'] ); // phpcs:ignore ?>
							</div>
						</footer>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php echo itp_carousel_controls( __( 'student reviews', 'industrial-training' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>
	<?php endif; ?>
<?php endif; ?>

<?php if ( $colleges || $leaders ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" id="colleges" aria-labelledby="itp-colleges-title" data-itp-spy>
	<div class="itp-container">
		<div class="itp-head">
			<h2 class="itp-h2 itp-reveal" id="itp-colleges-title"><?php esc_html_e( 'Trusted by Colleges & Academic Institutions', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php esc_html_e( 'Institutions that have run training programs with us, in their own words.', 'industrial-training' ); ?></p>
			<?php echo itp_preview_note( array_merge( $colleges, $leaders ) ); // phpcs:ignore ?>
		</div>

		<?php if ( $colleges ) : ?>
			<ul class="itp-grid itp-grid-2" role="list">
				<?php foreach ( $colleges as $col ) : ?>
					<?php
					$site   = itp_real_url( $col['social']['website'] ?? '' );
					$others = array_diff_key( (array) ( $col['social'] ?? [] ), [ 'website' => 1 ] );
					?>
					<li class="itp-reveal">
						<article class="itp-card itp-college<?php echo esc_attr( $sample( $col ) ); ?>">
							<header class="itp-person-head">
								<?php echo itp_avatar( $col['logo'] ?? '', (string) ( $col['logoAlt'] ?? '' ) ?: sprintf( /* translators: %s: college */ __( '%s logo', 'industrial-training' ), $col['name'] ), $col['name'], 56, 'itp-avatar itp-logo' ); // phpcs:ignore ?>
								<div>
									<h3><?php echo esc_html( $col['name'] ); ?></h3>
									<p><?php echo esc_html( $col['location'] ?? '' ); ?></p>
								</div>
								<?php echo itp_badge( $col, __( 'College Partner', 'industrial-training' ) ); // phpcs:ignore ?>
							</header>
							<dl class="itp-facts">
								<div><dt><?php esc_html_e( 'Collaboration', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $col['collaborationYear'] ?? '' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Program', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $col['program'] ?? '' ); ?></dd></div>
								<?php if ( ! empty( $col['studentsTrained'] ) ) : ?>
									<div><dt><?php esc_html_e( 'Students trained', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $col['studentsTrained'] ); ?></dd></div>
								<?php endif; ?>
							</dl>
							<?php if ( ! empty( $col['testimonial'] ) ) : ?>
								<?php $quote( $col['testimonial'] ); ?>
								<?php if ( ! empty( $col['testimonialBy'] ) ) : ?><p class="itp-cite">— <?php echo esc_html( $col['testimonialBy'] ); ?></p><?php endif; ?>
							<?php endif; ?>
							<footer class="itp-card-foot">
								<?php if ( $site ) : ?>
									<a class="itp-btn itp-btn-outline itp-btn-sm" href="<?php echo esc_url( $site ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit College Website', 'industrial-training' ); ?><span class="itp-sr"> <?php esc_html_e( '(opens in a new tab)', 'industrial-training' ); ?></span> <span aria-hidden="true">↗</span></a>
								<?php endif; ?>
								<?php echo itp_social_links( $others, $col['name'] ); // phpcs:ignore ?>
							</footer>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $leaders ) : ?>
			<h3 class="itp-subhead itp-reveal" id="itp-leaders-title"><?php esc_html_e( 'From College Leadership', 'industrial-training' ); ?></h3>
			<div class="itp-car itp-reveal" data-itp-carousel role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'industrial-training' ); ?>" aria-labelledby="itp-leaders-title">
				<ul class="itp-car-track" role="list">
					<?php foreach ( $leaders as $i => $ld ) : ?>
						<li class="itp-car-slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'industrial-training' ); ?>" aria-label="<?php echo esc_attr( $slide_label( $i, count( $leaders ) ) ); ?>">
							<article class="itp-card itp-testimonial itp-leader<?php echo esc_attr( $sample( $ld ) ); ?>">
								<p class="itp-kind"><?php esc_html_e( 'College leadership', 'industrial-training' ); ?></p>
								<?php $quote( $ld['testimonial'] ?? '' ); ?>
								<footer class="itp-person-head">
									<?php echo itp_avatar( $ld['photo'] ?? '', (string) ( $ld['photoAlt'] ?? '' ), $ld['name'], 48 ); // phpcs:ignore ?>
									<div>
										<p class="itp-person-name"><?php echo esc_html( $ld['name'] ); ?> <?php echo itp_badge( $ld, __( 'College Partner', 'industrial-training' ) ); // phpcs:ignore ?></p>
										<p><?php echo esc_html( $ld['designation'] ?? '' ); ?></p>
										<p><?php echo esc_html( implode( ' · ', array_filter( [ $ld['college'] ?? '', $ld['collaborationYear'] ?? '' ] ) ) ); ?></p>
										<?php echo itp_social_links( $ld['social'] ?? [], $ld['name'] ); // phpcs:ignore ?>
									</div>
								</footer>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php echo itp_carousel_controls( __( 'college leadership testimonials', 'industrial-training' ) ); // phpcs:ignore ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $partners ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" id="partners" aria-labelledby="itp-partners-title">
	<div class="itp-container">
		<div class="itp-head">
			<h2 class="itp-h2 itp-reveal" id="itp-partners-title"><?php esc_html_e( 'Organizations & Institutions We Work With', 'industrial-training' ); ?></h2>
			<?php echo itp_preview_note( $partners ); // phpcs:ignore ?>
		</div>
		<?php if ( ! empty( $c['orgImpactData'] ) ) : ?>
			<dl class="itp-strip itp-reveal">
				<?php foreach ( $c['orgImpactData'] as $m ) : ?>
					<div><dt><?php echo esc_html( $m['label'] ); ?></dt><dd><?php echo itp_stat_value( (string) $m['value'] ); // phpcs:ignore ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
		<ul class="itp-grid itp-grid-4" role="list">
			<?php foreach ( $partners as $org ) : ?>
				<li class="itp-reveal">
					<article class="itp-card itp-org<?php echo esc_attr( $sample( $org ) ); ?>">
						<?php echo itp_avatar( $org['logo'] ?? '', (string) ( $org['logoAlt'] ?? '' ) ?: sprintf( /* translators: %s: organisation */ __( '%s logo', 'industrial-training' ), $org['name'] ), $org['name'], 64, 'itp-avatar itp-logo itp-logo-lg' ); // phpcs:ignore ?>
						<h3><?php echo esc_html( $org['name'] ); ?></h3>
						<p class="itp-branch"><?php echo esc_html( $org['type'] ?? '' ); ?></p>
						<?php echo itp_badge( $org, (string) ( $org['badge'] ?? '' ) ); // phpcs:ignore ?>
						<p><?php echo esc_html( $org['description'] ?? '' ); ?></p>
						<footer><?php echo itp_social_links( $org['social'] ?? [], $org['name'] ); // phpcs:ignore ?></footer>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php if ( $community ) : ?>
<section class="itp-section<?php echo esc_attr( $bg() ); ?>" aria-labelledby="itp-community-title">
	<div class="itp-container itp-head itp-community">
		<h2 class="itp-h2 itp-reveal" id="itp-community-title"><?php esc_html_e( 'Join Our Community', 'industrial-training' ); ?></h2>
		<p class="itp-lead itp-reveal"><?php esc_html_e( 'Follow us for batch announcements, student projects and training updates.', 'industrial-training' ); ?></p>
		<div class="itp-reveal"><?php echo $community; // phpcs:ignore ?></div>
	</div>
</section>
<?php endif; ?>

<?php
$bg(); // Impact band is dark; keep the alternation in step.
$founder_bg = $bg();
include ITP_DIR . 'templates/founder.php';
?>

<?php if ( $cta ) : ?>
<section class="itp-band" id="work-with-us" aria-labelledby="itp-cta-title">
	<div class="itp-hero-bg" aria-hidden="true"></div>
	<div class="itp-container">
		<div class="itp-head">
			<h2 class="itp-h2 itp-reveal" id="itp-cta-title"><?php echo esc_html( $cta['title'] ?? '' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php echo esc_html( $cta['lead'] ?? '' ); ?></p>
		</div>
		<ul class="itp-grid itp-grid-3" role="list">
			<?php foreach ( [ 'students' => '🎓', 'colleges' => '🏫', 'organizations' => '🤝' ] as $key => $icon ) : ?>
				<?php if ( empty( $cta[ $key ] ) ) { continue; } ?>
				<li class="itp-glass itp-reveal">
					<span class="itp-hl-icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
					<h3><?php echo esc_html( $cta[ $key ]['title'] ); ?></h3>
					<p><?php echo esc_html( $cta[ $key ]['text'] ); ?></p>
					<?php if ( 'students' === $key ) : ?>
						<?php $opener( $cta[ $key ]['button'], 'itp-btn-primary itp-btn-block itp-shine' ); ?>
					<?php else : ?>
						<a class="itp-btn itp-btn-ghost itp-btn-block" href="#contact"><?php echo esc_html( $cta[ $key ]['button'] ); ?> <span class="itp-arrow" aria-hidden="true">→</span></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>
