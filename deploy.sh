#!/bin/bash
set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 Deploying CVG CMS..."

# Build new image
docker build -t cvg_cms:latest .

# Restart with new image (zero-downtime)
docker compose pull 2>/dev/null || true
docker compose up -d --force-recreate cvg_cms

# Wait for app to be healthy
echo "⏳ Waiting for app to be healthy..."
for i in {1..30}; do
  if docker inspect cvg_cms_app --format='{{.State.Health.Status}}' 2>/dev/null | grep -q "healthy"; then
    echo "✅ App is healthy"
    break
  fi
  sleep 2
done

# Run migrations
docker exec cvg_cms_app php artisan migrate --force

# Clear and rebuild caches
docker exec cvg_cms_app php artisan optimize:clear
docker exec cvg_cms_app php artisan optimize

# Restart worker and scheduler
docker compose restart cvg_cms_worker scheduler

echo "✅ Deployment complete!"
