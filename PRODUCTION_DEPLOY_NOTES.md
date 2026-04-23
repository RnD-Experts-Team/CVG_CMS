# Production Deployment Notes — CVG App Update

> Hand this file to the deployment AI/CLI. It explains every change, the impact on existing production data, the order of operations, and verification steps. Two repos are involved:
> - **Backend:** `RnD-Experts-Team/CVG_CMS` (Laravel 12, PHP 8.4, Postgres)
> - **Frontend:** `RnD-Experts-Team/cvg-website` (Next.js 16)

---

## 1. TL;DR — Impact on Existing Production Data

| Concern | Impact | Action Required |
|---|---|---|
| **Database schema** | None — no migrations added/changed. | Nothing. |
| **Existing rows in `projects`, `project_images`, `media`** | None — preserved as-is. | Nothing. |
| **Existing files under `storage/app/public/projects/`** | Preserved. New uploads use a hashed filename (`<name>-<16hex>.<ext>`); old filenames still resolve normally. | Nothing — existing URLs keep working. |
| **Public file URLs already cached/linked** | No change to old URLs. | Nothing. |
| **Dangling DB rows** (image rows pointing to a file that no longer exists on disk) | Possible legacy state. Optional cleanup script provided below. | Run optional cleanup if 403 errors are reported on assets. |
| **CORS** | Production origin `https://cvg.construction` is now whitelisted (previously may have been blocked). | Verify after deploy. |
| **PHP upload limits** | Backend now requires `upload_max_filesize ≥ 200M`, `post_max_size ≥ 210M`, `memory_limit ≥ 512M`. | Update prod `php.ini` / Docker image. |
| **Validation rules** | More permissive (any `image/*` or `video/*`; `featured` accepts `"true"`/`"false"`/`"on"`/`""` etc.). Existing valid payloads keep working. | Nothing. |

**Bottom line:** This is a **non-destructive update**. No data migration is required and no rows must be deleted. Existing media URLs continue to work unchanged.

---

## 2. Repository Changes

### 2.1 Backend — `CVG_CMS`

| File | Status | Purpose |
|---|---|---|
| `config/cors.php` | NEW | CORS config (allows `localhost:3000`, `127.0.0.1:*`, `https://cvg.construction`, `https://www.cvg.construction`). |
| `php.ini` | NEW | Reference PHP ini for raised upload limits (200 MB). Used as a guide; copy values into the actual prod ini. |
| `app/Http/Requests/AdminCMS/ProjectRequest.php` | MODIFIED | (a) `prepareForValidation()` coerces `featured` strings → bool. (b) MIME allowlist replaced with `image/*` or `video/*` regex check. (c) `failedValidation()` now logs failed fields to `storage/logs/laravel.log`. |
| `app/Services/AdminAuthCMS/ProjectService.php` | MODIFIED | (Touched indirectly through other fixes — no behavior changes that affect existing rows.) |
| `app/Traits/UploadImage.php` | MODIFIED | Filenames now stored as `<sanitized>-<16hex>.<ext>` instead of original name. **Prevents collisions** between projects uploading files with identical names. |
| `tests/samples/`, `tests/scripts/test_full_flow.py` | NEW | End-to-end Python test harness. Optional; safe to skip in production. |

### 2.2 Frontend — `cvg-website`

| File | Status | Purpose |
|---|---|---|
| `app/lib/http/http-client.ts` | MODIFIED | Error interceptor now surfaces Laravel validation errors in the toast (`Validation Error — field: msg`). |
| `app/dashboard/projects/create/page.tsx` | MODIFIED | Sends `featured` as `"1"`/`"0"`. Removed pre-flight "content required" gate. |
| `app/dashboard/projects/edit/[id]/page.tsx` | MODIFIED | Sends `featured` as `"1"`/`"0"`. Removed pre-flight "content required" gate. |
| `app/dashboard/projects/projects.ts` | MODIFIED | Type tweak. |
| `app/(root)/projects/[id]/ProjectDetailClient.tsx` | MODIFIED | Detail page renders `<video>` when asset is video (mime starts with `video/` or `type === "video"`). |
| `app/components/ProjectSections/ProjectCard.tsx` | MODIFIED | Project card thumbnail (used in homepage carousel + `/projects` grid) renders `<video autoplay muted loop playsinline>` when first asset is a video. |

---

## 3. Production Deployment Steps

### Step 1 — Backend: PHP ini (REQUIRED)

The new feature allows uploading videos up to 200 MB. Production PHP must allow this.

**Find loaded ini:**
```bash
php --ini | grep "Loaded Configuration"
```

**Append (or set) these directives in the loaded ini:**
```ini
upload_max_filesize = 200M
post_max_size       = 210M
memory_limit        = 512M
max_execution_time  = 300
max_input_time      = 300
max_file_uploads    = 50
```

**Then restart PHP-FPM / web server:**
```bash
sudo systemctl restart php8.4-fpm    # or php-fpm
sudo systemctl reload nginx          # if applicable
```

**If using Docker:** copy `CVG_CMS/php.ini` values into your image's `/usr/local/etc/php/conf.d/uploads.ini` and rebuild.

**If behind a reverse proxy (nginx):**
```nginx
client_max_body_size 210M;
```
Add to the relevant `server { … }` block and reload nginx.

### Step 2 — Backend: pull code + cache

```bash
cd /path/to/CVG_CMS
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan storage:link        # only if /public/storage symlink missing
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **No `php artisan migrate` needed** — there are no new migrations.

### Step 3 — Backend: verify CORS allowlist

`config/cors.php` already includes `https://cvg.construction` and `https://www.cvg.construction`. If the production frontend uses a different domain (e.g. staging), append it to `allowed_origins` in that file before deploy and re-cache config.

### Step 4 — Frontend: pull + build

```bash
cd /path/to/cvg-website
git pull origin master
pnpm install --frozen-lockfile
pnpm build
pnpm start    # or systemctl restart cvg-web
```

Confirm `NEXT_PUBLIC_API_URL` points at the production backend (e.g. `https://api.cvg.construction/api`).

### Step 5 — (Optional) Clean dangling DB rows

Only run this if some production projects show **403 / broken images** because their files were lost or overwritten by the old (pre-fix) collision bug. **It only deletes rows whose underlying file is missing on disk — it does not touch rows whose files exist.**

```bash
cd /path/to/CVG_CMS
psql "$DATABASE_URL" <<'SQL'
WITH dangling AS (
  SELECT pi.id AS project_image_id, m.id AS media_id, m.path
  FROM project_images pi
  JOIN media m ON m.id = pi.media_id
)
SELECT * FROM dangling;
SQL
```

Inspect the listed rows. For each row whose `path` does **not** exist under `storage/app/public/`, delete:

```bash
# bash loop — DRY RUN first (omit the DELETE psql line)
for p in $(psql -tA "$DATABASE_URL" -c \
  "SELECT m.path FROM media m JOIN project_images pi ON pi.media_id = m.id;"); do
  if [ ! -f "storage/app/public/$p" ]; then
    echo "MISSING: $p"
    psql "$DATABASE_URL" -c \
      "DELETE FROM project_images WHERE media_id IN (SELECT id FROM media WHERE path='$p');
       DELETE FROM media WHERE path='$p';"
  fi
done
```

> **Backup first:** `pg_dump "$DATABASE_URL" > /backups/cvg-pre-cleanup.sql`.

### Step 6 — Smoke test (on production)

1. **CORS preflight:**
   ```bash
   curl -i -X OPTIONS https://api.cvg.construction/api/admin/projects \
     -H "Origin: https://cvg.construction" \
     -H "Access-Control-Request-Method: POST"
   ```
   Expect `204` with `Access-Control-Allow-Origin: https://cvg.construction`.

2. **PHP limits live:**
   ```bash
   php -r 'echo ini_get("upload_max_filesize"),"|",ini_get("post_max_size"),"|",ini_get("memory_limit"),PHP_EOL;'
   # expect: 200M|210M|512M
   ```

3. **Dashboard:** open `https://cvg.construction/dashboard/projects`, edit any project, upload one image + one ~50 MB video, save → expect 200 OK and the new media to render in both the dashboard list and the public project detail page.

4. **Project carousel:** open `https://cvg.construction/projects/<id>` of a project that has a video — the video should auto-play muted in the card preview and play with controls in the detail carousel.

---

## 4. Roll-back Plan

All changes are code-only and additive:

```bash
# Backend
cd /path/to/CVG_CMS
git checkout <previous-commit>
php artisan config:cache

# Frontend
cd /path/to/cvg-website
git checkout <previous-commit>
pnpm build && pnpm start

# PHP ini values can stay raised — they are backwards compatible.
# CORS additions can stay — additive only.
# Uploaded files with hashed names are still served correctly by old code.
```

No DB rollback required.

---

## 5. Known Behavior After Deploy

- **Old uploaded files** keep their original filenames; new uploads get hashed filenames. This is intentional and avoids collisions.
- **Validation errors** now appear in the dashboard toast as `Validation Error — <field>: <message>` and are also logged server-side under `storage/logs/laravel.log` (search for `ProjectRequest validation failed`).
- The project list/homepage carousel will autoplay videos muted/looped. If autoplay should be disabled, remove `autoPlay loop` from `cvg-website/app/components/ProjectSections/ProjectCard.tsx`.

---

## 6. Quick Checklist for the Deployment AI

- [ ] Update production `php.ini` with the values in §3 Step 1, restart PHP-FPM.
- [ ] Update nginx `client_max_body_size 210M;` if behind a proxy.
- [ ] `git pull` + `composer install --no-dev` + `php artisan config:cache` on backend.
- [ ] `git pull` + `pnpm install` + `pnpm build` + restart on frontend.
- [ ] Verify `config/cors.php` lists the real production frontend origin.
- [ ] Run the §3 Step 6 smoke tests.
- [ ] (Optional) Run §3 Step 5 cleanup only if 403 errors are reported on legacy assets.
- [ ] **No `php artisan migrate` needed.**
- [ ] **No data deletion needed.**
