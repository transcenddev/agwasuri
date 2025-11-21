#!/usr/bin/env bash
# scripts/send-sample-water-quality.sh
# Requires: curl, jq

SERVER=${SERVER:-http://127.0.0.1:8000}
FILE=${1:-scripts/sample-water-quality.json}
API_KEY=${SENSOR_API_KEY:-}

if [ ! -f "$FILE" ]; then
  echo "Sample file not found: $FILE" >&2
  exit 1
fi

if [ -z "$API_KEY" ]; then
  read -p "API key: " API_KEY
fi

jq -c '.[]' "$FILE" | while read -r payload; do
  echo "Posting..."
  curl -sS -X POST "$SERVER/store-water-quality-data" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $API_KEY" \
    -d "$payload" | jq .
  sleep 0.3
done
