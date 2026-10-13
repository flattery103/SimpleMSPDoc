# Install SimpleMSPDoc 2.0.0-alpha.2

No command-line access or Apache rewrite rules are required.

## Upload

Extract the contents of the install ZIP directly into the website directory, for example `/www/SimpleMSPDoc`. Its root contains `index.php`, `assets/`, `app/`, `data/`, and `docs/`; there is no enclosing version directory or `public/` folder.

Set the website's DocumentRoot to that same directory. On hosting accounts, upload directly into the document root provided by your hosting service. Use the hosting file manager to ensure PHP can write to `data/`. Application files can remain read-only.

| Folder | Purpose |
| --- | --- |
| assets | Styles and browser scripts |
| app | Bundled application code and libraries |
| data | Protected settings, sessions, uploads and diagnostic logs |
| docs | Installation and reference documentation |

## Open the website

Visit the website URL or server IP. The installer opens automatically through `index.php?route=/install`. If your server does not select index.php as its default page, open `http://SERVER-IP/index.php` directly. No `.htaccess` support or mod_rewrite is needed for application routes.

## Verify website access

1. Click **Download setup file** in the installer.
2. Upload the downloaded `setup.key` file into the `data` folder using your hosting file manager or FTP.
3. Keep the same browser window open and click **Refresh setup**.

You do not need to open the file, copy a token, or run a server command. The file proves that the person running setup can manage this website's files. Downloading alone does not grant installation access. If you switch browsers or lose your session, download and upload a fresh file.

## Complete setup

Create a new, empty database through your hosting control panel. Enter its host, port, database name, username and password in the installer. Then enter your administrator name, username and password and click **Install SimpleMSPDoc**.

Setup locks automatically and consumes the uploaded setup file. Sign in, enable authenticator MFA and save your recovery codes, then create organizations and user accounts.

## Requirements

Apache 2.4, PHP 8.3+, MySQL 8.0 or MariaDB 10.11+. PHP needs PDO MySQL, mbstring, DOM, fileinfo and OpenSSL; Office attachments also need zip. Use HTTPS for deployed installations. With a TLS proxy, ensure the hosting configuration tells PHP that HTTPS is active; arbitrary forwarded headers are not trusted.

For a manually configured Apache site, use the application root as DocumentRoot and set `DirectoryIndex index.php`. `Require all granted` must permit that directory. `AllowOverride None` is supported; rewrite rules are unnecessary. The included `.htaccess` files add optional directory protections when overrides are enabled, but private files also have PHP guards that return404 when requested directly.

Set PHP upload_max_filesize to20M and post_max_size to22M through your hosting panel if you want the full default20MiB attachment limit. The installer reports missing runtime requirements; if data is not writable, adjust it through your hosting file manager or ask the hosting provider.

## Compatibility and backups

This rebuild is not backward-compatible with previous versions. There is no legacy migration. Install into a clean directory and use a new database. Since alpha.2 changes both layout and private-file storage, do not overlay it onto alpha.1 or reuse an installed alpha.1 data directory.

Back up the database, the entire `data/` folder and the application release. `data/config.php` contains the application key needed for MFA. Attachments are stored with a PHP guard; restore or export them through the application, rather than treating stored files as ordinary downloaded documents.

This is an alpha for a separate test installation. PHP8.3/MariaDB10.11 and HTTP file protection are tested locally. The actual Apache runtime, wider version matrix and visual browser checks remain pending.
