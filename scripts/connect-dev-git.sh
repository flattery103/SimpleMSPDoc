#!/usr/bin/env bash
set -euo pipefail
site_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
metadata_dir="${SIMPLEMSPDOC_GIT_DIR:-/root/SimpleMSPDoc-dev.git}"
cd "$site_dir"
if [[ -e .git || -e "$metadata_dir" ]]; then
  printf 'Git is already initialized here, or %s exists. Stop and inspect before continuing.\n' "$metadata_dir" >&2
  exit 1
fi
php -r '$m=json_decode(file_get_contents("manifest.json"),true);if(($m["version"]??"")!=="2.0.0-alpha.7"){fwrite(STDERR,"Install alpha.7 first so GitHub receives all unpublished application changes.\n");exit(1);}'
git init --initial-branch=main --separate-git-dir="$metadata_dir" "$site_dir"
git remote add origin https://github.com/flattery103/SimpleMSPDoc.git
git fetch origin main
git reset --mixed origin/main
git switch -c updates/alpha7-installed-layout
git check-ignore -q data/config.php
git check-ignore -q app/vendor/autoload.php
git add -A
while IFS= read -r -d '' staged_path; do
  case "$staged_path" in
    data/index.php|data/.htaccess) ;;
    data/*|app/vendor/*|*.sql|*.sql.gz|*.pem|*.key|.env|.env.*)
      git restore --staged -- "$staged_path"
      printf 'Private or generated path was staged: %s. Stop and inspect ignore rules.\n' "$staged_path" >&2
      exit 1 ;;
  esac
done < <(git diff --cached --name-only --diff-filter=ACMR -z)
git diff --cached --stat
git status --short
printf '\nActual website files are staged. No commit or push has been made.\n'
