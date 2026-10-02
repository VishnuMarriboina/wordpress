# Industrial Training (WordPress plugin)

"Industrial Training Program for Diploma Students" landing page with a registration form,
saved privately in WordPress. Works in any theme, as a shortcode, a block, or a page template.

- WordPress 6.4+, PHP 8.1+. No jQuery, no frameworks, no build step.
- Front-end assets (`assets/css/itp.css` + `assets/js/itp.js`, ~43 KB unminified, ~11 KB gzipped) load only on pages that use the plugin.

## File tree

```
industrial-training/
├── industrial-training.php      Bootstrap, constants, activation
├── uninstall.php                Deletes settings (registrations kept unless ITP_DELETE_DATA)
├── includes/
│   ├── content.php              Content loader, placeholder filter, social-links component, avatars
│   ├── defaults.php             All default content + placeholder photo list
│   ├── helpers.php              Settings merge, allowed values, validation, responsive images
│   ├── frontend.php             Assets, [industrial_training] shortcode, block, page template
│   ├── rest.php                 POST /wp-json/industrial-training/v1/register + emails
│   ├── registrations.php        Private itp_registration CPT, list table, filter, CSV export
│   ├── seo.php                  Title, meta description, Open Graph, JSON-LD
│   └── settings.php             Settings → Industrial Training (all content, tracks repeater)
├── content/data.php             Founder, students, colleges, leadership, partners, CTA (edit a copy, see below)
├── templates/
│   ├── landing.php              Page markup
│   ├── founder.php              Founder & CEO + journey/impact
│   ├── outcomes.php             Success stories, testimonials, colleges, leadership, partners, community, final CTA
│   └── page-full-width.php      "Industrial Training (full screen)" template: landing page only, no theme header/footer
├── blocks/landing/block.json    "Industrial Training Page" block (server-rendered)
├── assets/
│   ├── css/itp.css              Landing styles (scoped under .itp)
│   ├── js/itp.js                Motion, scroll-spy, modal, validation, fetch submit
│   ├── js/block.js              Block editor placeholder
│   ├── css/admin.css            Settings screen
│   └── js/admin.js              Media picker + tracks repeater
└── languages/                   Put industrial-training-xx_XX.mo files here
```

## Installation

1. **Upload and activate.** Copy the `industrial-training` folder to `wp-content/plugins/`, or zip it and use
   Plugins → Add New → Upload. Then activate **Industrial Training**. (In this repo's Docker setup it's already
   mounted, and `./setup.sh` activates it.)
2. **Create the page.** Pages → Add New, title "Industrial Training". Pick one of these:
   - **Recommended:** Page Attributes → Template → **Industrial Training (full screen)**. Leave the content empty.
   - Or add the **Industrial Training Page** block (it is full width by default).
   - Or paste `[industrial_training]` into the content, an Elementor Shortcode widget or a Divi Code module.
     If your builder stores content somewhere the plugin can't detect, the CSS loads in the footer instead.
     To load it in `<head>`, add `add_filter( 'itp_is_landing', '__return_true' );` on that page.
3. **Fill the settings.** Go to Settings → Industrial Training. Every text, stat, card, track, contact detail and
   email option is there. Use **+ Add track**, ↑ / ↓ and Remove to manage tracks. Track names also fill the form's
   "Training track" list.
4. **Set the images.** Click **Import placeholder photos into Media Library** to download the 8 default Unsplash photos,
   with alt text, into empty image slots. Or use **Choose image** on any slot. Alt text always comes from
   the Media Library, so fill it in there.
5. **Test the form.** Submit a registration, then check **Registrations** in the admin menu. Use the track filter,
   the search box and **Export CSV**. Admin emails go to the notification address (default: the site admin email).
   Install an SMTP plugin if your host doesn't deliver `wp_mail()`.

### Notes

- **Sticky header:** with the full-screen template it sticks to the top of the window (below the admin bar).
  With the shortcode or block it sticks under the theme's menu, and breaks if a theme wrapper has `overflow: hidden`.
- **Page caching:** the form uses a `wp_rest` nonce, which is valid for 12–24 hours. If full-page caching keeps pages
  longer than that, exclude this page or set the cache lifetime under 12 hours. Otherwise students see "Your session has
  expired".
- **Behind a proxy/CDN:** the rate limit uses `REMOTE_ADDR`. Return the real client IP with the
  `itp_client_ip` filter.
- **SEO plugins:** when Yoast, Rank Math, AIOSEO or SEOPress is active, the plugin doesn't output its own title, description
  or OG tags. JSON-LD (EducationalOrganization + one Course per track) is still added.
- **Stored values:** branch, year and track are stored in English, and the JSON keys
  (`fullName, email, phone, college, branch, year, track`) match the existing `/register` API.

## Founder, students, colleges & partners content

Page order: Hero → Why us → **Founder & CEO** → **Journey & Impact** → Tracks → How it works → **Student Success Stories**
→ **What Our Students Say** → **Colleges & Institutions** (+ **From College Leadership**) → **Organizations We Work With**
→ **Join Our Community** → **Want to Work With Us?** → Contact → Footer.

All of it comes from one data file with the arrays `founderData`, `impactData`, `studentsData`, `collegeTestimonials`,
`leadershipTestimonials`, `partnerOrganizations`, `orgImpactData`, `companySocial` and `ctaData`.

1. Copy `content/data.php` to `wp-content/industrial-training/content.php` and edit the copy. It is loaded instead of the
   bundled file and survives plugin updates. A theme or plugin can also change the data with the `itp_content` filter.
2. **Placeholders.** Entries with `'placeholder' => true` are samples, not real people or organisations.
   - Logged-in editors see them as dashed cards labelled "Placeholder", plus a note explaining that.
   - Visitors never see them.
   - A section appears to visitors only once it has at least one real entry. Until then, visitors see only the
     Founder, Impact and final CTA sections.
3. **Verification badges.** "Verified Student", "College Partner" or the partner's `badge` text show only when an entry has
   `'verified' => true` and is not a placeholder. Set it only after you have confirmed the details and have permission
   to publish them.
4. **Social links.** Paste full `https://` URLs. Empty values and `#` are never rendered. Every link opens in a new tab
   with `rel="noopener noreferrer"` and an accessible label such as "Ram on LinkedIn (opens in a new tab)". The same
   component (`itp_social_links()`) is used for the founder, students, colleges, leadership, partners and the company.
5. **Photos and logos.** Use a Media Library attachment ID (recommended) or an image URL, and always fill the `…Alt` text.
   Empty values show an initials avatar. Stock photos are deliberately not used for people.
6. **Numbers.** Only the company-provided figures are included: 10+ years, 10,000+ students, 200+ colleges. A college's
   `studentsTrained` stays hidden until you fill it with a verified number.

> Heads-up: the hero stats (2,400+ students, 60+ partners, 85%) came from the first spec and contradict the
> founder figures (10,000+ students, 200+ colleges). Update them under Settings → Industrial Training → Hero.

## Test checklist

| Area | Check |
|---|---|
| Widths | 320, 375, 768, 1024, 1366 px: no horizontal scroll. Hero stacks below 1000. Nav links hide below 760 (Register stays). Stats become rows below 560. |
| Dark mode | Set the OS to dark: surfaces, text and borders switch; hero stays navy; amber text stays readable. |
| Keyboard | Tab: "Skip to content" appears first and jumps to the page. All links and buttons show the amber focus ring. Open the modal with Enter: focus lands on Full name, Tab stays inside, Esc closes, focus returns to the opener. |
| Screen reader | One H1. Nav is "Page sections" and the current section is announced as "current location". Stats read their final values. Errors are announced via aria-describedby. Server errors are announced via role="alert". The success heading is focused. |
| Reduced motion | Turn on "reduce motion": no entrance, reveal, parallax, count-up, shine, ping, orb or modal animation, and everything is visible immediately. |
| Track pre-select | "Register for this track" on any card opens the modal with that track selected. |
| Validation | Submit empty: errors under each field, fields shake, focus goes to the first invalid field. Typing clears errors live. |
| Success | Valid submit: spinner + "Submitting…", then "You're registered, {first name}!". Reopening gives an empty form. |
| Network failure | DevTools → offline → submit: "You appear to be offline…", button says "Try again". Throttle so the request takes over 15 s to see the timeout message. |
| Server validation | POST invalid data to the endpoint: 400 `{error, fields}`. A bad nonce gives 403. |
| Rate limit | 6 attempts from one IP within 10 minutes: the 6th returns 429 with a Retry-After header. |
| Honeypot | Fill the hidden `website` field: 201 response, nothing saved. |
| Admin | Registrations list shows Name, Email, Phone, College, Branch, Year, Track, Date. The track filter and search (name/email/phone/college) work. Export CSV downloads the filtered rows (manage_options only). |
| Emails | The admin notification arrives. With "Send confirmation email" ticked, the student gets one too. |
| SEO | View source: title, meta description, og:image = hero photo, one JSON-LD graph. With Yoast/Rank Math active there are no duplicate tags. |
| New sections | As a visitor (logged out): no placeholder text or dashed cards, and no `#` links. As an editor: placeholders are labelled. Carousels show 3 / 2 / 1 cards at desktop / tablet / phone, prev/next and dots work, and there is no autoplay. |
| Performance | Lighthouse mobile: images have width/height (no CLS), the hero photo is `fetchpriority="high"`, all other images are lazy, JS is deferred. |
