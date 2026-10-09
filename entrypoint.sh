#!/bin/sh
set -e

# If using SQLite, ensure database file exists
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
  mkdir -p /app/database
  touch /app/database/database.sqlite
  chmod 666 /app/database/database.sqlite
fi

# If using MariaDB/MySQL, wait for database to be reachable
if [ "$DB_CONNECTION" = "mariadb" ] || [ "$DB_CONNECTION" = "mysql" ]; then
  echo "Waiting for MariaDB connection ($DB_HOST:$DB_PORT)..."
  until php -r "
    try {
      \$host = getenv('DB_HOST') ?: '127.0.0.1';
      \$port = getenv('DB_PORT') ?: '3306';
      \$db = getenv('DB_DATABASE') ?: 'motacast';
      \$user = getenv('DB_USERNAME') ?: 'root';
      \$pass = getenv('DB_PASSWORD') ?: '';
      new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 2]);
      exit(0);
    } catch (Exception \$e) {
      exit(1);
    }
  "; do
    echo "Database not ready yet, sleeping 2s..."
    sleep 2
  done
  echo "MariaDB is ready!"
fi

# Ensure storage link exists
php artisan storage:link || true

# Run database migrations
php artisan migrate --force

# Seed admin user if not exists
php artisan db:seed --class=AdminUserSeeder --force || true

# Start immortal background queue worker daemon
sh -c '
  echo "[Queue Supervisor] Starting queue worker daemon at $(date)..."
  while true; do
    php /app/artisan queue:work --tries=3 --timeout=1800 --sleep=2 || true
    sleep 3
  done
' >> /app/storage/logs/queue.log 2>&1 &

# Start main PHP server
exec php artisan serve --host=0.0.0.0 --port=8000
