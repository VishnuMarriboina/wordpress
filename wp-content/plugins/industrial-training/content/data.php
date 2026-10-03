<?php
/**
 * Founder, student, college, leadership and partner content.
 *
 * HOW TO EDIT
 * - Copy this file to wp-content/industrial-training/content.php and edit that copy.
 *   It is loaded instead of this one and survives plugin updates.
 * - Images ('photo' / 'logo'): a Media Library attachment ID (e.g. 123), a file bundled in the plugin
 *   (e.g. 'assets/images/founder-arun-kumar-reddy.jpg' — best for photos you own, no external link),
 *   a full image URL, or '' to show an initials avatar. Always fill the matching '...Alt' text.
 * - Social links: paste the full https:// URL. Leave '' (or '#') to hide that icon. Nothing is shown
 *   for an empty link.
 *
 * SAMPLE / PLACEHOLDER ENTRIES
 * - Entries with 'placeholder' => true are invented samples, not real people or organisations. They always
 *   carry a visible "Sample" label.
 * - Visitors see them only while Settings → Industrial Training → "Show sample content to visitors" is on
 *   (for testing). Turn that setting off before launch.
 * - Replace a sample with verified, consented information, then set 'placeholder' => false.
 * - With the setting off, a section appears only when it has at least one real (non-placeholder) entry.
 *
 * VERIFICATION BADGES
 * - 'verified' => true shows "Verified Student" / "College Partner" / the partner's 'badge'.
 *   Only set it after you have confirmed the details (and have permission to publish them).
 */

defined( 'ABSPATH' ) || exit;

$no_social = [ 'linkedin' => '', 'instagram' => '', 'facebook' => '', 'twitter' => '', 'youtube' => '', 'github' => '', 'website' => '' ];

return [

	/* ---------------- Our students & their colleges (real, company-provided) ----------------
	 * Students: real students from Skillrise's records (names and colleges confirmed by the company, Oct 2026).
	 * Fill the other fields only with details the student has confirmed — never invented comments or ratings. A card shows whatever is
	 * filled in: college, track, internship period (start → end), star rating and comment. No student photos.
	 *
	 * Colleges: public facts (checked October 2026 on the colleges' Careers360 / directory listings).
	 * 'logo' shows the college's logo (assets/images/colleges/) on a white header; 'image' takes a campus photo
	 * instead — use one the college has given you permission to use. With neither, the card shows the college's initials. */
	'ourStudents'            => [
		[
			'name'        => 'Kavya',
			'college'     => 'Loyola Polytechnic College',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Rajyalakshmi',
			'college'     => 'Gouthami Institute of Technology and Management for Women',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Yaswanth',
			'college'     => 'Vasavi Polytechnic',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Mahir',
			'college'     => 'KORM College of Engineering',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Naveen',
			'college'     => 'Loyola Polytechnic College',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Narasimha',
			'college'     => 'Vemu Institute of Technology',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Lohitha',
			'college'     => 'Vasavi Polytechnic',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
		[
			'name'        => 'Harshitha',
			'college'     => 'KORM College of Engineering',
			'track'       => '', // e.g. 'Manufacturing & CNC' — from the student's record
			'start'       => '', // e.g. 'May 2025' or 'Nov 2025' (batches start in May or November)
			'end'         => '', // e.g. 'Nov 2025' / 'May 2026' ('' = not shown)
			'comment'     => '', // The student's OWN words, with their permission.
			'rating'      =>  0,  // 1–5 stars given by the student, or 0 to hide.
		],
	],
	'ourColleges'            => [
		[
			'name'        => 'Loyola Polytechnic College',
			'location'    => 'Pulivendula',
			'district'    => 'YSR Kadapa district',
			'type'        => 'Polytechnic',
			'established' => '1980',
			'approvals'   => 'AICTE approved · Admissions through AP POLYCET',
			'courses'     => [ 'Civil', 'Mechanical', 'EEE', 'ECE', 'Computer', 'Mining' ],
			'logo'        => 'assets/images/colleges/LoyolaPolytechnicCollege.jpg',
			'image'       => '',
			'website'     => '',
		],
		[
			'name'        => 'KORM College of Engineering',
			'location'    => 'Kadapa',
			'district'    => 'YSR Kadapa district',
			'type'        => 'Engineering',
			'established' => '2006',
			'approvals'   => 'AICTE approved · Affiliated to JNTU Anantapur',
			'courses'     => [ 'B.Tech', 'M.Tech', 'MBA' ],
			'logo'        => 'assets/images/colleges/KORM.png',
			'image'       => '',
			'website'     => '',
		],
		[
			'name'        => 'Vemu Institute of Technology',
			'location'    => 'P. Kothakota, near Pakala',
			'district'    => 'Chittoor district',
			'type'        => 'Engineering',
			'established' => '2008',
			'approvals'   => 'AICTE approved · Affiliated to JNTU Anantapur · NAAC A+ & NBA accredited',
			'courses'     => [ 'CSE', 'ECE', 'EEE', 'Mechanical', 'Civil' ],
			'logo'        => 'assets/images/colleges/Vemu.jpg',
			'image'       => '',
			'website'     => '',
		],
		[
			'name'        => 'Gouthami Institute of Technology and Management for Women',
			'location'    => 'Proddatur',
			'district'    => 'YSR Kadapa district',
			'type'        => 'Engineering (Women)',
			'established' => '2009',
			'approvals'   => 'AICTE approved · Affiliated to JNTU Anantapur',
			'courses'     => [ 'CSE', 'ECE', 'EEE', 'Civil' ],
			'logo'        => 'assets/images/colleges/GITW.jpg',
			'image'       => '',
			'website'     => '',
		],
		[
			'name'        => 'Vasavi Polytechnic',
			'location'    => 'Banaganapalle',
			'district'    => 'Nandyal district',
			'type'        => 'Polytechnic',
			'established' => '1984',
			'approvals'   => 'AICTE approved · Recognised by SBTET Andhra Pradesh',
			'courses'     => [ 'Civil', 'Mechanical', 'ECE', 'EEE', 'Computer' ],
			'logo'        => 'assets/images/colleges/VasaviPolytechnic.jpg',
			'image'       => '',
			'website'     => '',
		],
	],

	/* ---------------- Founder & CEO (company-provided information) ---------------- */
	'founderData'            => [
		'name'              => 'Y Arun Kumar Reddy',
		'designation'       => 'Founder & CEO',
		'experience'        => '6+ Years',
		'studentsTrained'   => '10000+',
		'collegesConnected' => '50+',
		'photo'             => 'assets/images/founder-arun-kumar-reddy.jpg', // Stored in the plugin, no external link.
		'photoAlt'          => 'Y Arun Kumar Reddy, Founder & CEO',
		'bio'               => 'Y Arun Kumar Reddy, Founder & CEO, brings 6+ years of experience in student training, technology education, and career development. Through his work, he has helped 10000+ students and collaborated with 50+ colleges to provide practical training, technical guidance, and career-focused learning opportunities.',
		'tagline'           => 'Helping students learn, grow and prepare for industry careers.',
		'vision'            => 'To help every student gain practical, hands-on skills and the confidence to grow toward real industry opportunities.',
		'social'            => $no_social, // e.g. 'linkedin' => 'https://www.linkedin.com/in/…'
	],

	/* ---------------- Founder journey / impact (company-provided figures only) ---------------- */
	'impactData'             => [
		[ 'value' => '6+', 'label' => 'Years of Experience' ],
		[ 'value' => '10000+', 'label' => 'Students Trained' ],
		[ 'value' => '50+', 'label' => 'Colleges Connected' ],
		[ 'value' => 'Multiple', 'label' => 'Industry & Academic Collaborations' ],
	],

	/* ---------------- Students (success stories + "Student Reviews & Feedback") ----------------
	 * SAMPLE DATA: invented for testing the layout. Names, companies and quotes are not real.
	 * Replace each entry with a real past student's details (with their permission) and set placeholder => false.
	 * 'rating' is 1–5 (stars), or 0 to hide. */
	'studentsData'           => [
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Ravi K.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Ravi K.',
			'college'        => 'Test Polytechnic College A',
			'course'         => 'Diploma – Mechanical',
			'trainingYear'   => '2023',
			'program'        => 'Manufacturing & CNC',
			'skills'         => [ 'CNC programming', 'Drawing reading', 'Quality inspection' ],
			'opportunity'    => 'Shop-floor trainee role through placement support',
			'currentRole'    => 'CNC Operator',
			'currentCompany' => 'Demo Auto Components Pvt. Ltd.',
			'location'       => 'Hyderabad',
			'rating'         => 5,
			'testimonial'    => 'In college I had only seen CNC machines in pictures. Here I ran a lathe from the first week, learned to read drawings properly and inspected my own parts. When I went for my interview, I could explain exactly what I had done — that made all the difference.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Sneha P.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Sneha P.',
			'college'        => 'Test Polytechnic College B',
			'course'         => 'Diploma – Electrical',
			'trainingYear'   => '2023',
			'program'        => 'Electrical Systems & PLC',
			'skills'         => [ 'PLC ladder logic', 'Panel wiring', 'Industrial safety' ],
			'opportunity'    => 'Maintenance apprenticeship',
			'currentRole'    => 'Electrical Maintenance Technician',
			'currentCompany' => 'Demo Process Industries Ltd.',
			'location'       => 'Secunderabad',
			'rating'         => 5,
			'testimonial'    => 'Wiring a real control panel and fixing faults with the plant engineers taught me more than a whole semester of theory. The mentors were patient and always explained why, not just how. I now troubleshoot motors and drives on my own.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Arjun M.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Arjun M.',
			'college'        => 'Test Engineering College C',
			'course'         => 'Diploma – Electronics',
			'trainingYear'   => '2024',
			'program'        => 'PCB and electronic Components',
			'skills'         => [ 'Arduino & ESP32', 'Sensor interfacing', 'MQTT' ],
			'opportunity'    => 'IoT project internship',
			'currentRole'    => 'Junior Embedded Engineer',
			'currentCompany' => 'Demo IoT Solutions',
			'location'       => 'Bengaluru',
			'rating'         => 4,
			'testimonial'    => 'Our team built a sensor board that sent live machine data to a dashboard. Debugging it at midnight before the demo was stressful but it was the best learning experience I have had. The project went straight into my resume.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Lakshmi R.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Lakshmi R.',
			'college'        => 'Test Polytechnic College A',
			'course'         => 'Diploma – Civil',
			'trainingYear'   => '2023',
			'program'        => 'Civil & AutoCAD',
			'skills'         => [ 'AutoCAD 2D', 'Quantity estimation', 'Site supervision' ],
			'opportunity'    => 'Site engineer trainee',
			'currentRole'    => 'Junior Site Engineer',
			'currentCompany' => 'Demo Constructions',
			'location'       => 'Warangal',
			'rating'         => 5,
			'testimonial'    => 'The weekly site visits were my favourite part. I saw how the drawings I made in AutoCAD turned into real columns and slabs, and I learned to estimate quantities the way contractors actually do it.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Mohammed S.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Mohammed S.',
			'college'        => 'Test Engineering College C',
			'course'         => 'Diploma – Computer Science',
			'trainingYear'   => '2024',
			'program'        => 'Software & Web',
			'skills'         => [ 'JavaScript & React', 'Git workflow', 'REST APIs' ],
			'opportunity'    => 'Web developer internship',
			'currentRole'    => 'Frontend Developer',
			'currentCompany' => 'Demo Software Labs',
			'location'       => 'Hyderabad',
			'rating'         => 5,
			'testimonial'    => 'Working in a small team with code reviews and Git felt like a real job. We deployed an actual web app by the end of six weeks. I learned to ask better questions and to read other people’s code.',
			'social'         => $no_social,
		],
		[
			'placeholder'    => true,
			'verified'       => false,
			'name'           => 'Kiran T.',
			'photo'          => '',
			'photoAlt'       => 'Photo of Kiran T.',
			'college'        => 'Test Polytechnic College B',
			'course'         => 'Diploma – Automobile',
			'trainingYear'   => '2024',
			'program'        => 'Manufacturing & CNC',
			'skills'         => [ 'Milling', 'GD&T basics', 'Measuring instruments' ],
			'opportunity'    => 'Quality department trainee',
			'currentRole'    => 'Quality Inspector',
			'currentCompany' => 'Demo Precision Works',
			'location'       => 'Pune',
			'rating'         => 4,
			'testimonial'    => 'I was nervous at first because I had never used a vernier or height gauge on real parts. By the end I was checking parts on my own and the mock interviews gave me the confidence to talk about it.',
			'social'         => $no_social,
		],
	],

	/* ---------------- Colleges & academic institutions ----------------
	 * SAMPLE DATA: "Test" colleges for layout testing. Replace with real partner colleges (with their approval). */
	'collegeTestimonials'    => [
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Test Polytechnic College A',
			'logo'              => '',
			'logoAlt'           => 'Test Polytechnic College A logo',
			'location'          => 'Hyderabad, Telangana',
			'collaborationYear' => '2023',
			'program'           => 'Manufacturing & CNC, Civil & AutoCAD',
			'studentsTrained'   => '', // Only fill with a verified number.
			'testimonial'       => 'The program gave our final-year diploma students practical exposure we cannot provide on campus. Students came back more confident and with project work they could show in interviews.',
			'testimonialBy'     => 'Principal, Test Polytechnic College A',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Test Polytechnic College B',
			'logo'              => '',
			'logoAlt'           => 'Test Polytechnic College B logo',
			'location'          => 'Secunderabad, Telangana',
			'collaborationYear' => '2023',
			'program'           => 'Electrical Systems & PLC',
			'studentsTrained'   => '',
			'testimonial'       => 'The trainers coordinated closely with our department and the schedule fit around our semester. Students especially valued working on real panels with plant engineers.',
			'testimonialBy'     => 'Head of Department – Electrical, Test Polytechnic College B',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Test Engineering College C',
			'logo'              => '',
			'logoAlt'           => 'Test Engineering College C logo',
			'location'          => 'Warangal, Telangana',
			'collaborationYear' => '2024',
			'program'           => 'PCB and electronic Components, Software & Web',
			'studentsTrained'   => '',
			'testimonial'       => 'A well-structured, hands-on program. The team shared regular progress updates and every student finished with a graded project.',
			'testimonialBy'     => 'Training & Placement Officer, Test Engineering College C',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Test Institute of Technology D',
			'logo'              => '',
			'logoAlt'           => 'Test Institute of Technology D logo',
			'location'          => 'Vijayawada, Andhra Pradesh',
			'collaborationYear' => '2024',
			'program'           => 'Software & Web',
			'studentsTrained'   => '',
			'testimonial'       => 'Our students learned modern development practices — Git, code reviews and deployment — that are hard to cover in the regular curriculum.',
			'testimonialBy'     => 'Academic Coordinator, Test Institute of Technology D',
			'social'            => $no_social,
		],
	],

	/* ---------------- College leadership (Principal, Dean, HOD, TPO, Coordinator) ----------------
	 * SAMPLE DATA. */
	'leadershipTestimonials' => [
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Dr. A. Sample',
			'photo'             => '',
			'photoAlt'          => 'Photo of Dr. A. Sample',
			'designation'       => 'Principal',
			'college'           => 'Test Polytechnic College A',
			'collaborationYear' => '2023',
			'testimonial'       => 'We appreciated the practical approach and how closely the trainers engaged our students through hands-on learning.',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Prof. B. Sample',
			'photo'             => '',
			'photoAlt'          => 'Photo of Prof. B. Sample',
			'designation'       => 'Head of Department – Electrical',
			'college'           => 'Test Polytechnic College B',
			'collaborationYear' => '2023',
			'testimonial'       => 'The plant exposure helped our students connect classroom concepts with real equipment. We have already planned the next batch.',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Mr. C. Sample',
			'photo'             => '',
			'photoAlt'          => 'Photo of Mr. C. Sample',
			'designation'       => 'Training & Placement Officer',
			'college'           => 'Test Engineering College C',
			'collaborationYear' => '2024',
			'testimonial'       => 'The resume reviews and mock interviews made a visible difference in how our students presented themselves to recruiters.',
			'social'            => $no_social,
		],
		[
			'placeholder'       => true,
			'verified'          => false,
			'name'              => 'Dr. D. Sample',
			'photo'             => '',
			'photoAlt'          => 'Photo of Dr. D. Sample',
			'designation'       => 'Dean – Academics',
			'college'           => 'Test Institute of Technology D',
			'collaborationYear' => '2024',
			'testimonial'       => 'A professional team that understands both industry needs and academic schedules.',
			'social'            => $no_social,
		],
	],

	/* ---------------- Connected organisations / partners ----------------
	 * SAMPLE DATA. 'type' examples: College, University, Educational Institution, Training Organization,
	 * Technology Organization, Industry Partner, Placement/Recruitment Partner, Community Organization. */
	'partnerOrganizations'   => [
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'College Partner', 'name' => 'Test Polytechnic College A', 'logo' => '', 'logoAlt' => 'Test Polytechnic College A logo', 'type' => 'College', 'description' => 'Manufacturing and civil training batches for final-year diploma students.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'College Partner', 'name' => 'Test Polytechnic College B', 'logo' => '', 'logoAlt' => 'Test Polytechnic College B logo', 'type' => 'College', 'description' => 'Electrical and PLC training with plant visits.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'College Partner', 'name' => 'Test Engineering College C', 'logo' => '', 'logoAlt' => 'Test Engineering College C logo', 'type' => 'College', 'description' => 'Embedded, IoT and software project training.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'College Partner', 'name' => 'Test Institute of Technology D', 'logo' => '', 'logoAlt' => 'Test Institute of Technology D logo', 'type' => 'Educational Institution', 'description' => 'Software & Web training batches.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Industry Partner', 'name' => 'Demo Auto Components Pvt. Ltd.', 'logo' => '', 'logoAlt' => 'Demo Auto Components logo', 'type' => 'Industry Partner', 'description' => 'Hosts shop-floor training on CNC lines.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Industry Partner', 'name' => 'Demo Process Industries Ltd.', 'logo' => '', 'logoAlt' => 'Demo Process Industries logo', 'type' => 'Industry Partner', 'description' => 'Electrical maintenance and PLC exposure.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Industry Partner', 'name' => 'Demo Software Labs', 'logo' => '', 'logoAlt' => 'Demo Software Labs logo', 'type' => 'Technology Organization', 'description' => 'Mentors for the Software & Web track.', 'social' => $no_social ],
		[ 'placeholder' => true, 'verified' => false, 'badge' => 'Placement Partner', 'name' => 'Demo Careers Network', 'logo' => '', 'logoAlt' => 'Demo Careers Network logo', 'type' => 'Placement/Recruitment Partner', 'description' => 'Referrals and interview opportunities.', 'social' => $no_social ],
	],

	/* ---------------- Partner-network figures (company-provided only) ---------------- */
	'orgImpactData'          => [
		[ 'value' => '50+', 'label' => 'Colleges Connected' ],
		[ 'value' => '10000+', 'label' => 'Students Reached' ],
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
