#!/usr/bin/env bash
# One-time install: starts containers, installs WordPress, activates the theme + plugin.
set -euo pipefail
cd "$(dirname "$0")"

[ -f .env ] || cp .env.example .env
set -a; source .env; set +a

docker compose up -d

wp() { docker compose run --rm wpcli wp "$@"; }

echo "Waiting for WordPress files..."
until docker compose exec -T wordpress test -f /var/www/html/wp-config.php; do sleep 2; done

if ! wp core is-installed 2>/dev/null; then
  wp core install \
    --url="$WP_SITE_URL" \
    --title="$WP_SITE_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
fi

wp theme activate training-theme
wp plugin activate training-core industrial-training

# Landing page using the plugin's full-width template
if [ -z "$(wp post list --post_type=page --name=industrial-training --field=ID)" ]; then
  id=$(wp post create --post_type=page --post_status=publish --post_title="Industrial Training" --post_name=industrial-training --porcelain)
  wp post meta update "$id" _wp_page_template industrial-training-full-width.php
fi
wp rewrite structure '/%postname%/' --hard

echo
echo "Site:       $WP_SITE_URL"
echo "Admin:      $WP_SITE_URL/wp-admin  ($WP_ADMIN_USER / $WP_ADMIN_PASSWORD)"
echo "Training:   $WP_SITE_URL/industrial-training/"
echo "phpMyAdmin: http://localhost:$PMA_PORT"
