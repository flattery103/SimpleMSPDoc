# 2.0.0-alpha.2

Fresh installation only. This rebuild is not backward-compatible with previous versions, including the alpha.1 package layout. No migration is provided.

- Extract directly into the website root; no public directory or enclosing version folder.
- Four folders: app (application and dependencies), assets (styles and scripts), data (configuration and private files), docs (installation and update instructions).
- All application routes use index.php; Apache rewrite rules are unnecessary.
- Browser setup downloads a verification file. Upload it into data using FTP or the hosting file manager, refresh setup, then enter database and administrator details. No command-line access or token copying is required.
- Private configuration, attachments, logs, and sessions have PHP guards, including when Apache ignores .htaccess.

This is an alpha release. Local validation uses PHP 8.3 and MariaDB 10.11. The wider CI matrix and actual Apache deployment still require successful CI runs.
