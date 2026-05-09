###############################################################################
# Dockerfile.dev — Imagem leve para desenvolvimento local
#
# Não faz COPY de código nem roda composer/npm.
# Tudo chega via bind mount do compose.override.yaml.
###############################################################################
FROM dunglas/frankenphp:1-php8.4-bookworm

ARG USER_ID=1000
ARG GROUP_ID=1000

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY docker/dev/entrypoint.dev.sh /usr/local/bin/entrypoint.dev.sh

# Instalando dependências do sistema e Node.js 24
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        zip \
        unzip \
        openssh-client \
        gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pcntl gd pdo_mysql pdo_pgsql zip intl exif xdebug \
    && chmod +x /usr/local/bin/entrypoint.dev.sh \
    && chown -R www-data:www-data /usr/local/bin/entrypoint.dev.sh

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Ajusta UID/GID do www-data para coincidir com o usuário do host
RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data || true

RUN mkdir -p /data/caddy /config/caddy /var/www/.npm \
    && chown -R www-data:www-data /data /config /var/www/html /var/www/.npm

RUN git config --system url."https://github.com/".insteadOf "git@github.com:" \
    && git config --system url."https://github.com/".insteadOf "git@github.com:" \
    && git config --system --add safe.directory /var/www/html

USER www-data

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.dev.sh"]

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]

EXPOSE 8000
