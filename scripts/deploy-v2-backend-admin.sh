#!/usr/bin/env bash
set -euo pipefail

DEPLOY_ARCHIVE="$1"
DEPLOY_PATH="/var/www/apex/public_html/v2"
BACKUP_DIR="deploy_backup"

DRY_RUN=false
if [[ "${2:-}" == "--dry-run" || "${2:-}" == "--preview" ]]; then
  DRY_RUN=true
  echo "🚨 Dry run mode enabled — no files will be modified."
fi

echo "-> Switching to deployment directory: $DEPLOY_PATH"
cd "$DEPLOY_PATH"

if [[ ! -f "$DEPLOY_ARCHIVE" ]]; then
  echo "❌ Deployment archive '$DEPLOY_ARCHIVE' not found."
  exit 1
fi

echo "--Unzipping to temporary directory..."
if $DRY_RUN; then
  echo "[dry-run] Would remove app_new"
  echo "[dry-run] Would unzip $DEPLOY_ARCHIVE into app_new"
else
  rm -rf app_new
  unzip "$DEPLOY_ARCHIVE" -d app_new > /dev/null
fi

echo "--Backing up current deployment files to $BACKUP_DIR/..."
if $DRY_RUN; then
  echo "[dry-run] Would remove $BACKUP_DIR and create new"
else
  rm -rf "$BACKUP_DIR"
  mkdir "$BACKUP_DIR"
fi

for ITEM in app docker public scripts tests docker-compose.yml .env; do
  if [[ -e "$ITEM" ]]; then
    if $DRY_RUN; then
      echo "[dry-run] Would copy $ITEM to $BACKUP_DIR/"
    else
      cp -r "$ITEM" "$BACKUP_DIR/"
    fi
  fi
done

echo "--Copying new deployment contents into project root..."
if $DRY_RUN; then
  echo "[dry-run] Would copy contents of app_new/* into current dir"
  echo "[dry-run] Would copy .env.live to .env"
else
  rsync -a --delete \
    --exclude="$BACKUP_DIR/" \
    --exclude=".env" \
    app_new/ .

  # Then update the .env
  cp -f .env.live .env
fi

echo "--Cleaning up temporary files..."
if $DRY_RUN; then
  echo "[dry-run] Would remove app_new and $DEPLOY_ARCHIVE"
else
  rm -rf app_new
  rm -f "$DEPLOY_ARCHIVE"
fi

echo "✅ Deployment files copied successfully!"

# ────────────────────────────────────────────────
#  OPTIONAL POST-DEPLOY ACTIONS
# ────────────────────────────────────────────────

echo ""
echo "--Checking for post-deploy actions..."

read -p "Restart Docker containers? (y/n): " restart_docker
if [[ "$restart_docker" == "y" || "$restart_docker" == "Y" ]]; then
  read -p "Rebuild Docker containers too? (y/n): " rebuild
  if [[ "$rebuild" == "y" || "$rebuild" == "Y" ]]; then
    if $DRY_RUN; then
      echo "[dry-run] Would run: docker compose down && docker compose up -d --build"
    else
      docker compose down
      docker compose up -d --build
    fi
  else
    if $DRY_RUN; then
      echo "[dry-run] Would run: docker compose restart"
    else
      docker compose restart
    fi
  fi
fi

read -p "Clear CodeIgniter config cache? (y/n): " clear_cache
if [[ "$clear_cache" == "y" || "$clear_cache" == "Y" ]]; then
  if $DRY_RUN; then
    echo "[dry-run] Would run: docker compose exec app php spark config:clear"
  else
    docker compose exec app php spark cache:clear
  fi
fi

read -p "Run composer install? (y/n): " run_composer
if [[ "$run_composer" == "y" || "$run_composer" == "Y" ]]; then
  if $DRY_RUN; then
    echo "[dry-run] Would run: docker compose exec app composer install --no-dev --optimize-autoloader"
  else
    docker compose exec app composer install --no-dev --optimize-autoloader
  fi
fi

read -p "Optimize app on production? (y/n): " optimized_app
if [[ "$optimized_app" == "y" || "$optimized_app" == "Y" ]]; then
  if $DRY_RUN; then
    echo "[dry-run] Would run: docker compose exec app php spark optimize"
  else
    docker compose exec app php spark optimize
  fi
fi


echo "✅ Post-deployment actions complete!"
