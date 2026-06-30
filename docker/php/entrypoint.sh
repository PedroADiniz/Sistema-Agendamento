#!/bin/sh
set -e

cd /var/www/html

# 1) Instala dependências do Composer na primeira subida.
#    Checamos o autoload.php (não só a pasta): com o vendor em volume Docker,
#    a pasta existe vazia no 1º boot, então testar "-d vendor" não bastaria.
if [ ! -f "vendor/autoload.php" ]; then
    echo "==> Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# 2) Garante o arquivo .env
if [ ! -f ".env" ]; then
    echo "==> Criando .env a partir do .env.example..."
    cp .env.example .env
fi

# 3) Gera a APP_KEY se ainda não existir
if ! grep -q "^APP_KEY=base64" .env; then
    echo "==> Gerando APP_KEY..."
    php artisan key:generate --force
fi

# 4) Gera o JWT_SECRET se ainda não existir
if ! grep -q "^JWT_SECRET=." .env; then
    echo "==> Gerando JWT_SECRET..."
    php artisan jwt:secret --force
fi

# 5) Permissões das pastas de escrita do Laravel
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Backend pronto. Rode 'make migrate' e 'make seed' se ainda não o fez."

exec "$@"
