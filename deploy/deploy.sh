#!/usr/bin/env bash
# =====================================================================
# Portal de Noticias - Actualizar producción
# Uso (en el VPS, como saul):  bash deploy/deploy.sh
# =====================================================================
set -euo pipefail

CARPETA="/var/www/portal-noticias"
RAMA="main"

cd "$CARPETA"

echo "==> Poniendo el sitio en mantenimiento"
php artisan down --retry=15 || true

# Pase lo que pase, el sitio vuelve a estar en línea al terminar
trap 'php artisan up >/dev/null 2>&1 || true' EXIT

echo "==> Bajando cambios de $RAMA"
git fetch origin "$RAMA"
git reset --hard "origin/$RAMA"

echo "==> Dependencias PHP"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Compilando estilos y JS"
npm ci --no-audit --no-fund
npm run build

echo "==> Migraciones"
php artisan migrate --force

echo "==> Optimizando (config, rutas, vistas)"
php artisan optimize:clear
php artisan optimize

echo "==> Permisos de storage y caché"
chmod -R ug+rwX storage bootstrap/cache

php artisan up
echo ""
echo "✔ Deploy terminado: $(git log -1 --pretty='%h %s')"
