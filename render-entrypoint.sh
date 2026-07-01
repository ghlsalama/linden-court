#!/bin/bash
# Self-contained entrypoint (Fly/Render): WordPress + MariaDB on one machine.
# Does NOT rely on the base image's entrypoint. Copies WP core, writes
# wp-config, starts MariaDB, seeds on first boot, then runs Apache.
set -e

DATA="${DATA_DIR:-/data}"
MYSQL_DIR="$DATA/mysql"
UPLOADS_DIR="$DATA/uploads"
PORT="${PORT:-8080}"
SOCK="/run/mysqld/mysqld.sock"
DB_NAME="${WORDPRESS_DB_NAME:-wordpress}"
DB_USER="${WORDPRESS_DB_USER:-wordpress}"
DB_PASS="${WORDPRESS_DB_PASSWORD:-wordpress}"

mkdir -p "$MYSQL_DIR" "$UPLOADS_DIR" /run/mysqld
chown -R mysql:mysql "$MYSQL_DIR" /run/mysqld 2>/dev/null || true

# 0. Ensure WordPress core is present in /var/www/html (bake from /usr/src).
if [ ! -f /var/www/html/index.php ]; then
  echo "[render] copying WordPress core…"
  cp -a /usr/src/wordpress/. /var/www/html/
fi
chown -R www-data:www-data /var/www/html 2>/dev/null || true

# 1. Initialise MariaDB data dir if empty (first boot).
if [ ! -d "$MYSQL_DIR/mysql" ]; then
  echo "[render] initialising MariaDB data dir…"
  ( mysql_install_db --user=mysql --datadir="$MYSQL_DIR" \
    || mariadb-install-db --user=mysql --datadir="$MYSQL_DIR" ) >/tmp/mysqldb.log 2>&1 || { tail -50 /tmp/mysqldb.log; exit 1; }
fi

# 2. Start MariaDB in the background.
mysqld --user=mysql --datadir="$MYSQL_DIR" --bind-address=127.0.0.1 --socket="$SOCK" >/tmp/mysqld.log 2>&1 &
MDB_PID=$!
for i in $(seq 1 60); do mysqladmin --socket="$SOCK" ping >/dev/null 2>&1 && break; sleep 1; done
mysqladmin --socket="$SOCK" ping >/dev/null 2>&1 || { echo "[render] MariaDB did not start"; tail -50 /tmp/mysqld.log; exit 1; }

# 3. Create WP database + user (idempotent).
mysql --socket="$SOCK" <<-EOSQL
  CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;
  CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
  GRANT ALL ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
  FLUSH PRIVILEGES;
EOSQL

# 4. wp-config.php (unix socket + fresh salts) if missing.
if [ ! -f /var/www/html/wp-config.php ]; then
  echo "[render] writing wp-config.php…"
  SALTS="$(curl -s --max-time 10 https://api.wordpress.org/secret-key/1.1/salt/ || true)"
  if [ -z "$SALTS" ]; then
    SALTS="define('AUTH_KEY','a');define('SECURE_AUTH_KEY','b');define('LOGGED_IN_KEY','c');define('NONCE_KEY','d');define('AUTH_SALT','e');define('SECURE_AUTH_SALT','f');define('LOGGED_IN_SALT','g');define('NONCE_SALT','h');"
  fi
  cat > /var/www/html/wp-config.php <<PHP
<?php
define('DB_NAME','$DB_NAME');
define('DB_USER','$DB_USER');
define('DB_PASSWORD','$DB_PASS');
define('DB_HOST','localhost:$SOCK');
define('DB_CHARSET','utf8mb4');
define('DB_COLLATE','');
$SALTS
\$table_prefix='wp_';
define('WP_DEBUG',false);
define('FS_METHOD','direct');
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/');
require_once ABSPATH.'wp-settings.php';
PHP
fi
sed -i "s#define( *'DB_HOST', *[^)]*);#define('DB_HOST', 'localhost:$SOCK');#" /var/www/html/wp-config.php

# 5. Persist uploads on the disk.
if [ -d /var/www/html/wp-content ] && [ ! -L /var/www/html/wp-content/uploads ]; then
  rm -rf /var/www/html/wp-content/uploads
  ln -s "$UPLOADS_DIR" /var/www/html/wp-content/uploads
fi

cd /var/www/html

# 6. First-boot seed.
if [ ! -f "$DATA/.seeded" ]; then
  echo "[render] first boot — installing WordPress, plugins, theme & content…"
  wp core install \
    --url="${WP_URL:-http://localhost}" \
    --title="${WP_TITLE:-Linden Court Serviced Apartments}" \
    --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD:-changeme}" \
    --admin_email="${WP_ADMIN_EMAIL:-admin@example.com}" \
    --skip-email --allow-root || echo "[render] core install skipped (already installed?)"

  wp rewrite structure '/%postname%/' --allow-root || true
  a2enmod rewrite >/dev/null 2>&1 || true
  wp theme activate linden-court --allow-root || true

  for p in elementor advanced-custom-fields motopress-hotel-booking-lite; do
    echo "[render] plugin: $p"
    wp plugin install "$p" --activate --allow-root || echo "[render] plugin $p failed"
  done

  wp option update mphb_currency_symbol GBP --allow-root || true
  wp rewrite flush --allow-root || true

  cp /usr/src/scripts/*.php /tmp/ 2>/dev/null || true
  [ -f /tmp/seed-motopress.php ] && wp eval-file /tmp/seed-motopress.php --allow-root || true
  [ -f /tmp/seed-content.php ]   && wp eval-file /tmp/seed-content.php --allow-root || true
  wp rewrite flush --allow-root || true

  touch "$DATA/.seeded"
  echo "[render] seed complete."
else
  echo "[render] already seeded — skipping install."
fi

# 7. Keep site URL synced to the public URL.
if [ -n "$WP_URL" ]; then
  wp option update home    "$WP_URL" --allow-root || true
  wp option update siteurl "$WP_URL" --allow-root || true
fi

# 8. Apache on $PORT.
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

echo "[render] launching site on port ${PORT}…"
exec apache2-foreground
