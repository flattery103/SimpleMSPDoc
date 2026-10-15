# Action1 add-on

The optional Action1 add-on is included since SimpleMSPDoc alpha.5 and disabled by default. It imports Action1-managed devices into organization configurations. It does not deploy patches, run scripts, control devices or write to Action1. This first version supports one Action1 Enterprise with US or EU regional API endpoints.

## Install and enable

1. Back up your database and the entire data directory, including config.php and its application key. Upload the alpha.7 package over the website root while preserving data/.
2. Sign in as administrator and apply **Database upgrades**. Migration 003 adds only new tables and starts disabled. No reinstallation is required for installed alpha.2, alpha.3 or alpha.4 systems.
3. Open **Administration → Add-ons → Action1**. PHP cURL must be enabled; the settings page reports if it is missing.
4. In Action1, open **Configuration → Users & API Credentials → New API Credentials**. Assign a role that can read the organizations and endpoint data to import. Copy the Client ID and Client Secret into this add-on's settings, choose the correct region, enable Action1 and save.
5. Click **Test connection**, then **Load organizations**.
6. Map each Action1 organization to an existing SimpleMSPDoc organization. Create local organizations first if necessary. Mapping is explicit; names do not automatically determine where devices go.
7. Click **Sync now** for a mapping. Keep the page open until it completes. Device configurations appear under that organization's **Configurations** section.

No server command, cron job or PowerShell is required. Scheduled/unattended sync and full hardware/software reports are outside this version. The managed-endpoint hardware summary is imported.

## What is imported

The add-on uses a dedicated template internally; the UI displays shared configuration fields for device name, operating system, IP address, external address, platform, agent version/install date, last seen, status, subscription status, last reported user, missing critical/other update counts, serial number, manufacturer, RAM, disk, CPU, MAC address and Action1 notes when returned by Action1. The current API nests counts under `missing_updates.critical` and `missing_updates.other`; older flat counts are also accepted. Missing fields remain unknown or retain their prior values; only a returned zero means zero. Values that are absent from a response do not clear previously imported fields. Fields are limited to 1,000 characters each.

New configurations are internal only. Imported fields are integration-managed and refreshed on subsequent syncs. Source values fill shared configuration fields. Unchanged values keep refreshing; editing or clearing a value creates a manual override. Existing manually entered template data also takes priority. Action1 notes merge into the regular Notes field without repeated blocks; editing/removing an imported block suppresses later changes from that source. Model, device type, purchase/warranty dates and linked location/contact are maintained locally. Local configuration names, notes, categories, tags, visibility, location/contact references and additional custom fields are preserved. Sync does not change or import passwords.

The Action1 ID is used for matching within each organization mapping. Sync never merges an existing manually created configuration by hostname. Initial import may therefore create a separate record for a device you already documented manually. Archived imported records are skipped. Devices absent from Action1 are retained locally. No automatic delete, archival or restoration takes place.

## Resume and errors

Discovery and import run in pages of up to 50 items. Progress and cursors are saved in the database after each successful batch. Use **Stop after current batch**, close the browser, or let a session expire; use **Resume sync** or **Continue loading organizations** later. **Sync now** starts a fresh pass. A malformed/invalid page is rolled back without advancing its cursor.

Requests are paced to at most one every three seconds for this add-on. Action1 uses dynamic Enterprise-wide limits, so other integrations may still cause HTTP 429 responses. The browser honors the server retry delay and offers resume if it must stop. Do not open multiple browser runners for the same mapping.

Check region, credentials and role permissions if Action1 rejects access. Keep the browser session active while syncing. Network errors, rate limits and mapping errors appear on the add-on page. Disabled mappings and disabled add-ons make no sync requests and retain imported records.

## Credentials and tenant boundaries

Credentials and cached access tokens are encrypted using a separate key derived from data/config.php's application key. Keep that key when backing up, restoring or upgrading. Credentials are not shown again or written into audit entries or API error messages. Only staff administrators may configure or run this add-on; existing organization/record permissions govern imported configurations.

After mapping, the client ID and region are pinned to prevent accidental tenant mixing. To rotate a secret for the same client ID, leave Client ID blank, enter the new Client Secret, and save. A mapping that already has imported records cannot move those records into another local organization. Disabling a mapping is supported. Account/region switching or migration to another Enterprise requires a separately designed migration rather than relabeling existing records.

## Validation

The connector is tested with fake Action1 HTTP responses and real local database/HTTP integration tests. Live Action1 credentials were not available during development. Validate Test connection, organization mapping and one small organization on the dev site before broader use or publication.

Official API references:
- https://www.action1.com/api-documentation/api-credentials/
- https://www.action1.com/api-documentation/authentication/
- https://www.action1.com/api-documentation/making-example-calls/
- https://www.action1.com/api-documentation/how-action1-api-works/

- https://app.action1.com/apidocs/ (Endpoint schema)

## Shared fields for future adapters

Adapters should map their API values to ConfigurationDetails::FIELDS and call AssetService::setSourceDetails with a stable provider key. Shared details and source notes are stored per provider; do not add provider-prefixed visible copies of standard fields. Integration-owned compatibility field IDs belong in external_records.managed_fields. Manual details and ordinary custom fields remain separate from source data.
