# ============================================================================
# Vesper — Dockerfile de référence (Render / Railway / Koyeb / Oracle VPS)
# Stack : PHP 8.3 (FPM) + Nginx + Supervisord. Extensions : bcmath, sodium,
# opcache, pdo_pgsql (Supabase).
# ============================================================================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor libsodium-dev postgresql-dev \
    && docker-php-ext-install bcmath opcache pdo_pgsql sodium

WORKDIR /var/www/html
COPY . .

# Composer + dépendances (sans scripts : APP_KEY etc. injectés au runtime).
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
