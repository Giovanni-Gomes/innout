#!/bin/bash
set -e

cd /var/www/html

DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_DATABASE="${DB_DATABASE:-innout}"

echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
for i in $(seq 1 60); do
  if php -r "
    try {
      \$pdo = new PDO(
        'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
        '${DB_USERNAME}',
        '${DB_PASSWORD}'
      );
      exit(0);
    } catch (Throwable \$e) {
      exit(1);
    }
  " 2>/dev/null; then
    echo "MySQL is up."
    break
  fi
  if [ "$i" -eq 60 ]; then
    echo "MySQL did not become ready in time."
    exit 1
  fi
  sleep 2
done

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Evita chaves com \r (Windows) que quebram grep/sed e valores lidos pelo PHP
sed -i 's/\r$//' .env 2>/dev/null || true

# Garante que o .env dentro do container usa os valores vindos do docker-compose.
# Isso evita cair em DB_HOST=127.0.0.1 (valor do .env.example) quando a app está dentro do container.
set_env() {
  key="$1"
  value="$2"

  if grep -q "^${key}=" .env 2>/dev/null; then
    # Usa '|' como delimitador para tolerar valores com '/'
    sed -i "s|^${key}=.*|${key}=${value}|g" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

set_env "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
set_env "DB_HOST" "${DB_HOST}"
set_env "DB_PORT" "${DB_PORT}"
set_env "DB_DATABASE" "${DB_DATABASE}"
set_env "DB_USERNAME" "${DB_USERNAME}"
set_env "DB_PASSWORD" "${DB_PASSWORD}"
set_env "CACHE_DRIVER" "${CACHE_DRIVER:-file}"

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --force
fi

# Evita usar config em cache vinda do host (ex.: DB_HOST=127.0.0.1) quando há volume em bootstrap/cache
php artisan config:clear

php artisan migrate --force

# Sem --no-reload, o ServeCommand zera variáveis que não estão em $passthrough (ex.: DB_*)
# quando existe .env, e o PHP built-in fica sem DB_HOST=mysql → cai em 127.0.0.1 → Connection refused.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
