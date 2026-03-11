# CVG CMS — CI/CD Setup Guide

This document describes how the GitHub Actions CI/CD pipeline is configured for the CVG CMS Laravel backend.

---

## Overview

Two workflows run automatically on GitHub:

| Workflow | File | Trigger | Purpose |
|----------|------|---------|---------|
| **CI** | `.github/workflows/ci.yml` | Every PR to `main`, every push to `main` | Code style (Pint) + PHPUnit tests |
| **Deploy** | `.github/workflows/deploy.yml` | Push to `main` only | rsync code → VPS + rebuild Docker containers |

### Flow

```
Developer opens PR
      │
      ▼
CI workflow runs:
  ├── Laravel Pint code style check
  └── PHPUnit tests (SQLite in-memory)
      │
      ▼
PR is merged to main
      │
      ▼
Deploy workflow runs:
  ├── rsync source code → /srv/apps/cvg_cms/ on VPS
  └── SSH: ./deploy.sh → docker build + migrate + cache + restart
```

---

## Required GitHub Secrets

Go to **GitHub → Repository → Settings → Secrets and variables → Actions** and add:

| Secret name | Value | Where to get it |
|-------------|-------|-----------------|
| `SERVER_HOST` | `31.220.58.147` | VPS IP address |
| `SERVER_USER` | `root` | VPS SSH user |
| `SERVER_PORT` | `22` | SSH port (can be omitted, defaults to 22) |
| `SERVER_SSH_KEY` | *(private key — see below)* | Run: `cat /root/.ssh/cvg_cms_deploy` |

### How to get the SSH private key

SSH into the VPS and run:

```bash
cat /root/.ssh/cvg_cms_deploy
```

Copy the **entire output** (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`) and paste it as the value of `SERVER_SSH_KEY`.

The corresponding public key is already appended to `/root/.ssh/authorized_keys` on the VPS.

---

## Pushing the Workflows to GitHub

The workflow files were created on the server. Push them to GitHub with your PAT:

```bash
cd /srv/apps/cvg_cms

# Set remote with PAT temporarily
git remote set-url origin https://<YOUR_PAT>@github.com/RnD-Experts-Team/CVG_CMS.git

# Stage and commit
git add .github/workflows/ci.yml .github/workflows/deploy.yml deploy.sh
git commit -m "ci: add GitHub Actions CI and deploy workflows"

# Push
git push origin main

# Reset remote URL (no credentials in URL)
git remote set-url origin https://github.com/RnD-Experts-Team/CVG_CMS.git
```

---

## CI Workflow Details

**File:** `.github/workflows/ci.yml`

Runs on every pull request to `main` and on every push to `main`.

### Steps

1. **Checkout** — clone the repo
2. **Setup PHP 8.2** — uses `shivammathur/setup-php@v2` with all required extensions
3. **Cache Composer** — caches `vendor/` by `composer.lock` hash for faster runs
4. **Install dependencies** — `composer install --prefer-dist --no-dev` (no dev tools in prod path)

   > For the CI job, dev dependencies ARE installed so Pint and PHPUnit are available.

5. **Copy `.env`** — copies `.env.example` to `.env`
6. **Generate app key** — `php artisan key:generate`
7. **Laravel Pint** — `./vendor/bin/pint --test` (fails if code style violations found)
8. **PHPUnit tests** — `php artisan test --parallel` with SQLite in-memory (configured in `phpunit.xml`)

### Test environment (from `phpunit.xml`)

| Setting | Value |
|---------|-------|
| `DB_CONNECTION` | `sqlite` |
| `DB_DATABASE` | `:memory:` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `SESSION_DRIVER` | `array` |

No external services (MySQL, Redis) are needed for tests.

---

## Deploy Workflow Details

**File:** `.github/workflows/deploy.yml`

Runs only on push to `main`. Uses `concurrency: production-deploy` with `cancel-in-progress: false` — no two deploys run simultaneously, and in-progress deploys are never cancelled.

### Steps

1. **Checkout** — clone the repo on the GitHub Actions runner
2. **Sync code to server** — uses `burnett01/rsync-deployments@7.0.1` to rsync source code to `/srv/apps/cvg_cms/` on the VPS

   **Excluded from rsync** (preserved on server):
   - `.git` — not needed on server
   - `.env` — contains secrets, lives only on server
   - `vendor/` — installed inside Docker image at build time
   - `node_modules/` — same as vendor
   - `storage/app/public` — user uploads (persisted as Docker volume)
   - `storage/logs` — application logs (persisted as Docker volume)

3. **Build & restart** — SSHes into VPS and runs `./deploy.sh`

### deploy.sh

The `deploy.sh` script (runs on VPS after rsync):

```bash
docker build -t cvg_cms:latest .           # Build new Docker image
docker compose up -d --force-recreate cvg_cms  # Restart app container
# Wait for healthy...
docker exec cvg_cms_app php artisan migrate --force   # Run migrations
docker exec cvg_cms_app php artisan optimize:clear    # Clear caches
docker exec cvg_cms_app php artisan optimize          # Rebuild caches
docker compose restart cvg_cms_worker cvg_cms_scheduler  # Restart workers
```

Note: `git pull origin main` was removed from `deploy.sh` — code is now synced by the GitHub Actions rsync step.

---

## SSH Deploy Key

A dedicated ED25519 deploy key was generated for this service:

- **Private key (on VPS):** `/root/.ssh/cvg_cms_deploy` → add as `SERVER_SSH_KEY` GitHub Secret
- **Public key:** appended to `/root/.ssh/authorized_keys` on the VPS

This key is separate from the frontend deploy key (`/root/.ssh/cvg_website_deploy`) to follow the principle of least privilege.

---

## Testing the Pipeline

### Test CI (code style + tests)

1. Create a new branch from `main`
2. Make a change (e.g., add a test or modify a comment)
3. Open a PR to `main`
4. The **CI** workflow will trigger automatically
5. Check the "Actions" tab in GitHub

### Test Deploy

1. Merge any PR to `main` (or push directly)
2. The **Deploy** workflow will trigger automatically
3. Check the "Actions" tab — look for "Deploy to Production"
4. Verify the deployment: `curl https://backend.cvg.construction/up`

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|-------------|-----|
| CI fails on Pint | Code style violations | Run `./vendor/bin/pint` locally to auto-fix |
| CI fails on tests | Test failure | Run `php artisan test` locally |
| Deploy fails at rsync | Wrong SSH key or host | Check `SERVER_SSH_KEY` and `SERVER_HOST` secrets |
| Deploy fails at SSH | `deploy.sh` error | SSH into VPS: `cd /srv/apps/cvg_cms && ./deploy.sh` |
| Container not healthy | App error after deploy | `docker logs cvg_cms_app --tail=100` |
| Migrations failed | DB schema conflict | `docker exec cvg_cms_app php artisan migrate:status` |

---

## Architecture Reference

```
VPS: 31.220.58.147
├── /srv/apps/cvg_cms/           ← rsync target
│   ├── .env                     ← secrets (NOT synced, stays on server)
│   ├── deploy.sh                ← runs docker build + migrations
│   ├── Dockerfile               ← multi-stage PHP 8.4-fpm build
│   ├── docker-compose.yml       ← defines cvg_cms_app, worker, scheduler
│   └── .github/workflows/
│       ├── ci.yml               ← tests on every PR
│       └── deploy.yml           ← deploy on push to main

Docker containers (after deploy):
├── cvg_cms_app     (PHP-FPM :9001) ← serves the API
├── cvg_cms_worker  (queue worker)
└── cvg_cms_scheduler (task scheduler)

nginx (host) → 127.0.0.1:9001 → cvg_cms_app
```
