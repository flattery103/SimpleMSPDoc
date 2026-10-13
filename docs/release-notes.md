# 2.0.0-alpha.3

Fresh installation only. This rebuild is not backward-compatible with previous versions, including the alpha.1 package layout. No migration is provided.

- Extract directly into the website root; no public directory or enclosing version folder.
- Four folders: app (application and dependencies), assets (styles and scripts), data (configuration and private files), docs (installation and update instructions).
- All application routes use index.php; Apache rewrite rules are unnecessary.
- Setup opens directly to the database and administrator form. Setup keys and ownership verification have been removed completely. No command-line access is required.
- Private configuration, attachments, logs, and sessions have PHP guards, including when Apache ignores .htaccess.

This is an alpha release. Local validation uses PHP 8.3 and MariaDB 10.11. The wider CI matrix and actual Apache deployment still require successful CI runs.
