# Afriserva Real Estate: Production Deployment & Operations Manual
**Brand**: Afriserva Real Estate ([afriserva.com](https://afriserva.com))  
**Operating Entity**: Cloud Current International Limited ([cloudcurrentng.com](https://cloudcurrentng.com))

---

## 1. System Prerequisites & Architecture Target

Afriserva Real Estate is built on modern PHP 8.2+ without bloated client-side frameworks, running on high-performance Linux (Ubuntu 22.04 LTS / Debian 12 / AlmaLinux 9) or compatible Apache/XAMPP hosting environments.

### Required Software Packages & PHP Extensions

```bash
# Update and install system dependencies
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y nginx mariadb-server curl git unzip ufw certbot python3-certbot-nginx

# Install PHP 8.2 and required extensions
sudo apt-get install -y php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring \
                        php8.2-gd php8.2-xml php8.2-bcmath php8.2-zip php8.2-intl \
                        php8.2-opcache
```

### Essential PHP Configuration (`/etc/php/8.2/fpm/php.ini`)
```ini
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 36M
max_execution_time = 60
date.timezone = "Africa/Lagos"
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
```

---

## 2. Directory Structure & Permissions

The application root should reside outside the public web server document root. Only the `/public` directory must be accessible to the public internet.

```bash
# Recommended deployment directory
/var/www/afriserva/
├── app/                  # Secure PHP MVC Backend
├── bin/                  # CLI Console Runner (console.php)
├── config/               # App Configuration
├── database/             # Normalized SQL Migrations (001 - 012)
├── public/               # <-- WEB DOCUMENT ROOT (index.php, robots.txt, sitemap.xml, assets/)
├── storage/              # Cache, Logs, and File Uploads
│   ├── cache/
│   ├── logs/
│   └── uploads/
├── vendor/               # Composer Dependencies
├── .env                  # Production Secrets (0600 permissions)
└── composer.json
```

### Permission Hardening Commands
```bash
sudo chown -R www-data:www-data /var/www/afriserva
sudo find /var/www/afriserva -type d -exec chmod 755 {} \;
sudo find /var/www/afriserva -type f -exec chmod 644 {} \;

# Writable directories for web server
sudo chmod -R 775 /var/www/afriserva/storage
sudo chmod 600 /var/www/afriserva/.env
```

---

## 3. Web Server Configurations

### Option A: Nginx Production VirtualHost (`/etc/nginx/sites-available/afriserva`)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name afriserva.com www.afriserva.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name afriserva.com www.afriserva.com;

    # Document root points strictly to public directory
    root /var/www/afriserva/public;
    index index.php index.html;

    ssl_certificate /etc/letsencrypt/live/afriserva.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/afriserva.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Enterprise HTTP Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=(self)" always;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location = /sitemap.xml { access_log off; log_not_found off; }

    # Deny direct access to hidden files and system configs
    location ~ /\.(env|git|htaccess|ini) {
        deny all;
        return 404;
    }

    # PHP FastCGI execution
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Cache static assets for 30 days
    location ~* \.(jpg|jpeg|png|webp|gif|svg|css|js|ico|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }
}
```

### Option B: Apache 2.4+ / Shared Hosting (`.htaccess`)
The project includes a root `.htaccess` and `public/.htaccess` to securely route requests while blocking access to `.env`, `storage/logs/`, and source directories.

### Option C: cPanel Git™ Version Control (Automated Pull & Deploy)

1. **Log in to cPanel** &rarr; Navigate to **Files** &rarr; **Git™ Version Control**.
2. Click **Create** (Blue button at top right).
3. Configure the repository settings:
   - **Clone a Repository**: Toggle ON
   - **Clone URL**: `https://github.com/jaybe007/afriserva.properties.git`
   - **Repository Path**: `repositories/properties` (or `public_html` if deploying root directory)
   - **Repository Name**: `afriserva.properties`
4. Click **Create**.
5. Once cloned, navigate to the **Manage** tab:
   - Under **Pull or Deploy**, click **Update from Remote** to pull latest changes.
   - Click **Deploy HEAD Commit** (executes automated tasks defined in `.cpanel.yml`).
6. **Set up Production `.env`**:
   - In cPanel File Manager, create `.env` in the project root by copying `.env.example`.
   - Update database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) from your cPanel MySQL Wizard.
   - Set `APP_URL=https://afriserva.com` and `APP_ENV=production`.

---

## 4. Database Setup & Sequential Migrations

```bash
# 1. Create database and dedicated low-privilege user
mysql -u root -p -e "
CREATE DATABASE afriserva_properties CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'afriserva_user'@'localhost' IDENTIFIED BY 'STRONG_SECRET_PASSWORD_2026';
GRANT ALL PRIVILEGES ON afriserva_properties.* TO 'afriserva_user'@'localhost';
FLUSH PRIVILEGES;
"

# 2. Run migrations sequentially
for file in database/*.sql; do
    echo "Running migration: $file"
    mysql -u afriserva_user -p'STRONG_SECRET_PASSWORD_2026' afriserva_properties < "$file"
done
```

---

## 5. Automated Background Crons & Task Runner

Configure crontab for `www-data` user (`sudo crontab -u www-data -e`):

```bash
# Run scheduled maintenance tasks every 15 minutes
*/15 * * * * php /var/www/afriserva/bin/console.php cron:run >> /var/www/afriserva/storage/logs/cron.log 2>&1

# Re-generate XML Sitemap daily at 03:00 WAT
0 3 * * * php -r "require '/var/www/afriserva/vendor/autoload.php'; \App\Controllers\SeoController::generateXml();" >> /dev/null 2>&1

# Purge logs older than 30 days every Sunday at midnight
0 0 * * 0 find /var/www/afriserva/storage/logs -type f -name "*.log" -mtime +30 -delete
```

### Shared-Hosting Webhook Cron Alternative
For hosting environments without SSH access, trigger tasks via HTTP with the secure token:
```bash
curl -s -X GET "https://afriserva.com/cron/run?token=YOUR_CRON_SECRET_KEY" > /dev/null
```

---

## 6. Payment Webhook Endpoints & Merchant Console Setup

Configure the following live webhook URLs in your payment merchant portals:

| Gateway | Merchant Console Setting | Webhook Target URL | Required Signature Secret |
| :--- | :--- | :--- | :--- |
| **Paystack** | Settings &rarr; Preferences &rarr; Webhooks | `https://afriserva.com/webhook/paystack` | Verified via `X-Paystack-Signature` (HMAC SHA-512) |
| **Flutterwave** | Settings &rarr; Webhooks | `https://afriserva.com/webhook/flutterwave` | Verified via `verif-hash` header |

---

## 7. Disaster Recovery & Backup Strategy

```bash
#!/bin/bash
# /usr/local/bin/backup_afriserva.sh
BACKUP_DIR="/var/backups/afriserva"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p "$BACKUP_DIR"

# 1. Database SQL Dump
mysqldump -u afriserva_user -p'STRONG_SECRET_PASSWORD_2026' afriserva_properties \
    --single-transaction --quick --routines --triggers | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# 2. Uploaded Media & Documents
tar -czf "$BACKUP_DIR/media_$DATE.tar.gz" -C /var/www/afriserva/storage/uploads .

# 3. Rotate backups (Keep 30 days)
find "$BACKUP_DIR" -type f -mtime +30 -delete
```

- **RTO (Recovery Time Objective)**: `< 30 minutes`
- **RPO (Recovery Point Objective)**: `< 1 hour` (via binary log replication)

---

## 8. Rollback & Zero-Downtime Deployment

For seamless updates, utilize symlinked release directories:

```bash
/var/www/afriserva/
├── current -> releases/20261020_v1.2.0
├── releases/
│   ├── 20261019_v1.1.0 (Rollback target)
│   └── 20261020_v1.2.0 (Active)
```

To rollback instantly to a previous release:
```bash
ln -sfn /var/www/afriserva/releases/20261019_v1.1.0 /var/www/afriserva/current
sudo systemctl reload php8.2-fpm nginx
```
