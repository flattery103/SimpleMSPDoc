# 2.0.0-alpha.7

Cumulative package: includes all changes since alpha.4, including the unpublished Action1 add-on, password policy and inventory improvements. You can upgrade directly without installing intermediate releases.

- Consolidates matching standard, template and integration fields into one editable field and one table column. Existing template values remain stored internally. Serial number, manufacturer, model, system specifications and other common fields no longer appear twice.
- One shared source-data model supports multiple integrations. Explicit manual edits have highest priority, followed by older manual template data, then integration data. Conflicting integrations use a stable provider-key order, so sync timing cannot change the chosen value. All source values are retained internally.
- Saving an unchanged imported value keeps it eligible for future refreshes. Editing or clearing it creates a manual override. Required fields and legacy choice/number constraints remain validated.
- Action1 notes merge into regular Notes. Identical note blocks are shown once. Retained source blocks refresh on sync; manual additions survive. If you edit or remove an imported block, that source is suppressed so it cannot overwrite your change or reappear during the next sync.
- Replaces the separate column-settings card with a gear at the configuration table's top right. Clicking it opens column choices. Saved selections for old duplicate columns resolve to the shared column.
- Less-used agent metadata is collapsed in the editor. Empty shared fields are omitted from the read view. Genuine custom fields remain available.
- Search uses the merged visible data rather than hidden duplicate values.

No additional database migration is required from alpha.6. Older rebuilds still receive migrations 003 and 004 through browser Database upgrades. Preserve data/config.php and the entire data folder. No records, source values, credentials or application keys are deleted.

Legacy SimpleMSPDoc and the alpha.1 folder layout remain incompatible and require fresh installation. This remains an alpha build; live Action1 verification on the dev site is still required.
