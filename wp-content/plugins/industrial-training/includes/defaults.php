<?php
/**
 * Default content. Everything here is editable under Settings → Industrial Training.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Placeholder photos (free Unsplash License), used until a Media Library image is chosen.
 * key => [ Unsplash photo id (source, for reference), alt text ].
 * The files are bundled in assets/images/photos/ as <key>-480/800/1200.webp; nothing loads from Unsplash.
 */
function itp_default_photos(): array {
	return [
		'hero'    => [ '1581092334651-ddf26d9a09d0', __( 'Student checking a tablet beside a machine on a workshop line', 'industrial-training' ) ],
		'inset'   => [ '1504328345606-18bbc8c9d7d1', __( 'Welder in a protective mask working with bright sparks', 'industrial-training' ) ],
		'cnc'     => [ '1558618666-fcd25c85cd64', __( 'Machinist turning a part on a lathe', 'industrial-training' ) ],
		'plc'     => [ '1621905251189-08b45d6a269e', __( 'Electrician in a hard hat testing an electrical panel', 'industrial-training' ) ],
		'iot'     => [ '1555664424-778a1e5e1b48', __( 'Arduino kit with sensors, display, servo motors and breadboards', 'industrial-training' ) ],
		'civil'   => [ '1541888946425-d81bb19240f5', __( 'Site engineers in hard hats overlooking a construction site', 'industrial-training' ) ],
		'web'     => [ '1522071820081-009f0129c71c', __( 'Developers coding together on laptops around a table', 'industrial-training' ) ],
		'contact' => [ '1581091226825-a6a2a5aee158', __( 'Engineer working on a laptop inside a factory lab', 'industrial-training' ) ],
		// "What we offer students" cards.
		'safety'      => [ '1622612023350-b15f063eabe6', __( 'Worker wearing a white safety helmet and high-visibility vest', 'industrial-training' ) ],
		'certificate' => [ '1589330694653-ded6df03f754', __( 'Signed certificate with a red seal', 'industrial-training' ) ],
		'placement'   => [ '1758518730384-be3d205838e8', __( 'Candidate shaking hands with an interviewer after a job interview', 'industrial-training' ) ],
		'stay'        => [ '1781415980730-bfcf192e38bc', __( 'Clean, well-lit shared room with neatly made beds', 'industrial-training' ) ],
		'everyone'    => [ '1558023608-bbcc13ffdc24', __( 'Group of smiling young men and women students', 'industrial-training' ) ],
		'criteria'    => [ '1758270705290-62b6294dd044', __( 'Students gathered around a laptop in a classroom', 'industrial-training' ) ],
		'colleges'    => [ '1635246550194-11af93a2763f', __( 'Students sitting together on a college campus', 'industrial-training' ) ],
		// "How it works" steps (4:3).
		'step-register' => [ '1660982741734-5a7d2730ff28', __( 'Student checking details on his phone in a classroom', 'industrial-training' ) ],
		'step-call'     => [ '1626863905121-3b0c0ed7b94c', __( 'Counsellors wearing headsets on a call', 'industrial-training' ) ],
		'step-train'    => [ '1690356107685-3725367f6f3f', __( 'Trainees in blue overalls working at workbenches in a workshop', 'industrial-training' ) ],
		'step-career'   => [ '1776248783518-400b6d0da64c', __( 'Young professionals in formal wear celebrating together', 'industrial-training' ) ],
		'naps'        => [ '1581092160607-ee22621dd758', __( 'Trainees in safety glasses learning on machines in a workshop', 'industrial-training' ) ],
	];
}

function itp_defaults(): array {
	$track = static function ( $name, $branch, $weeks, $summary, $skills, $photo ) {
		return compact( 'name', 'branch', 'weeks', 'summary', 'skills', 'photo' ) + [ 'image' => 0 ];
	};
	// 'note' is the fine print shown under the card text (conditions, disclaimers).
	$offer = static function ( $icon, $title, $text, $note, $photo ) {
		return compact( 'icon', 'title', 'text', 'note', 'photo' ) + [ 'image' => 0 ];
	};

	return [
		'brand_logo' => 'assets/images/skillrise-logo.png', // File in the plugin or a URL; '' shows the mark + name instead.
		'brand_mark' => 'SR',
		'brand_name' => __( 'Skillrise Technologies', 'industrial-training' ),
		'nav'        => [
			'highlights' => __( 'Why us', 'industrial-training' ),
			'about'      => __( 'About', 'industrial-training' ),
			'offers'     => __( 'What you get', 'industrial-training' ),
			'reviews'    => __( 'Reviews', 'industrial-training' ),
			'founder'    => __( 'Founder', 'industrial-training' ),
			'tracks'     => __( 'Tracks', 'industrial-training' ),
			'contact'    => __( 'Contact', 'industrial-training' ),
			'register'   => __( 'Register', 'industrial-training' ),
		],
		'hero'       => [
			'badge'         => __( 'Admissions open · Batches start in May & November', 'industrial-training' ),
			'title'         => __( 'Industrial Training Program', 'industrial-training' ),
			'title_accent'  => __( 'for Diploma Students', 'industrial-training' ),
			'lead'          => __( 'A six-month internship (compulsory) with hands-on work in real plants, labs and project teams. Two batches a year: May to November and November to May. Learn the tools industry uses, build a portfolio, and leave with a certificate and placement support.', 'industrial-training' ),
			// Main message: under-18s can join too (from age 14 under NAPS). Leave 'eligible_title' empty to hide.
			'eligible_title' => __( 'Below 18? You can join too!', 'industrial-training' ),
			'eligible_text'  => '', // Optional line under the title; empty = hidden.
			'eligible_note'  => '', // Optional condition; empty = hidden.
			'cta_primary'   => __( 'Register now', 'industrial-training' ),
			'cta_secondary' => __( 'Explore tracks', 'industrial-training' ),
			'image'         => 0,
			'inset_image'   => 0,
			'float_icon'    => '🛠️',
			'float_title'   => __( 'Hands-on, every day', 'industrial-training' ),
			'float_text'    => __( 'Real machines, real projects', 'industrial-training' ),
			'scroll_label'  => __( 'Scroll to highlights', 'industrial-training' ),
		],
		'stats'      => [
			// The first stat gets a full-width tile; the rest sit side by side below it.
			[ 'value' => '₹17,000 – ₹21,500', 'label' => __( 'Monthly stipend during the internship', 'industrial-training' ) ],
			[ 'value' => '10000+', 'label' => __( 'Students trained so far', 'industrial-training' ) ],
			[ 'value' => '50+', 'label' => __( 'Partner colleges', 'industrial-training' ) ],
		],
		
		'about'      => [
			'eyebrow' => __( 'About us', 'industrial-training' ),
			'title'   => __( 'About Skillrise Technologies', 'industrial-training' ),
			'text'    => __( 'Skillrise Technologies helps diploma and engineering students turn classroom knowledge into real industry skills. Through internships, industrial training and apprenticeships, students work on real machines and projects, learn from experienced mentors and earn a monthly stipend while they train.', 'industrial-training' ),
			'text2'   => __( 'Founded by Y Arun Kumar Reddy, we bring 6+ years of experience, have trained 10000+ students and work with 50+ colleges. Our goal is simple: practical experience, professional skills and the confidence to take the next step in your career.', 'industrial-training' ),
			'points'  => [
				[ 'icon' => '🎯', 'title' => __( 'Our mission', 'industrial-training' ), 'text' => __( 'Make every student industry-ready with hands-on training.', 'industrial-training' ) ],
				[ 'icon' => '🤝', 'title' => __( 'Who we serve', 'industrial-training' ), 'text' => __( 'Diploma and engineering students from any college.', 'industrial-training' ) ],
				[ 'icon' => '📈', 'title' => __( 'What you gain', 'industrial-training' ), 'text' => __( 'Skills, a stipend, a certificate and career guidance.', 'industrial-training' ) ],
			],
		],
		'highlights' => [
			'title' => __( 'Why train with us', 'industrial-training' ),
			'lead'  => __( 'Built for diploma students who want industry experience before their first job.', 'industrial-training' ),
			'items' => [
				[ 'icon' => '🏭', 'title' => __( 'Real shop-floor work', 'industrial-training' ), 'text' => __( 'Train inside partner plants and labs, not just a classroom.', 'industrial-training' ) ],
				[ 'icon' => '🧑', 'title' => __( 'Industry mentors', 'industrial-training' ), 'text' => __( 'Every batch is guided by experienced engineers from industry.', 'industrial-training' ) ],
				[ 'icon' => '📜', 'title' => __( 'Recognised certificate', 'industrial-training' ), 'text' => __( 'Get a certificate and project report your college can count as credit.', 'industrial-training' ) ],
				[ 'icon' => '💼', 'title' => __( 'Placement support', 'industrial-training' ), 'text' => __( 'Resume reviews, mock interviews and referrals to hiring partners.', 'industrial-training' ) ],
			],
		],
		'offers'     => [
			'title'       => __( 'What We Offer Students', 'industrial-training' ),
			'lead'        => __( 'A safe, supportive and career-focused internship and training experience: practical exposure, professional skills and preparation for your future career.', 'industrial-training' ),
			'items'       => [
				$offer( '🛡️', __( 'Student safety comes first', 'industrial-training' ), __( 'The safety and well-being of our students is one of our priorities. We maintain a professional, respectful and supportive environment throughout the internship and training period.', 'industrial-training' ), '', 'safety' ),
				$offer( '🎓', __( 'Certification', 'industrial-training' ), __( 'Students who successfully complete the internship or training program receive a certificate recognising their participation and practical learning experience.', 'industrial-training' ), '', 'certificate' ),
				$offer( '💼', __( 'Placement guidance & opportunities', 'industrial-training' ), __( 'Career guidance and placement support to understand career paths, prepare for opportunities and connect with relevant organisations where opportunities are available.', 'industrial-training' ), __( 'Placement assistance and guarantee employment.', 'industrial-training' ), 'placement' ),
				$offer( '🏠', __( 'Accommodation guidance', 'industrial-training' ), __( 'Need a place to stay during your internship or training? We guide you and refer you to suitable accommodation options.', 'industrial-training' ), __( 'Subject to availability and applicable terms.', 'industrial-training' ), 'stay' ),
				$offer( '👩‍💻', __( 'Opportunities for all students', 'industrial-training' ), __( 'Our programs are open to eligible students regardless of gender, with equal learning and professional-development opportunities for male and female students.', 'industrial-training' ), '', 'everyone' ),
				$offer( '📋', __( 'No minimum pass-percentage', 'industrial-training' ), __( 'Explore our programs without a restrictive academic pass-percentage requirement.', 'industrial-training' ), __( 'Subject to the eligibility requirements of the particular program.', 'industrial-training' ), 'criteria' ),
				$offer( '🏫', __( 'Students from any college', 'industrial-training' ), __( 'Apply regardless of your college or institution. We do not restrict opportunities to students from specific colleges.', 'industrial-training' ), __( 'Subject to the requirements of the particular program.', 'industrial-training' ), 'colleges' ),
				$offer( '👨‍🎓', __( 'Apprenticeships through NAPS', 'industrial-training' ), __( 'Where applicable, we support apprenticeship opportunities through the National Apprenticeship Promotion Scheme (NAPS).', 'industrial-training' ), __( 'Subject to NAPS rules, age requirements, documentation and other government requirements. Additional requirements and safeguards may apply for students below 18.', 'industrial-training' ), 'naps' ),
			],
			'stipend'     => __( '💰 Stipend: ₹17,000 – ₹21,500 per month', 'industrial-training' ),
			// Highlighted banner under the stipend. While its title is set, the NAPS card is left out of the grid.
			// Pill shown next to the stipend; many students don't know the apprenticeship age rules.
			'age'         => __( '🎂 Below 18? You can join too', 'industrial-training' ),
			'naps'        => [
				// Age tiles (value + label) shown in the NAPS banner; empty = hidden.
				// e.g. [ 'value' => '14+ years', 'label' => 'Minimum age to join' ], [ '18+ years', 'For hazardous trades' ], [ 'Up to 35', 'At registration, for govt. stipend support' ].
				'ages'     => [],
				'eligible' => __( '✅ Students below 18 are eligible', 'industrial-training' ),
				'title' => __( 'Apprenticeships through NAPS', 'industrial-training' ),
				'text'  => __( 'Where applicable, we support apprenticeship opportunities through the National Apprenticeship Promotion Scheme (NAPS).', 'industrial-training' ),
				'note'  => __( 'Subject to NAPS rules, age requirements, documentation and other government requirements. Additional requirements and safeguards may apply for students below 18.', 'industrial-training' ),
			],
			'cta_title'   => __( '🚀 Learn. Experience. Grow.', 'industrial-training' ),
			'cta_text'    => __( 'Gain practical experience, build industry-relevant skills and take your next career step with greater confidence.', 'industrial-training' ),
			'cta_button'  => __( 'Apply for an internship', 'industrial-training' ),
		],
		'tracks'     => [
			'title'  => __( 'Training tracks', 'industrial-training' ),
			'lead'   => __( 'Pick the track that matches your branch. Each one ends with a graded project.', 'industrial-training' ),
			'button' => __( 'Register for this track', 'industrial-training' ),
			'items'  => [
				$track( __( 'Manufacturing & CNC', 'industrial-training' ), __( 'Mechanical · Automobile', 'industrial-training' ), 8, __( 'Run CNC lathes and milling machines, read drawings and inspect parts on a working shop floor.', 'industrial-training' ), __( 'CNC programming, GD&T basics, Quality inspection', 'industrial-training' ), 'cnc' ),
				$track( __( 'Electrical Systems & PLC', 'industrial-training' ), __( 'Electrical · Electronics', 'industrial-training' ), 8, __( 'Wire control panels, program PLCs and troubleshoot motors and drives with plant engineers.', 'industrial-training' ), __( 'PLC ladder logic, Panel wiring, Industrial safety', 'industrial-training' ), 'plc' ),
				$track( __( 'PCB and electronic Components', 'industrial-training' ), __( 'ELECTRONICS AND COMMUNICATION', 'industrial-training' ), 6, __( 'Build sensor boards, flash microcontrollers and send live machine data to a cloud dashboard.', 'industrial-training' ), __( 'Arduino & ESP32, Sensor interfacing, MQTT', 'industrial-training' ), 'iot' ),
				$track( __( 'Civil & AutoCAD', 'industrial-training' ), __( 'Civil', 'industrial-training' ), 6, __( 'Draft plans in AutoCAD, estimate quantities and visit active construction sites each week.', 'industrial-training' ), __( 'AutoCAD 2D, Quantity estimation, Site supervision', 'industrial-training' ), 'civil' ),
				$track( __( 'Software & Web', 'industrial-training' ), __( 'Computer Science · Any branch', 'industrial-training' ), 6, __( 'Ship a real web app in a small team: HTML, CSS, JavaScript, Git and a deployed backend.', 'industrial-training' ), __( 'JavaScript & React, Git workflow, REST APIs', 'industrial-training' ), 'web' ),
			],
		],
		// Internship length (fixed six-month batches, joining in May or November); shown on every track card.
		'batches'    => [
			'duration' => __( '6 months', 'industrial-training' ),
		],
		'steps'      => [
			'title' => __( 'How it works', 'industrial-training' ),
			'lead'  => __( 'Four simple steps from registration to your first industry role.', 'industrial-training' ),
			'items' => [
				[ 'icon' => '📝', 'title' => __( 'Register', 'industrial-training' ), 'text' => __( 'Fill the short form. It takes under two minutes.', 'industrial-training' ), 'photo' => 'step-register', 'image' => 0 ],
				[ 'icon' => '📞', 'title' => __( 'Counselling call', 'industrial-training' ), 'text' => __( 'Our team calls within 2 working days to confirm your track and your batch: May or November.', 'industrial-training' ), 'photo' => 'step-call', 'image' => 0 ],
				[ 'icon' => '🛠️', 'title' => __( 'Hands-on training with stipend', 'industrial-training' ), 'text' => __( 'Six months of hands-on work on real machines and projects with industry mentors, earning a stipend of ₹17,000 – ₹21,500 per month.', 'industrial-training' ), 'photo' => 'step-train', 'image' => 0 ],
				[ 'icon' => '🎓', 'title' => __( 'Certificate & career', 'industrial-training' ), 'text' => __( 'Complete the program, get your certificate and move forward with placement guidance.', 'industrial-training' ), 'photo' => 'step-career', 'image' => 0 ],
			],
		],
		'contact'    => [
			'title'      => __( 'Questions? Talk to us', 'industrial-training' ),
			'lead'       => __( 'Our counsellors help you choose the right track and batch.', 'industrial-training' ),
			'phone'      => '+91 86393 54430',
			'whatsapp'   => '+91 86393 54430',
			'email'      => 'info@skillrisetechnologies.com',
			'address'    => __( '18/3, Ramanagar Main Rd, Dobbaspet, Chandanahosahalli, Karnataka 562111', 'industrial-training' ),
			'hours'      => __( 'Mon – Sat, 9:30 AM – 6:00 PM', 'industrial-training' ),
			'cta_title'  => __( 'Seats are limited each batch', 'industrial-training' ),
			'cta_text'   => __( "Register today and we'll call you within 2 working days.", 'industrial-training' ),
			'cta_button' => __( 'Register now', 'industrial-training' ),
			'image'      => 0,
		],
		'footer'     => [
			'copyright' => __( '© {year} Skillrise Technologies. All rights reserved.', 'industrial-training' ),
			'credits'   => '',
		],
		'form'       => [
			'title'           => __( 'Register for Industrial Training', 'industrial-training' ),
			'note'            => __( 'All fields are required.', 'industrial-training' ),
			'success_title'   => __( "You're registered, {name}!", 'industrial-training' ),
			'success_text'    => __( "We've saved your registration for {track}. Our counsellor will call you on {phone} within 2 working days.", 'industrial-training' ),
			// Registration alerts go here only; info@ (the contact email) is kept for enquiries.
			'notify_email'    => 'arunreddy@skillrisetechnologies.com',
			'confirm_student' => 0,
			// Registrations allowed per internet connection every 10 minutes (anti-spam). Raise for college drives,
			// where many students share one Wi-Fi.
			'rate_limit'      => 30,
		],
		'samples'    => [
			// Shows the sample students, colleges and partners from content/data.php to visitors, labelled "Sample".
			// For testing only. Off by default so visitors never see invented entries.
			'show' => 0,
		],
		'seo'        => [
			'enabled'     => 1,
			'title'       => __( 'Industrial Training for Diploma Students | Skillrise Technologies', 'industrial-training' ),
			'description' => __( 'Hands-on industrial training for diploma students in Mechanical, Electrical, Electronics, Civil and Computer Science. Real shop-floor projects, mentors, certificate and placement support.', 'industrial-training' ),
		],
	];
}
