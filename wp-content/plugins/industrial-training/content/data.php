<?php
/**
 * Founder, student, college, leadership and partner content.
 *
 * HOW TO EDIT
 * - Copy this file to wp-content/industrial-training/content.php and edit that copy.
 *   It is loaded instead of this one and survives plugin updates.
 * - Images ('photo' / 'logo'): a Media Library attachment ID (recommended, e.g. 123), a full image URL,
 *   or '' to show an initials avatar. Always fill the matching '...Alt' text.
 * - Social links: paste the full https:// URL. Leave '' (or '#') to hide that icon. Nothing is shown
 *   for an empty link.
 *
 * PLACEHOLDERS
 * - Entries with 'placeholder' => true are samples, not real people or organisations. Visitors never see
 *   them. Logged-in editors see them, labelled "Placeholder", so the layout can be previewed.
 * - Replace a sample with verified, consented information, then set 'placeholder' => false.
 * - A section appears to visitors only when it has at least one non-placeholder entry.
 *
 * VERIFICATION BADGES
 * - 'verified' => true shows "Verified Student" / "College Partner" / the partner's 'badge'.
 *   Only set it after you have confirmed the details (and have permission to publish them).
 */

defined( 'ABSPATH' ) || exit;

$no_social = [ 'linkedin' => '', 'instagram' => '', 'facebook' => '', 'twitter' => '', 'youtube' => '', 'github' => '', 'website' => '' ];

return [

	/* ---------------- Founder & CEO (company-provided information) ---------------- */
	'founderData'            => [
		'name'              => 'Ram',
		'designation'       => 'Founder & CEO',
		'experience'        => '10+ Years',
		'studentsTrained'   => '10,000+',
		'collegesConnected' => '200+',
		'photo'             => '', // Attachment ID or URL. '' shows an initials avatar.
		'photoAlt'          => 'Ram, Founder & CEO',
		'bio'               => 'Ram, Founder & CEO, brings 10+ years of experience in student training, technology education, and career development. Through his work, he has helped 10,000+ students and collaborated with 200+ colleges to provide practical training, technical guidance, and career-focused learning opportunities.',
		'tagline'           => 'Helping students learn, grow and prepare for industry careers.',
		'vision'            => 'To help every student gain practical, hands-on skills and the confidence to grow toward real industry opportunities.',
		'social'            => $no_social, // e.g. 'linkedin' => 'https://www.linkedin.com/in/…'
	],

	/* ---------------- Founder journey / impact (company-provided figures only) ---------------- */
	'impactData'             => [
		[ 'value' => '10+', 'label' => 'Years of Experience' ],
		[ 'value' => '10,000+', 'label' => 'Students Trained' ],
		[ 'value' => '200+', 'label' => 'Colleges Connected' ],
		[ 'value' => 'Multiple', 'label' => 'Industry & Academic Collaborations' ],
	],

	/* ---------------- Students (success stories + "What Our Students Say") ---------------- */
	'studentsData'           => [
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Student Name 1',
			'photo'          => '',
			'photoAlt'       => 'Photo of the student',
			'college'        => 'College Name',
			'course'         => 'Diploma / B.Tech – Branch',
			'trainingYear'   => 'Year',
			'program'        => 'Training Program',
			'skills'         => [ 'Skill 1', 'Skill 2', 'Skill 3' ],
			'opportunity'    => 'Placement / career opportunity',
			'currentRole'    => 'Current Role',
			'currentCompany' => 'Company Name',
			'location'       => 'City',
			'testimonial'    => 'Placeholder testimonial. Replace with the student’s own words, published with their permission.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Student Name 2',
			'photo'          => '',
			'photoAlt'       => 'Photo of the student',
			'college'        => 'College Name',
			'course'         => 'Diploma – Branch',
			'trainingYear'   => 'Year',
			'program'        => 'Training Program',
			'skills'         => [ 'Skill 1', 'Skill 2' ],
			'opportunity'    => 'Placement / career opportunity',
			'currentRole'    => 'Current Role',
			'currentCompany' => 'Company Name',
			'location'       => '',
			'testimonial'    => 'Placeholder testimonial. Replace with the student’s own words, published with their permission.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Student Name 3',
			'photo'          => '',
			'photoAlt'       => 'Photo of the student',
			'college'        => 'College Name',
			'course'         => 'Diploma – Branch',
			'trainingYear'   => 'Year',
			'program'        => 'Training Program',
			'skills'         => [ 'Skill 1', 'Skill 2', 'Skill 3' ],
			'opportunity'    => 'Placement / career opportunity',
			'currentRole'    => 'Current Role',
			'currentCompany' => 'Company Name',
			'location'       => 'City',
			'testimonial'    => 'Placeholder testimonial. Replace with the student’s own words, published with their permission.',
			'social'         => $no_social,
		],
	],

	/* ---------------- Colleges & academic institutions ---------------- */
	'collegeTestimonials'    => [
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'College Name A',
			'logo'              => '',
			'logoAlt'           => 'College logo',
			'location'          => 'City, State',
			'collaborationYear' => 'Year',
			'program'           => 'Training Program',
			'studentsTrained'   => '', // Only fill with a verified number.
			'testimonial'       => 'Placeholder testimonial. Replace with a statement approved by the college.',
			'testimonialBy'     => 'Name, Designation',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'College Name B',
			'logo'              => '',
			'logoAlt'           => 'College logo',
			'location'          => 'City, State',
			'collaborationYear' => 'Year',
			'program'           => 'Training Program',
			'studentsTrained'   => '',
			'testimonial'       => 'Placeholder testimonial. Replace with a statement approved by the college.',
			'testimonialBy'     => 'Name, Designation',
			'social'            => $no_social,
		],
	],

	/* ---------------- College leadership (Principal, Dean, HOD, TPO, Coordinator) ---------------- */
	'leadershipTestimonials' => [
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Person Name',
			'photo'             => '',
			'photoAlt'          => 'Photo of the faculty member',
			'designation'       => 'Principal',
			'college'           => 'College Name',
			'collaborationYear' => 'Year',
			'testimonial'       => 'Placeholder testimonial. Replace with the person’s approved statement.',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Person Name',
			'photo'             => '',
			'photoAlt'          => 'Photo of the faculty member',
			'designation'       => 'Head of Department – Branch',
			'college'           => 'College Name',
			'collaborationYear' => 'Year',
			'testimonial'       => 'Placeholder testimonial. Replace with the person’s approved statement.',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Person Name',
			'photo'             => '',
			'photoAlt'          => 'Photo of the faculty member',
			'designation'       => 'Training & Placement Officer',
			'college'           => 'College Name',
			'collaborationYear' => 'Year',
			'testimonial'       => 'Placeholder testimonial. Replace with the person’s approved statement.',
			'social'            => $no_social,
		],
	],

	/* ---------------- Connected organisations / partners ---------------- */
	// 'type' examples: College, University, Educational Institution, Training Organization,
	// Technology Organization, Industry Partner, Placement/Recruitment Partner, Community Organization.
	'partnerOrganizations'   => [
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'College Partner', 'name' => 'Organization Name', 'logo' => '', 'logoAlt' => 'Organization logo', 'type' => 'College', 'description' => 'Placeholder. Describe the actual collaboration.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Industry Partner', 'name' => 'Organization Name', 'logo' => '', 'logoAlt' => 'Organization logo', 'type' => 'Industry Partner', 'description' => 'Placeholder. Describe the actual collaboration.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Industry Partner', 'name' => 'Organization Name', 'logo' => '', 'logoAlt' => 'Organization logo', 'type' => 'Technology Organization', 'description' => 'Placeholder. Describe the actual collaboration.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Placement Partner', 'name' => 'Organization Name', 'logo' => '', 'logoAlt' => 'Organization logo', 'type' => 'Placement/Recruitment Partner', 'description' => 'Placeholder. Describe the actual collaboration.', 'social' => $no_social ],
	],

	/* ---------------- Partner-network figures (company-provided only) ---------------- */
	'orgImpactData'          => [
		[ 'value' => '200+', 'label' => 'Colleges Connected' ],
		[ 'value' => '10,000+', 'label' => 'Students Reached' ],
		[ 'value' => 'Multiple', 'label' => 'Academic Collaborations' ],
		[ 'value' => 'Industry', 'label' => 'Connections' ],
	],

	/* ---------------- Company social accounts ("Join our community"; hidden until a URL is added) ---------------- */
	'companySocial'          => $no_social,

	/* ---------------- Final call to action ---------------- */
	'ctaData'                => [
		'title'         => 'Want to Work With Us?',
		'lead'          => 'Whether you are a student, a college or an organization, we would like to hear from you.',
		'students'      => [ 'title' => 'For Students', 'text' => 'Explore training opportunities.', 'button' => 'Students – Get Started' ],
		'colleges'      => [ 'title' => 'For Colleges', 'text' => 'Partner with us to provide industry-focused training to students.', 'button' => 'Colleges – Partner With Us' ],
		'organizations' => [ 'title' => 'For Organizations', 'text' => 'Explore collaboration opportunities.', 'button' => 'Organizations – Collaborate' ],
	],
];
