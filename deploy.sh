#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# SIPADES VPS Deployment Automation Script
# ==============================================================================

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}  Memulai Proses Deployment SIPADES Production VPS    ${NC}"
echo -e "${BLUE}======================================================${NC}"

# 1. Cek ketersediaan Docker
if ! command -v docker >/dev/null 2>&1; then
    echo -e "${RED}[ERROR] Docker tidak ditemukan! Silakan install Docker terlebih dahulu.${NC}"
    exit 1
fi

# Cek ketersediaan Docker Compose (v2 plugin atau standalone v1)
if docker compose version >/dev/null 2>&1; then
    COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE="docker-compose"
else
    echo -e "${RED}[ERROR] Docker Compose tidak ditemukan! Silakan install Docker Compose.${NC}"
    exit 1
fi

echo -e "${GREEN}[OK]${NC} Docker engine dan Compose terdeteksi ($COMPOSE)"

# 2. Cek berkas konfigurasi environment production
PROD_COMPOSE_FILE="docker-compose.prod.yml"

if [ ! -f ".env.production" ]; then
    if [ -f ".env.production.example" ]; then
        echo -e "${YELLOW}[WARN] Berkas .env.production tidak ditemukan. Menyalin dari .env.production.example...${NC}"
        cp .env.production.example .env.production
    elif [ -f ".env" ]; then
        echo -e "${YELLOW}[WARN] Berkas .env.production tidak ditemukan. Menyalin dari .env...${NC}"
        cp .env .env.production
    else
        echo -e "${RED}[ERROR] Berkas .env.production atau template konfigurasi tidak ditemukan!${NC}"
        exit 1
    fi
fi
echo -e "${GREEN}[OK]${NC} Berkas .env.production siap."

# 3. Jalankan Docker Compose build & start
echo -e "${BLUE}[INFO] Menjalankan build dan start container production...${NC}"
$COMPOSE -f "$PROD_COMPOSE_FILE" up -d --build

# 4. Tunggu MySQL healthcheck
echo -e "${BLUE}[INFO] Menunggu database MySQL siap (healthcheck)...${NC}"
MAX_WAIT=60
COUNTER=0

while true; do
    DB_STATUS=$($COMPOSE -f "$PROD_COMPOSE_FILE" ps db --format json 2>/dev/null | grep -o '"Health":"[^"]*"' | cut -d'"' -f4 || echo "")
    
    # Fallback status check jika Docker compose versi lama tidak mendukung json format
    if [ -z "$DB_STATUS" ]; then
        if $COMPOSE -f "$PROD_COMPOSE_FILE" ps db | grep -qi "healthy"; then
            DB_STATUS="healthy"
        fi
    fi

    if [ "$DB_STATUS" = "healthy" ]; then
        echo -e "${GREEN}[OK]${NC} Database MySQL sudah sehat (healthy) dan siap menerima koneksi."
        break
    fi

    COUNTER=$((COUNTER + 3))
    if [ $COUNTER -ge $MAX_WAIT ]; then
        echo -e "${YELLOW}[WARN] Waktu tunggu healthcheck MySQL melebihi ${MAX_WAIT}s. Melanjutkan tahapan migrasi...${NC}"
        break
    fi

    sleep 3
done

# 5. Generate APP_KEY jika belum terisi di .env.production
if ! grep -q "^APP_KEY=base64:" .env.production; then
    echo -e "${BLUE}[INFO] Generate APP_KEY Laravel...${NC}"
    $COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan key:generate --force
fi

# 6. Jalankan Migrasi Database
echo -e "${BLUE}[INFO] Menjalankan migrasi database...${NC}"
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan migrate --force

# 7. Optimasi Cache Laravel (config, route, view)
echo -e "${BLUE}[INFO] Melakukan caching konfigurasi, rute, dan view...${NC}"
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan config:cache
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan route:cache
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan view:cache

# 8. Setup direktori storage link & perizinan hak akses
echo -e "${BLUE}[INFO] Menyiapkan storage:link dan perizinan hak akses...${NC}"
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app php artisan storage:link || true
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app chown -R www-data:www-data storage bootstrap/cache
$COMPOSE -f "$PROD_COMPOSE_FILE" exec -T app chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}  Deployment SIPADES Selesai dengan Sukses!           ${NC}"
echo -e "${GREEN}  Aplikasi aktif dan dapat diakses melalui port web.  ${NC}"
echo -e "${GREEN}======================================================${NC}"
