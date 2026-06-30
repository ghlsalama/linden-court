#!/usr/bin/env bash
# Host-side bootstrap: waits for WordPress, runs the famous 5-minute install,
# then installs + activates the theme and core plugins. Idempotent enough to re-run.
set -euo pipefail

cd "$(dirname "$0")"

# Load env
set -a; . ./.env; set +a

echo "Waiting for MariaDB to accept connections..."
# NOTE: gate on `mysqladmin ping` in the db container. The slim WP image lacks
# `mysqlcheck`, so `wp db check` always errors and would loop forever here.
until docker compose exec -T db mysqladmin ping -h localhost -uroot -p"${DB_ROOT_PASSWORD}" --silent >/dev/null 2>&1; do
  sleep 2
done
echo "MariaDB is up."

if docker compose exec -T wordpress wp core is-installed --allow-root >/dev/null 2>&1; then
  echo ">> WordPress already installed, skipping core install."
else
  echo ">> Running core install..."
  docker compose exec -T wordpress wp core install \
    --url="${WP_URL}" \
    --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email \
    --allow-root
fi

echo ">> Setting permalink structure (pretty URLs, good for SEO)..."
docker compose exec -T wordpress wp rewrite structure '/%postname%/' --allow-root
docker compose exec -T wordpress wp rewrite flush --allow-root

echo ">> Installing + activating theme (Hello Elementor)..."
docker compose exec -T wordpress wp theme install hello-elementor --activate --allow-root || echo "WARN: theme install/activate"

echo ">> Installing + activating core plugins (each independent so one bad slug can't block the rest)..."
# elementor              -> page builder (mandatory skill)
# advanced-custom-fields -> custom fields (mandatory skill)
# motopress-hotel-booking-lite -> accommodation booking engine (availability, rates, iCal sync)
for p in elementor advanced-custom-fields motopress-hotel-booking-lite; do
  echo "   - $p"
  docker compose exec -T wordpress wp plugin install "$p" --activate --allow-root || echo "WARN: plugin '$p' failed"
done

echo ">> Deleting default sample content..."
docker compose exec -T wordpress wp post delete 1 2 --force --allow-root 2>/dev/null || true
docker compose exec -T wordpress wp comment delete 1 --force --allow-root 2>/dev/null || true

echo ""
echo "========================================================="
echo "DONE. Visit:    ${WP_URL}"
echo "WP Admin:       ${WP_URL}/wp-admin"
echo "Admin user:     ${WP_ADMIN_USER}"
echo "Admin password: ${WP_ADMIN_PASSWORD}"
echo "phpMyAdmin:     http://localhost:8081"
echo "========================================================="
