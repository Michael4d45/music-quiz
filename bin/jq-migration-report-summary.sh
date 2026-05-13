#!/usr/bin/env bash
# Summarize reports/migration_resource_report.json without dumping the full file.
# Usage: ./bin/jq-migration-report-summary.sh [path-to-report.json]
set -euo pipefail
REPORT="${1:-reports/migration_resource_report.json}"
if [[ ! -f "$REPORT" ]]; then
  echo "Report not found: $REPORT" >&2
  exit 1
fi

echo "=== Non-empty drift sections (should be {} when clean) ==="
jq '.report | to_entries | map(select(.value | length > 0)) | from_entries' "$REPORT"

echo ""
echo "=== App-oriented resource keys (edit grep as needed) ==="
jq -r '.resources | keys[]' "$REPORT" | grep -E '^(game_sessions|session_|playlist|quiz_|music_|player_|scoring_|answer_|multiple_|source_api_credentials|users|track_|categories|sub_categories)$' || true

echo ""
echo "=== game_sessions: migration vs model field keys ==="
jq '.resources.game_sessions | {migration: (.migration_fields | keys | sort), model: (.model_fields | keys | sort)}' "$REPORT"
