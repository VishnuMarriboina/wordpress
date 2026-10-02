# Deploying to Vercel

Vercel runs this WordPress site as a single PHP serverless function using the
community runtime [`vercel-php`](https://github.com/vercel-community/php) (PHP 8.3,
same as `docker-compose.yml`). Docker is only used for local development.

## How it works

| File | Purpose |
| --- | --- |
| `vercel.json` | Declares the `api/index.php` function and rewrites every URL to it |
| `composer.json` | Its `vercel` script runs `vercel/build.php` during the Vercel build |
| `vercel/build.php` | Downloads WordPress core into `./wordpress/`, copies in the theme, plugin, mu-plugin and `wp-config.php`, generates salts |
| `vercel/seed-demo.php` | Demo mode only: installs WordPress into SQLite and creates the admin account |
| `vercel/wp-config.php` | Reads DB credentials, salts and URLs from environment variables |
| `vercel/mu-plugins/` | Vercel-specific tweaks (pretty permalinks) |
| `api/index.php` | Router: serves static assets, runs `wp-admin/*.php` etc., otherwise WordPress `index.php` |
| `api/php.ini` | PHP settings for the function |

## Demo mode: UI testing without a database

If **`DB_HOST` is not set**, the build installs WordPress into a bundled SQLite database
(via the official *SQLite Database Integration* plugin) and creates the admin account.
No database provider is needed.

Set these in Vercel → Settings → Environment Variables, then deploy (see step 3):

| Name | Example |
| --- | --- |
| `WP_ADMIN_USER` | `admin` (default) |
| `WP_ADMIN_PASSWORD` | a strong password. If unset, one is generated and printed in the build log |
| `WP_ADMIN_EMAIL` | `you@example.com` |
| `WP_SITE_TITLE` | `Training Site` |

Log in at `https://<your-deployment>/wp-admin/`. Training Core is active, `training-theme`
is active, permalinks are set to *Post name*, and there is a sample course at `/courses/sample-course/`.

**Demo mode is for looking at the UI only.** Anything you change in wp-admin (posts,
settings, menus) lives in the function's `/tmp` and **resets** whenever Vercel starts a
new instance (after idle time, on redeploy, or under parallel load). To change the
starting content, edit `vercel/seed-demo.php`. Changing the admin password or other
env vars requires a redeploy.

To switch to a real database later, set `DB_HOST` and the other DB variables and redeploy.

## 1. Create a MySQL database

Vercel does not host MySQL. Create a MySQL 8 / MariaDB database with any provider
that allows connections from the internet (e.g. Aiven, Railway, DigitalOcean, AWS RDS).
Pick a region close to your Vercel function region.

## 2. Set environment variables in Vercel

Project → Settings → Environment Variables:

| Name | Example | Required |
| --- | --- | --- |
| `DB_HOST` | `mysql.example.com:3306` | yes |
| `DB_NAME` | `wordpress` | yes |
| `DB_USER` | `wordpress` | yes |
| `DB_PASSWORD` | `…` | yes |
| `DB_SSL` | `true` (most managed DBs require TLS) | no |
| `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT` | generate at https://api.wordpress.org/secret-key/1.1/salt/ (if unset, random ones are generated per deploy, which logs everyone out on each deploy) | recommended |
| `WP_HOME` | `https://your-site.vercel.app` (your production domain) | recommended |
| `WP_TABLE_PREFIX` | `wp_` | no |
| `WP_VERSION` | `latest` or e.g. `7.1.2` to pin | no |
| `WP_DEBUG` | `true` to log PHP errors to the function logs | no |

If `WP_HOME` is unset, the site uses the request host. That is convenient for preview
deployments, but set it for production.

## 3. Deploy

Either import the Git repository in the Vercel dashboard (Framework preset: **Other**,
leave build/output settings empty), or use the CLI:

```bash
npm i -g vercel
vercel link
vercel --prod
```

## 4. First-time install

Open `https://<your-domain>/wp-admin/install.php`, complete the installer, then in wp-admin:

1. **Plugins** → activate **Training Core**
2. **Settings → Permalinks** → choose **Post name** → Save

`training-theme` is the default theme, so it is active automatically.

Moving an existing local site instead? Export the local DB (phpMyAdmin at
`localhost:8081`), import it into the hosted DB, then update the URLs, e.g.
`wp search-replace 'http://localhost:8080' 'https://your-site.vercel.app'` before exporting.

## Serverless limitations (important)

- **Read-only filesystem.** Plugins and themes cannot be installed or updated from
  wp-admin (`DISALLOW_FILE_MODS` is on). Add them to the repo and redeploy. A new plugin
  needs a copy step in `vercel/build.php`.
- **Media uploads are not persisted.** Uploads to `wp-content/uploads` fail or vanish.
  Use an offload plugin that stores media in S3, Cloudflare R2 or similar
  (e.g. *WP Offload Media*) before relying on the Media Library.
- **Request size limit 4.5 MB** (Vercel limit), so large imports or uploads through wp-admin won't work.
- **Cold starts** add latency to the first request after idle time. WP-Cron runs only
  when pages are visited.
- `vercel dev` is not supported by `vercel-php`. Keep using `./setup.sh` / Docker locally.
