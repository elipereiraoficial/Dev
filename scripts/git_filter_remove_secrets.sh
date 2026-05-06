#!/usr/bin/env bash
set -euo pipefail

# git_filter_remove_secrets.sh
# Usage:
#   ./scripts/git_filter_remove_secrets.sh [secret1] [secret2] ...
# Or set SECRETS environment variable (comma-separated) and run without args.
# IMPORTANT: Rotate any live credentials before running this script. This script WILL rewrite git history and FORCE PUSH.
# Make sure you have a full backup and that all collaborators are informed.

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

if ! command -v git-filter-repo >/dev/null 2>&1; then
  echo "git-filter-repo is not installed. Install it first:"
  echo "  pip3 install git-filter-repo"
  echo "  or see https://github.com/newren/git-filter-repo"
  exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree is not clean. Commit or stash changes before proceeding." >&2
  git status --porcelain
  exit 1
fi

read -p "This will rewrite git history and force-push to origin/master. Are you sure? (type 'YES' to continue): " confirm
if [ "$confirm" != "YES" ]; then
  echo "Aborted by user.";
  exit 1
fi

BACKUP_BUNDLE="${REPO_ROOT}/backup-before-filter-$(date +%Y%m%d%H%M%S).bundle"
echo "Creating repository bundle backup: $BACKUP_BUNDLE"
git bundle create "$BACKUP_BUNDLE" --all

# Prepare replace-text file for git-filter-repo
REPLACE_FILE="${REPO_ROOT}/.gitfilter-replace.txt"
rm -f "$REPLACE_FILE"

if [ "$#" -gt 0 ]; then
  SECRETS=("$@")
elif [ -n "${SECRETS:-}" ]; then
  IFS=',' read -r -a SECRETS <<< "$SECRETS"
else
  # Default list - adjust if needed
  SECRETS=("Cadu5540!!" "admin123" "123456")
fi

echo "Building replace file with ${#SECRETS[@]} secrets..."
for s in "${SECRETS[@]}"; do
  # Trim
  secret="$(echo "$s" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"
  if [ -z "$secret" ]; then
    continue
  fi
  echo "# secret: ${secret}" >> "$REPLACE_FILE"
  # Write literal block: one line with secret, next line '==>REDACTED'
  printf '%s
done

echo "Replace file created at: $REPLACE_FILE"

echo "Running git-filter-repo (this may take a while)..."
git filter-repo --force --replace-text "$REPLACE_FILE"

echo "Cleaning up refs and GC..."
git reflog expire --expire=now --all || true
git gc --prune=now --aggressive || true

echo "Force-pushing rewritten history to origin (all branches and tags)..."
git push origin --force --all
git push origin --force --tags

echo "Done. Notify collaborators: they must re-clone the repository or run commands to realign their local clones." 
echo "Recommended collaborator instructions:"
cat <<'EOF'

echo "Script finished. Keep a copy of the backup bundle stored at: $BACKUP_BUNDLE"
