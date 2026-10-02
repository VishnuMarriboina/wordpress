<?php
/**
 * Default content. Everything here is editable under Settings → Industrial Training.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Placeholder photos (free Unsplash License), used until a Media Library image is chosen.
 * key => [ Unsplash photo id, alt text ].
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
	];
}

function itp_defaults(): array {
	$track = static function ( $name, $branch, $weeks, $summary, $skills, $photo ) {
		return compact( 'name', 'branch', 'weeks', 'summary', 'skills', 'photo' ) + [ 'image' => 0 ];
	};

	return [
		'brand_mark' => 'IT',
		'brand_name' => __( 'Industrial Training', 'industrial-training' ),
		'nav'        => [
			'highlights' => __( 'Why us', 'industrial-training' ),
			'reviews'    => __( 'Reviews', 'industrial-training' ),
			'founder'    => __( 'Founder', 'industrial-training' ),
			'tracks'     => __( 'Tracks', 'industrial-training' ),
			'contact'    => __( 'Contact', 'industrial-training' ),
			'register'   => __( 'Register', 'industrial-training' ),
		],
		'hero'       => [
			'badge'         => __( 'Admissions open · Next batch starts soon', 'industrial-training' ),
			'title'         => __( 'Industrial Training Program', 'industrial-training' ),
			'title_accent'  => __( 'for Diploma Students', 'industrial-training' ),
			'lead'          => __( 'Six to eight weeks of hands-on work in real plants, labs and project teams. Learn the tools industry uses, build a portfolio, and leave with a certificate and placement support.', 'industrial-training' ),
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
			[ 'value' => '2,400+', 'label' => __( 'students trained', 'industrial-training' ) ],
			[ 'value' => '60+', 'label' => __( 'industry partners', 'industrial-training' ) ],
			[ 'value' => '85%', 'label' => __( 'placement support rate', 'industrial-training' ) ],
		],
		'highlights' => [
			'title' => __( 'Why train with us', 'industrial-training' ),
			'lead'  => __( 'Built for diploma students who want industry experience before their first job.', 'industrial-training' ),
			'items' => [
				[ 'icon' => '🏭', 'title' => __( 'Real shop-floor work', 'industrial-training' ), 'text' => __( 'Train inside partner plants and labs, not just a classroom.', 'industrial-training' ) ],
				[ 'icon' => '🧑', 'title' => __( 'Industry mentors', 'industrial-training' ), 'text' => __( 'Every batch is guided by engineers with 10+ years on the job.', 'industrial-training' ) ],
				[ 'icon' => '📜', 'title' => __( 'Recognised certificate', 'industrial-training' ), 'text' => __( 'Get a certificate and project report your college can count as credit.', 'industrial-training' ) ],
				[ 'icon' => '💼', 'title' => __( 'Placement support', 'industrial-training' ), 'text' => __( 'Resume reviews, mock interviews and referrals to hiring partners.', 'industrial-training' ) ],
			],
		],
		'tracks'     => [
			'title'  => __( 'Training tracks', 'industrial-training' ),
			'lead'   => __( 'Pick the track that matches your branch. Each one ends with a graded project.', 'industrial-training' ),
			'button' => __( 'Register for this track', 'industrial-training' ),
			'items'  => [
				$track( __( 'Manufacturing & CNC', 'industrial-training' ), __( 'Mechanical · Automobile', 'industrial-training' ), 8, __( 'Run CNC lathes and milling machines, read drawings and inspect parts on a working shop floor.', 'industrial-training' ), __( 'CNC programming, GD&T basics, Quality inspection', 'industrial-training' ), 'cnc' ),
				$track( __( 'Electrical Systems & PLC', 'industrial-training' ), __( 'Electrical · Electronics', 'industrial-training' ), 8, __( 'Wire control panels, program PLCs and troubleshoot motors and drives with plant engineers.', 'industrial-training' ), __( 'PLC ladder logic, Panel wiring, Industrial safety', 'industrial-training' ), 'plc' ),
				$track( __( 'Embedded & IoT', 'industrial-training' ), __( 'Electronics · Computer Science', 'industrial-training' ), 6, __( 'Build sensor boards, flash microcontrollers and send live machine data to a cloud dashboard.', 'industrial-training' ), __( 'Arduino & ESP32, Sensor interfacing, MQTT', 'industrial-training' ), 'iot' ),
				$track( __( 'Civil & AutoCAD', 'industrial-training' ), __( 'Civil', 'industrial-training' ), 6, __( 'Draft plans in AutoCAD, estimate quantities and visit active construction sites each week.', 'industrial-training' ), __( 'AutoCAD 2D, Quantity estimation, Site supervision', 'industrial-training' ), 'civil' ),
				$track( __( 'Software & Web', 'industrial-training' ), __( 'Computer Science · Any branch', 'industrial-training' ), 6, __( 'Ship a real web app in a small team: HTML, CSS, JavaScript, Git and a deployed backend.', 'industrial-training' ), __( 'JavaScript & React, Git workflow, REST APIs', 'industrial-training' ), 'web' ),
			],
		],
		'steps'      => [
			'title' => __( 'How it works', 'industrial-training' ),
			'items' => [
				[ 'title' => __( 'Register', 'industrial-training' ), 'text' => __( 'Fill the short form. It takes under two minutes.', 'industrial-training' ) ],
				[ 'title' => __( 'Counselling call', 'industrial-training' ), 'text' => __( 'Our team calls within 2 working days to confirm your track and batch.', 'industrial-training' ) ],
				[ 'title' => __( 'Start training', 'industrial-training' ), 'text' => __( 'Join the next batch, work on real projects and earn your certificate.', 'industrial-training' ) ],
			],
		],
		'contact'    => [
			'title'      => __( 'Questions? Talk to us', 'industrial-training' ),
			'lead'       => __( 'Our counsellors help you choose the right track and batch.', 'industrial-training' ),
			'phone'      => '+91 98765 43210',
			'email'      => 'training@example.com',
			'address'    => __( 'Training Centre, Industrial Estate, Hyderabad, Telangana', 'industrial-training' ),
			'hours'      => __( 'Mon – Sat, 9:30 AM – 6:00 PM', 'industrial-training' ),
			'cta_title'  => __( 'Seats are limited each batch', 'industrial-training' ),
			'cta_text'   => __( "Register today and we'll call you within 2 working days.", 'industrial-training' ),
			'cta_button' => __( 'Register now', 'industrial-training' ),
			'image'      => 0,
		],
		'footer'     => [
			'copyright' => __( '© {year} Industrial Training Program. All rights reserved.', 'industrial-training' ),
			'credits'   => __( 'Photos: <a href="https://unsplash.com/">Unsplash</a> contributors, used under the <a href="https://unsplash.com/license">Unsplash License</a>.', 'industrial-training' ),
		],
		'form'       => [
			'title'           => __( 'Register for Industrial Training', 'industrial-training' ),
			'note'            => __( 'All fields are required.', 'industrial-training' ),
			'success_title'   => __( "You're registered, {name}!", 'industrial-training' ),
			'success_text'    => __( "We've saved your registration for {track}. Our counsellor will call you on {phone} within 2 working days.", 'industrial-training' ),
			'notify_email'    => '',
			'confirm_student' => 0,
		],
		'samples'    => [
			// Shows the sample students, colleges and partners from content/data.php to visitors, labelled "Sample".
			// For testing only — switch off before launch.
			'show' => 1,
		],
		'seo'        => [
			'enabled'     => 1,
			'title'       => __( 'Industrial Training Program for Diploma Students', 'industrial-training' ),
			'description' => __( 'Hands-on industrial training for diploma students in Mechanical, Electrical, Electronics, Civil and Computer Science. Real shop-floor projects, mentors, certificate and placement support.', 'industrial-training' ),
		],
	];
}
