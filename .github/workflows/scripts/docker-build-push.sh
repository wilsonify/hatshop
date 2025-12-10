#!/usr/bin/env bash
set -euo pipefail

# Arguments
UNIT_NAME="$1"
REPO_OWNER="$2"
# Optional: GITHUB_SHA and GITHUB_REF from environment

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

# Generate additional tags
short_sha="${GITHUB_SHA:0:7}"
build_date=$(date -u +"%Y%m%d")
branch_name="${GITHUB_REF_NAME:-main}"
# Sanitize branch name for use as a tag
branch_tag=$(echo "$branch_name" | tr '/' '-' | tr '[:upper:]' '[:lower:]')

echo "Previous version: $latest_version"
echo "New version: $new_version"
echo "Short SHA: $short_sha"
echo "Build date: $build_date"
echo "Branch: $branch_tag"

# Find the Dockerfile (handles both 'Dockerfile' and 'dockerfile')
dockerfile_name=$(ls -1 Dockerfile dockerfile 2>/dev/null | head -1)

# Build tags array
# - Semantic version (e.g., 0.0.1)
# - Major.minor (e.g., 0.0) for easy minor version pinning
# - SHA-based (e.g., sha-abc1234) for exact commit reference
# - Date-based (e.g., 20251210) for time-based reference
# - Branch-based (e.g., main, feature-xyz) for branch tracking
# - latest (always points to most recent build)
tags=(
  "${image_name}:${new_version}"
  "${image_name}:${major}.${minor}"
  "${image_name}:sha-${short_sha}"
  "${image_name}:${build_date}"
  "${image_name}:${branch_tag}"
  "${image_name}:latest"
)

# Build tag arguments for docker build
tag_args=()
for tag in "${tags[@]}"; do
  tag_args+=("-t" "$tag")
done

# Build the Docker image with all tags
docker build --progress=plain \
  "${tag_args[@]}" \
  -f "$dockerfile_name" . \
  --label "org.opencontainers.image.source=https://github.com/${REPO_OWNER}/hatshop" \
  --label "org.opencontainers.image.description=hatshop ${UNIT_NAME}" \
  --label "org.opencontainers.image.licenses=Freeware" \
  --label "org.opencontainers.image.version=${new_version}" \
  --label "org.opencontainers.image.revision=${GITHUB_SHA:-unknown}" \
  --label "org.opencontainers.image.created=$(date -u +"%Y-%m-%dT%H:%M:%SZ")"

# Push all tags
for tag in "${tags[@]}"; do
  echo "Pushing $tag"
  docker push "$tag"
done
