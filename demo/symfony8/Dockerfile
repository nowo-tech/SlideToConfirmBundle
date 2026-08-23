# FrankenPHP + Node for SlideToConfirmBundle Demo (Symfony 8, Vite)
FROM dunglas/frankenphp:1-php8.5-alpine

RUN apk add --no-cache git unzip bash nodejs npm curl

# pnpm (Alpine nodejs does not ship corepack; use npm global install)
RUN npm install -g pnpm
ENV PNPM_HOME="/usr/local/share/pnpm"
ENV PATH="/usr/local/bin:/usr/local/share/pnpm:${PATH}"

RUN install-php-extensions zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN git config --global --add safe.directory /app && \
    git config --global --add safe.directory /var/slide-to-confirm-bundle

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="/app/vendor/bin:${PATH}"
ENV SERVER_NAME=:80

COPY docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
