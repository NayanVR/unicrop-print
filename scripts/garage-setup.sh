#!/bin/sh
# One-time setup: assigns this Garage node to the cluster layout and
# creates the bucket + access key used by the app. Run once after
# `docker compose up -d garage`:
#
#   docker compose exec garage /scripts/garage-setup.sh
#
# Copy the printed Key ID / Secret into .env as AWS_ACCESS_KEY_ID /
# AWS_SECRET_ACCESS_KEY, then restart the app container.
set -e

BUCKET="${AWS_BUCKET:-unicrop-print}"
KEY_NAME="unicrop-print-app"

NODE_ID=$(garage status | awk '/NO ROLE ASSIGNED/{print $1; exit}')
if [ -z "$NODE_ID" ]; then
    echo "No unassigned node found. Layout may already be applied:"
    garage status
else
    garage layout assign -z dc1 -c 1G "$NODE_ID"
    garage layout apply --version 1
fi

garage bucket create "$BUCKET" 2>/dev/null || true

if ! garage key info "$KEY_NAME" >/dev/null 2>&1; then
    garage key create "$KEY_NAME"
fi

garage bucket allow --read --write --owner "$BUCKET" --key "$KEY_NAME"

echo
echo "=== Garage credentials (copy into .env) ==="
garage key info "$KEY_NAME" --show-secret
