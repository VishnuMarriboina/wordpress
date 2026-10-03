<?php
/**
 * Founder & CEO + journey/impact sections.
 *
 * @var array  $s          Settings.
 * @var string $founder_bg Background modifier class from the including template.
 */

defined( 'ABSPATH' ) || exit;

$c       = itp_content();
$founder = $c['founderData'] ?? [];
$impact  = $c['impactData'] ?? [];
if ( empty( $founder['name'] ) ) {
	return;
}
$f_social = itp_social_links( $founder['social'] ?? [], $founder['name'], 'itp-social-lg' );
?>
<?php if ( $impact ) : ?>
<section class="itp-band" id="impact" aria-labelledby="itp-impact-title">
	<div class="itp-hero-bg" aria-hidden="true"></div>
	<div class="itp-container">
		<div class="itp-head">
			<h2 class="itp-h2 itp-reveal" id="itp-impact-title"><?php esc_html_e( 'Our Journey & Impact', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php esc_html_e( 'Milestones shared by our team, built one batch of students at a time.', 'industrial-training' ); ?></p>
		</div>
		<ol class="itp-milestones itp-reveal" role="list">
			<?php foreach ( $impact as $i => $m ) : ?>
				<li style="--i:<?php echo (int) $i; ?>">
					<span class="itp-milestone-dot" aria-hidden="true"></span>
					<p class="itp-milestone-value"><?php echo itp_stat_value( (string) $m['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<p class="itp-milestone-label"><?php echo esc_html( $m['label'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<p class="itp-fineprint itp-reveal"><?php esc_html_e( 'Figures provided by the company.', 'industrial-training' ); ?></p>
	</div>
</section>
<?php endif; ?>

<section class="itp-section<?php echo esc_attr( $founder_bg ?? ' itp-alt' ); ?>" id="founder" aria-labelledby="itp-founder-title" data-itp-spy>
	<div class="itp-container itp-founder">
		<article class="itp-card itp-founder-card itp-reveal" aria-labelledby="itp-founder-name">
			<div class="itp-founder-photo">
				<?php echo itp_avatar( $founder['photo'] ?? '', (string) ( $founder['photoAlt'] ?? $founder['name'] ), $founder['name'], 160, 'itp-avatar itp-avatar-xl' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<header>
				<h3 class="itp-founder-name" id="itp-founder-name"><?php echo esc_html( $founder['name'] ); ?></h3>
				<p class="itp-founder-role"><?php echo esc_html( $founder['designation'] ?? '' ); ?></p>
				<?php if ( ! empty( $founder['experience'] ) ) : ?>
					<?php /* translators: %s: e.g. "6+ Years" */ ?>
					<p class="itp-pill"><?php echo esc_html( sprintf( __( '%s Experience', 'industrial-training' ), $founder['experience'] ) ); ?></p>
				<?php endif; ?>
			</header>
			<dl class="itp-founder-stats">
				<div><dt><?php esc_html_e( 'Students trained', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $founder['studentsTrained'] ?? '' ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Colleges connected', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $founder['collegesConnected'] ?? '' ); ?></dd></div>
			</dl>
			<?php if ( ! empty( $founder['tagline'] ) ) : ?>
				<p class="itp-founder-tagline">“<?php echo esc_html( $founder['tagline'] ); ?>”</p>
			<?php endif; ?>
			<footer>
				<?php echo $f_social; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in itp_social_links(). ?>
				<?php /* translators: %s: founder name */ ?>
				<a class="itp-btn itp-btn-primary itp-btn-block" href="#contact"><?php echo esc_html( sprintf( __( 'Connect with %s', 'industrial-training' ), $founder['name'] ) ); ?> <span class="itp-arrow" aria-hidden="true">→</span></a>
			</footer>
		</article>

		<div class="itp-founder-copy">
			<p class="itp-eyebrow itp-reveal"><?php esc_html_e( 'Leadership', 'industrial-training' ); ?></p>
			<h2 class="itp-h2 itp-reveal" id="itp-founder-title"><?php esc_html_e( 'Meet Our Founder & CEO', 'industrial-training' ); ?></h2>
			<p class="itp-lead itp-reveal"><?php echo esc_html( $founder['bio'] ?? '' ); ?></p>
			<?php if ( ! empty( $founder['vision'] ) ) : ?>
				<div class="itp-vision itp-reveal">
					<h3><span aria-hidden="true">🎯</span> <?php esc_html_e( 'Vision', 'industrial-training' ); ?></h3>
					<p><?php echo esc_html( $founder['vision'] ); ?></p>
				</div>
			<?php endif; ?>
			<div class="itp-connect itp-reveal">
				<?php /* translators: %s: founder name */ ?>
				<h3><?php echo esc_html( sprintf( __( 'Connect with %s', 'industrial-training' ), $founder['name'] ) ); ?></h3>
				<p>
					<?php
					echo esc_html( $f_social
						/* translators: %s: founder name */
						? sprintf( __( 'Follow %s on the profiles in his card, or reach our team through the contact details below.', 'industrial-training' ), $founder['name'] )
						: __( 'Reach our team through the contact details below and we’ll put you in touch.', 'industrial-training' ) );
					?>
				</p>
				<a class="itp-btn itp-btn-outline" href="#impact"><?php esc_html_e( 'Learn More About Our Journey', 'industrial-training' ); ?> <span class="itp-arrow" aria-hidden="true">→</span></a>
			</div>
		</div>
	</div>
</section>
