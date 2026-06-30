# WordPress (Apache + PHP 8.2) with wp-cli baked in, so we can
# script installs/themes/plugins via `docker compose exec wordpress wp ...`
FROM wordpress:php8.2-apache

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp
