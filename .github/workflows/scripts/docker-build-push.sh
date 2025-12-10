#!/usr/bin/env bash
set -euo pipefail

# Arguments
UNIT_NAME="$1"
REPO_OWNER="$2"

# Sanitize the unit name by replacing spaces with dashes, removing consecutive dashes, and converting to lowercase
sanitized_unit=$(echo "$UNIT_NAME" | tr ' ' '-' | sed 's/--*/-/g' | tr '[:upper:]' '[:lower:]')
image_name="ghcr.io/${REPO_OWNER}/${sanitized_unit}"

# Get the latest version tag from the container registry
# Fetch all tags and filter for semver format (vX.Y.Z or X.Y.Z)
latest_version=$(gh api \
  -H "Accept: application/vnd.github+json" \
  "/users/${REPO_OWNER}/packages/container/${sanitized_unit}/versions" \
  --paginate 2>/dev/null | \
  jq -r '.[].metadata.container.tags[]' 2>/dev/null | \
  grep -E '^v?[0-9]+\.[0-9]+\.[0-9]+$' | \
  sed 's/^v//' | \
  sort -t. -k1,1n -k2,2n -k3,3n | \
  tail -1) || true

# If no version found, start at 0.0.0
if [ -z "$latest_version" ]; then
  latest_version="0.0.0"
fi

# Increment patch version (X.Y.Z -> X.Y.Z+1)
IFS='.' read -r major minor patch <<< "$latest_version"
new_patch=$((patch + 1))
new_version="${major}.${minor}.${new_patch}"

echo "Previous version: $latest_version"
echo "New version: $new_version"

# Find the Dockerfile (handles both 'Dockerfile' and 'dockerfile')
dockerfile_name=$(ls -1 Dockerfile dockerfile 2>/dev/null | head -1)

# Build the Docker image with both version tag and latest tag
docker build --progress=plain \
  -t "${image_name}:${new_version}" \
  -t "${image_name}:latest" \
  -f "$dockerfile_name" . \
  --label "org.opencontainers.image.source=https://github.com/${REPO_OWNER}/hatshop" \
  --label "org.opencontainers.image.description=hatshop ${UNIT_NAME}" \
  --label "org.opencontainers.image.licenses=Freeware" \
  --label "org.opencontainers.image.version=${new_version}"

# Push both tags
docker push "${image_name}:${new_version}"
docker push "${image_name}:latest"
