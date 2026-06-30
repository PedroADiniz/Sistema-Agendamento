# PHP-FPM 8.2 com as extensões necessárias para Laravel 11 + MySQL + JWT
FROM php:8.2-fpm

# Pacotes do sistema e dependências de build
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP exigidas pelo Laravel/MySQL + OPcache (performance)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Configuração do OPcache (acelera muito as requisições no Docker/Windows)
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Em bind mount no Windows os arquivos do projeto pertencem ao root. Para o
# php-fpm conseguir escrever em storage/ (logs, cache), rodamos os workers como
# root neste ambiente de desenvolvimento. (Em produção use www-data + ownership.)
RUN sed -i 's/^user = www-data/user = root/; s/^group = www-data/group = root/' \
    /usr/local/etc/php-fpm.d/www.conf

# Composer (copiado da imagem oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Versões recentes do Composer bloqueiam, durante a resolução, pacotes com
# advisories de segurança (inclusive toda a linha 11.x do Laravel). Como este é
# um projeto de avaliação, desligamos esse bloqueio para o install funcionar.
RUN composer config --global policy.advisories.block false

WORKDIR /var/www/html

# Entrypoint: instala dependências e prepara o app na primeira subida
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
# --allow-to-run-as-root: necessário porque o pool roda como root (ver acima).
CMD ["php-fpm", "--allow-to-run-as-root"]
