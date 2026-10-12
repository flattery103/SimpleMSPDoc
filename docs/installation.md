# Install SimpleMSPDoc

## Compatibility

This rebuild is not backward-compatible with previous versions. Use a new, empty database and a separate installation directory. There is no legacy migration or in-place upgrade.

## Server preparation

On Ubuntu 24.04 LTS, install Apache, PHP, and the database server:

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql php-mbstring php-xml php-zip mariadb-server unzip
sudo a2enmod rewrite
```

MySQL 8.0 is also supported as a database baseline. Keep distribution security updates installed. Composer is not required for release ZIP installations.

Extract a built release to `/var/www/simplemspdoc`. The ZIP contains production vendor dependencies. A GitHub-generated source ZIP is not the built release ZIP.

```bash
sudo mkdir -p /var/www/simplemspdoc/config /var/www/simplemspdoc/var
sudo chown -R www-data:www-data /var/www/simplemspdoc/config /var/www/simplemspdoc/var
sudo chmod 750 /var/www/simplemspdoc/config /var/www/simplemspdoc/var
```

Keep application code owned by the deployment administrator and readable by PHP. Only `config/` and `var/` need to be writable by PHP. Run the token command as the same user that runs PHP so the installer can read its private token file.

## Database

Create an empty UTF-8 database and a user restricted to it. Replace the sample password:

```sql
CREATE DATABASE simplemspdoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'simplemspdoc'@'localhost' IDENTIFIED BY 'REPLACE_WITH_A_UNIQUE_PASSWORD';
GRANT ALL PRIVILEGES ON simplemspdoc.* TO 'simplemspdoc'@'localhost';
```

Schema installation/upgrades need DDL privileges on this application database. Do not use a database root account for the application.

## Apache

Example vhost; configure your TLS certificate using your preferred tooling:

```apache
<VirtualHost *:80>
    ServerName docs.example.com
    DocumentRoot /var/www/simplemspdoc/public
    <Directory /var/www/simplemspdoc/public>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/simplemspdoc-error.log
    CustomLog ${APACHE_LOG_DIR}/simplemspdoc-access.log combined
</VirtualHost>
```

Enable the vhost and configure HTTPS before entering credentials. PHP must see HTTPS as active for secure session cookies. With a TLS-terminating reverse proxy, configure Apache's trusted proxy handling to set the HTTPS environment correctly; the application does not trust arbitrary forwarded headers.

For a subdirectory deployment, use an Apache Alias to `public/`, with the same Directory permissions. Do not alias the entire application directory. The UI detects the base path from the entry script.

Set PHP `upload_max_filesize = 20M` and `post_max_size = 22M` if you want the full default 20 MiB attachment limit. Restart Apache after changing PHP settings. Files are downloaded as attachments; scripts, HTML, SVG and macro-enabled Office documents are rejected. Office attachments require PHP zip.

## Web setup

```bash
cd /var/www/simplemspdoc
sudo -u www-data php scripts/setup-token.php
```

Open `https://docs.example.com/install`. Enter the printed one-time token, empty database connection, administrator name, username, and a 12–72 byte password. The setup token is consumed and the installer is permanently locked after installation. If you lose an unused token, delete only `var/setup-token` and generate another. Do not remove install locks from an installed application.

A partial installation is not retried against a partly populated database. After diagnosing the private logs, use a new empty database and remove incomplete local configuration only if the application has never completed installation.

Enable MFA for the administrator and save the recovery codes. Then create organizations, asset templates, and staff/client users. Client accounts must be read-only. Records default to internal-only; explicitly mark records client-visible when ready to share them.

## Backups

Back up the application database, `config/local.php`, and all of `var/`. The application key in `config/local.php` is needed to decrypt MFA secrets. Store backups privately. For upgrades, keep the previous application release too. Restore-test backups before depending on the software for operational documentation.
