#!/bin/bash
set -e

# Instalar dependências
composer install --no-interaction --no-progress

# Aguardar o banco de dados
echo "Aguardando o banco de dados..."
until php -r "try { new PDO('mysql:host=db;dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); exit(0); } catch (\Exception \$e) { echo \$e->getMessage().PHP_EOL; sleep(1); exit(1); };"
do
  echo "Aguardando banco de dados..."
  sleep 2
done

# Iniciar o worker
echo "Iniciando processamento de fila..."
exec php artisan queue:work --tries=2
