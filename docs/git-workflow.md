# Git directly from the dev website

The repository uses the installed layout: `index.php`, `app/`, `assets/`, `data/`, and `docs/`. Tests and scripts stay in separate development folders and do not go into downloadable application releases.

Your existing installed `app/vendor/` continues working. Composer and test tools are not required to commit code or push it to GitHub; GitHub Actions runs the checks. A fresh clone needs production libraries installed once with `composer --working-dir=app install --no-dev`, or copied from the release ZIP.

## One-time transition from an installed ZIP

Install alpha.7 first so the actual site contains all unpublished application changes. Back up the site first. Extract SimpleMSPDoc-git-tools.zip over the installed dev website. It adds the missing developer files and updates the packaging helper and Composer metadata. It does not replace normal application code, overwrite configuration, or include any data files. Review local edits to the packaging helper or Composer files before extraction.

The included scripts/connect-dev-git.sh performs the initialization below with checks for existing Git metadata, the installed app version and private staged paths. Git metadata stays outside the web root:

```bash
cd /www/SimpleMSPDoc
git init --initial-branch=main --separate-git-dir=/root/SimpleMSPDoc-dev.git
git remote add origin https://github.com/flattery103/SimpleMSPDoc.git
git fetch origin main
git reset --mixed origin/main
git switch -c updates/alpha7-installed-layout
git add -A
git diff --cached --stat
git status --short
```

`reset --mixed` aligns the index with GitHub without overwriting website files. The resulting commit includes your actual installed application code, all unpublished updates present on the site, and the aligned folder layout. Do not use `--hard` here.

The ignore rules exclude app/vendor, configuration and all private data contents, logs, dependency caches, database dumps, bundles and release ZIPs. Only data/index.php and data/.htaccess guard files may be tracked from data/. Check the staged paths before committing. Keep the original data/config.php and its encryption key.

After reviewing the staged changes:

```bash
git -c user.name="Rob Campbell" -c user.email="flattery103@users.noreply.github.com" commit -m "Include Action1 updates and align repository with installed website"
git push -u origin updates/alpha7-installed-layout
gh pr create --base main --head updates/alpha7-installed-layout --draft --title "Include unpublished updates and use installed website layout" --body "Imports the installed dev site and development tools using the same layout as release ZIPs. Includes the unpublished Action1 and configuration updates present on the dev site. Private data and installed vendor dependencies are excluded. GitHub CI validation is pending."
```

## Normal future work

After the first PR is merged, stay on the updates branch while fetching the new main. Advance the local bootstrap main before switching, so the old pre-transition layout is never checked out over the website. This one-time main branch has no local commits of its own:

```bash
cd /www/SimpleMSPDoc
git fetch origin main
git branch -f main origin/main
git switch main
```

For later changes, use the normal workflow below. No source bundle is needed.

```bash
cd /www/SimpleMSPDoc
git switch main
git pull --ff-only
git switch -c updates/describe-the-change
# Edit and check the website, then stage the files you changed.
git add app/src app/templates assets
git diff --cached --stat
git commit -m "Describe the change"
git push -u origin HEAD
```

Do not run the database test suite against the website's database. It uses disposable databases. If an update changes the Composer lock file, refresh app/vendor with Composer or deploy the corresponding release ZIP. Website content in the database, passwords, API credentials and uploads are backed up separately and never committed.
