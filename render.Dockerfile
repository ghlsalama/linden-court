# Render.com image — one web service running WordPress + MariaDB, self-seeding.
# (Render doesn't run docker-compose, so we bundle the DB in the same container
#  and persist state on a Render Disk mounted at /data.)
FROM wordpress:php8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends mariadb-server sudo \
 && rm -rf /var/lib/apt/lists/* \
 && curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
 && chmod +x /usr/local/bin/wp

# Bake the theme + seed scripts into the WP source tree the official
# wordpress entrypoint copies into /var/www/html on first boot.
COPY wp-content/themes/linden-court /usr/src/wordpress/wp-content/themes/linden-court
COPY scripts /usr/src/scripts
COPY render-entrypoint.sh /usr/local/bin/render-entrypoint.sh

RUN chmod +x /usr/local/bin/render-entrypoint.sh \
 && mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld

# Self-contained: our entrypoint copies WP core, writes wp-config, starts
# MariaDB, seeds, and runs Apache — no reliance on the base image's entrypoint.
ENTRYPOINT ["/usr/local/bin/render-entrypoint.sh"]
