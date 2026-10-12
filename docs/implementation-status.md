# Rebuild implementation status

## Delivered

The alpha rebuild includes organizations, locations, contacts, typed asset templates and custom fields, articles/revisions, private attachments, related records, scoped search, roles, staff/client classification, internal-only visibility, local authentication and authenticator MFA, auditing, soft deletion, a protected web installer, database migration runner, and production ZIP packaging. External connector contracts are reserved; no active connectors or vault ship.

The README warns prominently that this is not backward-compatible with previous versions and requires a fresh database without a legacy migration path.

## Verification

- PHP 8.3.6, MariaDB 10.11.14: 33 PHPUnit tests / 190 assertions pass.
- HTTP tests cover actual install/forms/login, required-MFA gating, CSRF, article conflicts/restoration, asset relationships and attachment downloads, hidden references, client restrictions and subdirectory URLs.
- Concurrent MFA enrollment and session reset regressions fail before the fix and pass after it.
- Archived asset values are removed from search when fields are archived and cannot reappear after asset edits.
- Release ZIP tests verify production dependencies, absence of legacy scripts/local secrets/dev test tools, and private directory permissions.
- All application/test PHP files pass syntax validation; Apache configuration syntax passes.
- Actual Apache worker execution is blocked by local sandbox privilege restrictions. Chromium is unavailable: its download is invalid and the available executable fails even with `--version`. No visual QA success is claimed.
- MySQL 8.0 and PHP 8.4/8.5 are not verified locally. The workflow includes those matrix checks, an actual packaged Apache installation, and desktop/mobile browser acceptance. It has not run.
- An independent code reviewer completed the branch review. Two Important findings (MFA confirmation concurrency and archived-field search disclosure) were fixed in one regression-tested pass. ZIP private directory permissions were also tightened.

## Publishing status

No GitHub changes were published. Git push cannot obtain credentials. The selected GitHub connector reads the repository but rejects writing a Git tree with HTTP403, `Resource not accessible by integration`. No draft PR or CI run exists for this rebuild.

The delivery archive includes the install ZIP and a Git bundle preserving branch history, tests, CI workflow and build tooling. From an authenticated development workstation:

```bash
git clone -b rebuild/core SimpleMSPDoc-rebuild.bundle SimpleMSPDoc-rebuild
cd SimpleMSPDoc-rebuild
git remote rename origin bundle
git remote add origin https://github.com/flattery103/SimpleMSPDoc.git
git push -u origin rebuild/core
gh pr create --draft --base main --head rebuild/core --title "Rebuild SimpleMSPDoc as fresh-install LAMP documentation application" --body-file docs/pr-description.md
```

Do not merge or publish a stable release until the remaining checks pass. The alpha install ZIP is intended for a separate test LAMP instance.

## Decisions made during implementation

- Work on an isolated `rebuild/core` branch. Cost if this choice is unsuitable: branch cleanup.
- Client classification implies read-only independently of organization assignments. Cost if unsuitable: permission-model changes.
- Use a workspace-local extracted runtime because system package ownership changes are prohibited here. Cost: this environment does not prove the deployed Apache configuration.
- Use shared organization/type/visibility record headers with validated payloads. Cost: high-volume future integrations may need specialized tables.
- Name the connector cursor column `sync_cursor` to avoid a reserved SQL word. Cost: future connector mappings must use that name.
- Existing assets cannot change templates. Cost: users create replacement assets when moving between template types, avoiding silent field loss.
- Client users receive current article content; historical revision pages are staff-only. Cost: clients cannot inspect article history, protecting formerly internal drafts.
- Group web controllers/templates into final integration work so their dependent services can be tested through real forms. Cost: a larger web-interface commit to review.
- Move unavailable local Apache/browser checks to the prepared CI workflow. Cost: deployment/visual defects remain possible until it runs.
- Package the unpublished work with branch history instead of claiming a successful push/PR. Cost: an authenticated workstation must publish the branch and run CI.

## Deferred minor review findings

1. ArticleService's historical read methods rely on the HTTP controller for the staff-only policy. No externally reachable bypass was found; keep future APIs from exposing these methods without the same check.
2. Restoring a revision produces an update audit event rather than a distinct restoration event containing its source revision.
3. A direct malformed service call with a non-array asset values payload raises TypeError instead of422. Ordinary web forms construct an array.
