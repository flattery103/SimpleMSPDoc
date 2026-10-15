# Update to 2.0.0-alpha.7

## From alpha.2, alpha.3, alpha.4, alpha.5 or alpha.6

1. Back up the database and the entire `data/` directory before uploading the update.
2. Extract the new ZIP over the existing website root. Preserve your existing `data/` directory, especially `data/config.php`, sessions and uploaded files. Do not delete it or replace its contents with an empty folder. The ZIP does not contain a configuration file.
3. Sign in as an administrator. The application directs you to **Database upgrades**.
4. Confirm your backup and click **Apply database updates**. Existing organizations, documentation, users and uploads remain intact. The additive migrations add password policy and per-user configuration columns, plus Action1 settings and mapping tables if not already installed. Older rebuilds also receive the organization details/logo fields and encrypted password table. No reinstallation is needed. New Action1 installations start disabled; configure them under Administration → Add-ons. Existing enabled Action1 settings and mappings are retained. Run a fresh Sync now after upgrading to refresh missing update counts and inventory. There is no need to install alpha.5 separately.

Keep the original `data/config.php` application key: it is needed to decrypt MFA secrets and stored passwords. Back up the database and configuration together.

MySQL/MariaDB schema changes can commit implicitly. If an upgrade fails, maintenance remains active. Review `data/upgrade.log.php` through your hosting file manager and restore the database and application backup before retrying. Do not manually clear maintenance or migration records to force a retry.

## From alpha.1 or the legacy application

The old layout and legacy application remain incompatible. Use a clean directory and fresh database; no migration from those versions is provided.

## Field consolidation in alpha.7

No new schema migration is needed from alpha.6. Existing template values resolve into shared fields immediately; an Action1 sync refreshes source data. Duplicate fields are hidden, not deleted. Manual values remain authoritative. Unchanged imported values keep refreshing; edits and clears become manual overrides. The column gear retains your saved choices and maps old duplicate columns to their shared equivalents.
