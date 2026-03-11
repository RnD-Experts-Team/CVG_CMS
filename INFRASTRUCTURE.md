# CVG CMS — Infrastructure Deep Dive

A complete explanation of how **Docker**, **nginx**, and **SSL certificates** work together to serve the CVG CMS Laravel application securely in production.

---

## Table of Contents

1. [Overview — How Everything Connects](#overview)
2. [Docker — Containerization](#docker)
   - [Dockerfile — Multi-Stage Build](#dockerfile)
   - [Docker Compose — Services](#docker-compose)
   - [Networking](#docker-networking)
3. [nginx — Reverse Proxy & Web Server](#nginx)
   - [How nginx Sits in Front](#how-nginx-sits-in-front)
   - [backend.cvg.construction vhost](#backendcvgconstruction)
   - [db.cvg.construction vhost](#dbcvgconstruction)
   - [PHP-FPM Passthrough](#php-fpm-passthrough)
   - [Security Headers](#security-headers)
4. [SSL Certificates — HTTPS](#ssl-certificates)
   - [How Let's Encrypt Works](#how-lets-encrypt-works)
   - [DNS Challenge via Hostinger](#dns-challenge-via-hostinger)
   - [Certificate Files](#certificate-files)
   - [Auto-Renewal](#auto-renewal)
5. [Request Lifecycle](#request-lifecycle)
6. [Common Operations & Troubleshooting](#common-operations)

---

## Overview

Here's the big picture of how a request flows through the stack:

```
Browser (HTTPS)
      │
      ▼
 nginx on VPS (port 443)
      │  SSL termination
      │  Static files served directly
      │
      ├──► PHP-FPM (127.0.0.1:9001) ──► cvg_cms_app container ──► Laravel
      │
      └──► phpMyAdmin proxy (127.0.0.1:8081) ──► cvg_phpmyadmin container
```

- **nginx** handles all incoming traffic, terminates SSL, and routes requests.
- **Docker** runs the Laravel app and phpMyAdmin in isolated containers.
- **Let's Encrypt** provides the free SSL certificate, renewed automatically via the Hostinger DNS API.

---

## Docker — Containerization

### Dockerfile — Multi-Stage Build

The `Dockerfile` uses a **3-stage build** to produce a lean production image:

```
Stage 1: composer-stage  (composer:2.8)
  └── Installs PHP dependencies (vendor/) without dev packages

Stage 2: node-stage  (node:20-alpine)
  └── Installs JS dependencies and runs `npm run build`
      Produces compiled assets in public/build/

Stage 3: production  (php:8.4-fpm)
  └── The final image:
      - Installs PHP extensions (pdo_mysql, gd, zip, intl, opcache...)
      - Copies app code + vendor/ from stage 1 + public/build/ from stage 2
      - Runs as www-data (non-root for security)
      - Exposes port 9000 (PHP-FPM)
```

**Why multi-stage?** The final image only contains what's needed to run the app — no Node.js, no Composer binary, no dev tools. This keeps the image small and secure.

**Key PHP configuration applied in the image:**

| Setting | Value | Why |
|--------|-------|-----|
| `upload_max_filesize` | 100M | Allow large file uploads |
| `post_max_size` | 100M | Match upload limit |
| `max_execution_time` | 300s | Long-running requests |
| `memory_limit` | 256M | Enough for Laravel + OPcache |
| `opcache.validate_timestamps` | 0 | Disable file stat checks (production) |

---

### Docker Compose — Services

Four services are defined in `docker-compose.yml`, all sharing the same image (`cvg_cms:latest`):

#### `cvg_cms_app` — Main Application
```yaml
ports:
  - "127.0.0.1:9001:9000"   # PHP-FPM only accessible locally
```
- Runs PHP-FPM, serving the Laravel app.
- Port `9001` on the host maps to port `9000` inside the container.
- Bound to `127.0.0.1` so it is **not exposed to the internet** — only nginx can reach it.
- Has a **healthcheck** that validates PHP-FPM config every 30s.

#### `cvg_cms_worker` — Queue Worker
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```
- Processes background jobs (emails, notifications, etc.).
- Restarts automatically; waits for `cvg_cms_app` to be healthy before starting.
- No ports exposed — it only needs outbound DB/network access.

#### `cvg_cms_scheduler` — Task Scheduler
```bash
while true; do
  php artisan schedule:run &
  sleep 60
done
```
- Runs Laravel's scheduled commands every 60 seconds (replaces a cron job).
- Uses a shell loop because containers don't have cron daemons by default.

#### `cvg_mysql` — MySQL 8 Database
- Internal only, no ports exposed to host.
- Only accessible to other containers on `cvg_network`.

#### `cvg_phpmyadmin` — Database Admin UI
```yaml
ports:
  - "127.0.0.1:8081:80"   # Only accessible locally
```
- Bound to `127.0.0.1:8081` — not directly exposed to the internet.
- nginx reverse-proxies `db.cvg.construction` → `127.0.0.1:8081`.

---

### Docker Networking

All containers share a custom bridge network:

```yaml
networks:
  cvg_network:
    external: true
    name: cvg_network
```

**Why external?** The network was created manually with `docker network create cvg_network`. This allows other future containers (e.g., Redis, a second app) to join the same network without being defined in this `docker-compose.yml`.

On this network, containers can reach each other by **container name** as hostname:
- App connects to MySQL via `DB_HOST=cvg_mysql`
- phpMyAdmin connects to MySQL via `PMA_HOST=cvg_mysql`

---

## nginx — Reverse Proxy & Web Server

nginx runs **on the VPS host** (not in Docker). It handles:
- SSL termination (HTTPS → plain HTTP/FastCGI internally)
- Routing requests to the correct backend
- Serving static files directly from disk
- Enforcing security headers

### How nginx Sits in Front

```
Internet → VPS port 80/443 → nginx
                                ├─ backend.cvg.construction → PHP-FPM (127.0.0.1:9001)
                                └─ db.cvg.construction      → phpMyAdmin (127.0.0.1:8081)
```

Both domains share the **same SSL certificate** (multi-domain via SAN).

---

### backend.cvg.construction

**File:** `/etc/nginx/sites-enabled/backend.cvg.construction`

#### HTTP → HTTPS Redirect
```nginx
server {
    listen 80;
    server_name backend.cvg.construction;
    return 301 https://$host$request_uri;
}
```
Any plain HTTP request is permanently redirected to HTTPS.

#### HTTPS Server Block
```nginx
listen 443 ssl http2;
ssl_protocols TLSv1.2 TLSv1.3;     # Only modern TLS
ssl_prefer_server_ciphers off;       # Let clients pick cipher (modern best practice)
ssl_session_cache shared:SSL:10m;   # Session resumption cache
```

#### Static Assets
```nginx
location ~* \.(jpg|jpeg|png|gif|css|js|woff2|svg|webp...)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    try_files $uri =404;
}
```
Static files are served **directly from `/srv/apps/cvg_cms/public`** on the host — bypassing PHP entirely. This is much faster.

#### Laravel Routing
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
- First tries to serve a real file (`$uri`).
- Then tries a directory (`$uri/`).
- Falls back to `index.php` (Laravel's front controller) for all other routes.

---

### PHP-FPM Passthrough

```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9001;
    fastcgi_param SCRIPT_FILENAME /var/www/html/public$fastcgi_script_name;
    fastcgi_param HTTPS on;
    fastcgi_param HTTP_X_FORWARDED_PROTO https;
    include fastcgi_params;
}
```

**Key details:**
- `fastcgi_pass 127.0.0.1:9001` — sends PHP requests to the container's PHP-FPM via the mapped host port.
- `SCRIPT_FILENAME` uses `/var/www/html/public` (the path **inside the container**), not the host path.
- `HTTPS on` and `HTTP_X_FORWARDED_PROTO https` — tells Laravel it's running over HTTPS so it generates correct `https://` URLs and doesn't redirect infinitely.

---

### db.cvg.construction

**File:** `/etc/nginx/sites-enabled/db.cvg.construction`

This vhost acts as a **reverse proxy** to phpMyAdmin:

```nginx
location / {
    proxy_pass http://127.0.0.1:8081;
    proxy_set_header X-Forwarded-Proto https;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
```

nginx receives the HTTPS request, strips the SSL, and forwards plain HTTP to the container on port `8081`. phpMyAdmin sees a normal HTTP request but knows the original protocol was HTTPS via the `X-Forwarded-Proto` header.

---

### Security Headers

Applied on both vhosts:

| Header | Value | Purpose |
|--------|-------|---------|
| `Strict-Transport-Security` | `max-age=63072000; includeSubDomains; preload` | Force HTTPS for 2 years, include in browser HSTS preload list |
| `X-Frame-Options` | `SAMEORIGIN` | Prevent clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevent MIME-type sniffing |
| `X-XSS-Protection` | `1; mode=block` | Legacy XSS filter for older browsers |
| `Referrer-Policy` | `no-referrer-when-downgrade` | Control referrer info sent to other sites |

---

## SSL Certificates — HTTPS

### How Let's Encrypt Works

Let's Encrypt is a free Certificate Authority (CA). It issues certificates after verifying you control the domain. There are two challenge methods:

| Method | How it works | Used here? |
|--------|-------------|-----------|
| HTTP-01 | Places a file at `/.well-known/acme-challenge/` | ❌ |
| DNS-01 | Creates a temporary TXT DNS record | ✅ |

**We use DNS-01** because it allows issuing wildcard certificates and works even when port 80 is not accessible.

### DNS Challenge via Hostinger

The `certbot-dns-hostinger` plugin automates the DNS-01 challenge:

1. Certbot asks Let's Encrypt for a certificate for `backend.cvg.construction` + `db.cvg.construction`.
2. Let's Encrypt says: "Prove you own the domain by adding this TXT record: `_acme-challenge.backend.cvg.construction`"
3. The plugin uses the **Hostinger API** to automatically add that TXT record.
4. Let's Encrypt verifies the TXT record exists and issues the certificate.
5. The plugin removes the temporary TXT record.

**Credentials file:** `/etc/letsencrypt/hostinger/credentials.ini`
```ini
dns_hostinger_api_token = <your-token>
```

**Issue command used:**
```bash
certbot certonly \
  --authenticator dns-hostinger \
  --dns-hostinger-credentials /etc/letsencrypt/hostinger/credentials.ini \
  -d backend.cvg.construction \
  -d db.cvg.construction
```

---

### Certificate Files

All files live at `/etc/letsencrypt/live/backend.cvg.construction/`:

| File | Contents | Used for |
|------|----------|---------|
| `fullchain.pem` | Your cert + intermediate chain | `ssl_certificate` in nginx |
| `privkey.pem` | Your private key | `ssl_certificate_key` in nginx |
| `chain.pem` | Intermediate chain only | `ssl_trusted_certificate` (OCSP stapling) |
| `cert.pem` | Your cert only | Not used directly |

**Current certificate:**
```
Domains:  backend.cvg.construction, db.cvg.construction
Key type: ECDSA (smaller, faster than RSA)
Valid until: 2026-05-31
```

---

### Auto-Renewal

Let's Encrypt certificates expire every **90 days**. Certbot installs a systemd timer that runs renewal checks twice daily:

```bash
# Check timer status
systemctl status certbot.timer

# Test renewal without actually renewing
certbot renew --dry-run

# Force renew now
certbot renew --force-renewal

# Reload nginx after renewal (picked up by renewal hooks)
systemctl reload nginx
```

Certbot stores renewal configuration at:
```
/etc/letsencrypt/renewal/backend.cvg.construction.conf
```

To ensure nginx reloads after each renewal, a deploy hook can be added:
```bash
echo 'systemctl reload nginx' > /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
```

---

## Request Lifecycle

### A user visits `https://backend.cvg.construction/api/projects`

```
1. Browser resolves backend.cvg.construction → 31.220.58.147 (DNS A record)

2. Browser connects to VPS on port 443 (HTTPS)

3. nginx receives the connection:
   - Presents the Let's Encrypt certificate
   - TLS handshake completes
   - nginx decrypts the request

4. nginx matches the server_name: backend.cvg.construction

5. The URI /api/projects doesn't match a static file

6. nginx hits the `location /` block:
   try_files → no file found → falls back to /index.php

7. nginx passes to PHP-FPM via FastCGI:
   → 127.0.0.1:9001 (host port mapped to container port 9000)

8. PHP-FPM inside cvg_cms_app container receives the request:
   → Boots Laravel
   → Routes to the correct Controller
   → Queries MySQL (cvg_mysql container via cvg_network)
   → Returns JSON response

9. PHP-FPM sends response back to nginx

10. nginx sends the HTTPS response back to the browser
```

### A user visits `https://db.cvg.construction`

```
1-3. Same DNS + TLS steps as above

4. nginx matches server_name: db.cvg.construction

5. nginx proxies to http://127.0.0.1:8081 (phpMyAdmin container)

6. phpMyAdmin connects to MySQL via cvg_network (host: cvg_mysql)

7. Response proxied back through nginx → browser
```

---

## Common Operations

### Docker

```bash
# View all running containers
docker ps

# View logs of the app container
docker logs cvg_cms_app
docker logs cvg_cms_worker
docker logs cvg_cms_scheduler

# Access a running container shell
docker exec -it cvg_cms_app bash

# Run artisan commands
docker exec cvg_cms_app php artisan <command>
docker exec cvg_cms_app php artisan migrate --force
docker exec cvg_cms_app php artisan cache:clear

# Rebuild and restart containers
cd /srv/apps/cvg_cms
docker compose down
docker compose up -d --build

# Restart without rebuilding
docker compose restart
```

### nginx

```bash
# Test config for syntax errors (always do this before reloading)
nginx -t

# Reload nginx (applies config changes without dropping connections)
systemctl reload nginx

# Restart nginx (drops connections briefly)
systemctl restart nginx

# View logs
tail -f /var/log/nginx/backend.cvg.construction.error.log
tail -f /var/log/nginx/backend.cvg.construction.access.log
tail -f /var/log/nginx/db.cvg.construction.error.log
```

### SSL / Certbot

```bash
# List all certificates and their expiry
certbot certificates

# Test renewal
certbot renew --dry-run

# Force renew now
certbot renew --force-renewal && systemctl reload nginx

# View renewal config
cat /etc/letsencrypt/renewal/backend.cvg.construction.conf
```

### Troubleshooting

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| `502 Bad Gateway` | PHP-FPM container not running | `docker ps` → `docker compose up -d` |
| `SSL_ERROR_RX_RECORD_TOO_LONG` | Accessing HTTPS port via HTTP | Use `https://` in the URL |
| `ERR_SSL_PROTOCOL_ERROR` | DNS not yet propagated | Wait or flush DNS cache |
| Laravel generates `http://` URLs | `HTTPS on` not passed to FastCGI | Check `fastcgi_param HTTPS on` in nginx config |
| Certificate expired | Auto-renewal failed | `certbot renew --force-renewal` |
| `Permission denied` on storage | Wrong file ownership | `docker exec cvg_cms_app chown -R www-data:www-data storage` |
