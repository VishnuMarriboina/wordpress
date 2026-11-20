# Skillrise New Website (`/new`)

The redesigned Skillrise Technologies website — 3D hero, scroll animations, modern UI — served at
**`https://skillrisetechnologies.com/new`**, next to the classic site, which stays exactly as it is.

- **Separate plugin.** Nothing in `industrial-training` is changed. This plugin only *reads* from it.
- **Same content.** Every text, track, step, stat, contact detail and student review comes from
  **Settings → Industrial Training** and `industrial-training/content/data.php`, so edits there show on both sites.
- **Same registration backend.** The form posts to the existing `POST /wp-json/industrial-training/v1/register`
  with the same fields, nonce flow, validation rules and messages. Registrations, emails, the Registrations
  screen and CSV export are the existing ones. No new endpoint, table, column or option holding data.
- Needs the **Industrial Training** plugin active. WordPress 6.4+, PHP 8.1+.

## How it works

| Piece | What it does |
|---|---|
| `skillrise-new.php` | Adds the rewrite rule `^new/?$ → index.php?skillrise_new=1` (also works as `/?skillrise_new=1` with plain permalinks). On that request it renders `templates/page.php` at `template_redirect` (before the theme), enqueues its own CSS/JS, and dequeues theme + block-library styles **on `/new` only**. |
| `includes/data.php` | Read-only helpers over `itp_settings()` / `itp_content()`: offers by key, icon mapping, skills grouped by track, FAQ (built only from existing settings text), WhatsApp link, legal links. |
| `templates/page.php` | Standalone HTML document (`wp_head()` / `wp_footer()` still run). 12 sections: nav, hero, about, programs, why, journey, skills, benefits (+ student reviews), registration, FAQ, contact, footer. All output escaped. |
| `templates/icons.php` | Inline SVG icon sprite. |
| `assets/css/skillrise-new.css` | Styles for `/new`. Every rule is scoped to `.skillrise-new` (or `body.srn-canvas`, printed only by this template). |
| `assets/js/srn-core.js` | Plain script, deferred. Nav + mobile menu, scroll-spy, reveals, counters, tilt, FAQ accordion, reviews carousel, click-to-load map, **registration form**. Loads the two modules below. |
| `assets/js/srn-motion.js` | ES module: scroll animations. Loads GSAP + ScrollTrigger from `assets/vendor/gsap-3.15.1/`. Imported after page load, skipped for `prefers-reduced-motion`. |
| `assets/js/srn-scene.js` | ES module: the 3D hero. Imports Three.js from `assets/vendor/three-r180/`. Imported after load + idle, only with WebGL and on devices with ≥ 4 cores / ≥ 4 GB and no Save-Data. Otherwise `assets/images/hero-orbit.svg` stays. |
| `assets/vendor/` | Official prebuilt library files, unmodified (one import path changed in `three-r180/addons/RoomEnvironment.js`, marked with a comment). Folder names carry the version, so browsers fetch new copies when a library is upgraded. |
| `assets/fonts/` | Inter + Space Grotesk (variable, latin), self-hosted, OFL. |

### Registration flow (unchanged backend)

1. Client-side checks use the same regexes and messages as the classic form (`industrial-training/assets/js/itp.js`).
2. `GET /wp-json/industrial-training/v1/nonce` → fresh `wp_rest` nonce (the page may be served from cache; no nonce is printed in the HTML).
3. `POST /wp-json/industrial-training/v1/register`, header `X-WP-Nonce`, JSON `{ fullName, email, phone, college, branch, website }` (`website` is the existing honeypot).
4. The existing PHP validates, rate-limits, saves the private `itp_registration` post and sends the emails.
5. `201` → success view with the success text from the settings. `400 { error, fields }` → inline field errors. `403` / `429` / `500` → the server's message. Network failure / 15 s timeout → offline / timeout message. The button is disabled while a request is in flight (no double submissions).

### 3D and motion

- Hero: faceted core, orbit rings with satellites, floating models built in code (gear → CNC, chip → PCB, bolt → electrical, I-beam → civil, `</>` → software, graduation cap), particle field. Pointer parallax, hover reaction, scroll-driven camera/spread.
- Render loop runs only while the hero is on screen and the tab is visible; pixel ratio capped (1.25 on small screens), fewer objects and no environment map on small screens; a single still frame with reduced motion; WebGL context loss falls back to the SVG.
- Skills: CSS 3D orbit of the real track skills (billboard chips, depth fade), paused off-screen.
- GSAP: hero scroll-out, 3D card entrances, journey rail fill + step activation, parallax glows, CTA fold-up.

## Admin portal (`/admin`)

The team's admin portal at **`https://skillrisetechnologies.com/admin`**. It uses WordPress accounts and the existing
registration data and settings — no separate login, database or email system. Files: `includes/admin-dashboard.php`
(routing, data, form handling), `templates/admin.php` (layout), `templates/admin/*.php` (one file per screen + shared
parts), `assets/css/srn-admin.css`, `assets/js/srn-admin.js`.

| Screen | What it does | Data source |
|---|---|---|
| Sign in | Branded form that posts to the standard `wp-login.php` (login-limit / 2FA plugins still apply). | WordPress users |
| Overview | Date range (Today, 7/30/90 days, This month, All time, custom), registrations, daily average, busiest day, failed alerts, all-time total, chart per day/week/month, by branch, latest 6. | `itp_registration` posts |
| Registrations | Search, branch, date range, sort, 20 per page; Call / WhatsApp / Email on every row; cards on phones. | same |
| Registration | All stored fields, email-alert status and error, contact buttons, "Open in WP Admin" for edit/delete. | same |
| Analytics | Same range filter; comparison with the previous period of equal length, trend chart, email-alert split, by branch, top colleges, by weekday. | same |
| Email notifications | Sent / failed / not recorded, success rate, failed alerts with the stored error, who receives alerts, From address, student confirmation on/off, SMTP plugin detected. Read-only — nothing is (re)sent. | `_itp_mail` meta + settings |
| Settings | All Settings → Industrial Training fields in 9 tabs, tracks editor (add / reorder / remove), toggles, unsaved-changes warning. Posts to `wp-admin/options.php` with the existing option group and nonce, so the plugin's own `itp_sanitize_settings()` validates and saves. Fields without a visible input are sent with their current value (that function resets missing fields). Photos are still chosen in WP Admin (Media Library). | `itp_settings` option |
| My profile | First/last/display name, change password (current password required), sign out of other devices, link to the WordPress profile for email changes. | Your WordPress user |

- **Layout:** collapsible sidebar (remembered per browser, tooltips when collapsed), slide-in drawer on phones,
  breadcrumbs, quick actions (Export CSV, Visit website), account menu, toasts, page-load progress bar, count-up
  numbers and gentle entrance animations (all switched off for "reduce motion").
- **Badge:** the red number on "Email notifications" is the real count of failed alerts in the last 30 days.
- **Roles:**
  - *Administrator* (`manage_options`): everything above, plus **Team** — add counsellors (name, email, starting
    password), set a new password for one (signs them out everywhere), or remove their access (the account is kept,
    the role is removed, sessions are ended).
  - *Counsellor* (role `srn_counsellor`, capability `srn_contact_students`, created automatically): a simple
    Overview (new today / last 7 days / this month + the 10 newest students with Call · WhatsApp · Email),
    Registrations and student details (without email-alert status), and My profile. Analytics, Email notifications,
    Settings, Team and CSV export are hidden and return 403. Counsellors get no WordPress toolbar, sign-in lands on
    `/admin`, and any `/wp-admin` URL sends them back to `/admin`.
  - Other users get "No access". All forms use nonces; pages are never cached and are `noindex`.
- **Follow-ups (counsellor work log):** on every student page anyone in the team logs a contact — how
  (Call / WhatsApp / Email), what happened (No answer, Call back later, Interested, Confirmed for batch, Not interested,
  Wrong number), a note and an optional next follow-up date. The full history is kept (who, when, how, note).
  The Registrations list has a **Follow-up** column and filter (Not contacted, Pending, Follow-up due, Interested,
  Confirmed, Closed). Counsellors' Overview shows not-contacted / due counts, their contacts today and this week, due
  follow-ups, students not yet contacted and their recent activity. Admins see the follow-up status of all
  registrations and the latest team activity on the Overview, and on **Team** each counsellor's contacts (7 / 30 days),
  confirmed and interested counts, last activity and a full activity feed.
  Stored as private posts of the type `srn_followup` (post_parent = registration, post_author = who logged it,
  meta `_srn_status`, `_srn_channel`, `_srn_next`). Registration records are never modified; no tables or columns are
  added. Entries can't be edited or deleted in the portal, so the history stays a reliable record.
- The portal shows no WordPress screens or links. Administrators can still open `/wp-admin` directly if ever needed
  (e.g. to delete a registration or pick Media Library photos).
- **Charts** are plain HTML/CSS (no chart library): fast on phones, readable by screen readers, with hover/focus
  tooltips and a "Show as table" view.
- Without this plugin WordPress redirects `/admin` to `/wp-admin`. Change the URL with the `srn_admin_slug` filter,
  then re-save Permalinks. The WhatsApp greeting can be changed with `srn_admin_wa_message`.

Known behaviour of the existing plugin (unchanged): saving the classic Settings → Industrial Training page resets each
track's hidden "weeks" value to 6 (that page has no field for it; the value isn't shown on either site). Saving from
`/admin` keeps it.

## Editing

Like `industrial-training`, this plugin is plain WordPress code: **no build step, no Node.js, no `node_modules`**.
Edit the PHP, CSS and JS files in this folder directly. Asset URLs carry the file's modification time, so browsers and
Hostinger's CDN pick up changes after each upload.

To upgrade a library, download its prebuilt files into a new versioned folder (e.g. `assets/vendor/three-r181/`) and
update the import paths at the top of `srn-scene.js` (Three.js) or the `VENDOR` constant in `srn-motion.js` (GSAP).

## Deploy (Hostinger)

1. Zip **this folder** (`wp-content/plugins/skillrise-new`) so the zip contains `skillrise-new/` at its top level.
2. WP Admin → **Plugins → Add New → Upload Plugin** → choose the zip → **Install Now** → **Activate**.
3. Open `https://skillrisetechnologies.com/new`. If it shows "Page not found", go to **Settings → Permalinks** and click **Save** once.
4. hPanel → **Performance / CDN → Flush cache**.

The `industrial-training` plugin is not touched and does not need re-uploading.

## Rollback

- **Turn it off:** WP Admin → Plugins → **Deactivate** "Skillrise New Website (/new)". `/new` returns 404 again;
  the classic site, registrations and settings are unaffected. (Deactivate from WP Admin rather than deleting
  the folder over FTP — deactivation removes the rewrite rule. If the folder was deleted, re-save Settings → Permalinks.)
- **Remove it:** after deactivating, **Delete**. The plugin stores one option, `srn_rewrite_sig`, removed on deactivation.
- Registrations submitted from `/new` live in the same Registrations list as all others and are kept.

## Options for developers (filters)

| Filter | Default | Use |
|---|---|---|
| `srn_slug` | `'new'` | Change the URL (re-save Permalinks after changing). |
| `srn_noindex` | `true` | `/new` sends `noindex, follow` so search engines don't see two copies of the site. Return `false` when `/new` becomes the main site. |
| `srn_faq` | built from settings | Edit/add/remove FAQ entries (`[ [ 'q' => …, 'a' => … ], … ]`). |
| `srn_terms_url` | `''` | URL of a Terms and Conditions page; the footer link appears only when set. The Privacy Policy link appears when WordPress has a published Privacy Policy page. |

## Third-party code and assets

- Three.js r180 — MIT (`assets/vendor/three-r180/LICENSE.txt`). GSAP 3.15.1 + ScrollTrigger — GSAP Standard "no charge" licence, https://gsap.com/standard-license (`assets/vendor/gsap-3.15.1/LICENSE.txt`).
- Inter, Space Grotesk — SIL Open Font License 1.1 (`assets/fonts/LICENSE-*.txt`).
- Icons — drawn in the style of Lucide (ISC).
- Photos — the existing bundled photos of the Industrial Training plugin (Unsplash License), or Media Library images chosen in its settings. `hero-orbit.svg` is original artwork for this site.
