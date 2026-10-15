# Install SimpleMSPDoc 2.0.0-alpha.7

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

## Complete setup

Create a new, empty database through your hosting control panel. Enter its host, port, database name, username and password in the installer. Then enter your administrator name, username and password and click **Install SimpleMSPDoc**.

Setup locks automatically after installation. No setup key or ownership verification is required. Sign in, enable authenticator MFA and save your recovery codes, then create organizations and user accounts.

## Requirements

Apache 2.4, PHP 8.3+, MySQL 8.0 or MariaDB 10.11+. PHP needs PDO MySQL, mbstring, DOM, fileinfo and OpenSSL; Office attachments also need zip. Use HTTPS for deployed installations. With a TLS proxy, ensure the hosting configuration tells PHP that HTTPS is active; arbitrary forwarded headers are not trusted.

For a manually configured Apache site, use the application root as DocumentRoot and set `DirectoryIndex index.php`. `Require all granted` must permit that directory. `AllowOverride None` is supported; rewrite rules are unnecessary. The included `.htaccess` files add optional directory protections when overrides are enabled, but private files also have PHP guards that return404 when requested directly.

Set PHP upload_max_filesize to20M and post_max_size to22M through your hosting panel if you want the full default20MiB attachment limit. The installer reports missing runtime requirements; if data is not writable, adjust it through your hosting file manager or ask the hosting provider.

## Compatibility and backups

The rebuild is not backward-compatible with the legacy application. New installations need a clean directory and empty database. Alpha.5 can update alpha.2/alpha.3/alpha.4 through the database upgrade page; see upgrading.md. Alpha.1 layout/storage and legacy versions require a fresh installation.

Back up the database, the entire `data/` folder and the application release. `data/config.php` contains the application key needed for MFA and encrypted passwords. Attachments are stored with a PHP guard; restore or export them through the application, rather than treating stored files as ordinary downloaded documents.

This is an alpha for a separate test installation. PHP8.3/MariaDB10.11 and HTTP file protection are tested locally. The actual Apache runtime, wider version matrix and visual browser checks remain pending.
