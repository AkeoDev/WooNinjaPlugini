#!/usr/bin/env bash
#
# Builds upload-ready plugin ZIPs from the current working tree into dist/.
#
# Only the four plugins klet-brda actually runs are built. Dev-only files
# (JS sources, webpack config, test harnesses) are stripped so nothing
# unnecessary is served from wp-content/plugins.
#
# Usage: ./build-zips.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

PLUGINS=(
	wooninja-kreditnekartice
	wooninja-kreditnekartice-diners
	wooninja-nestpay
	wooninja-lowestprice
)

# Syntax-check first: a parse error in a payment return page is invisible
# until a customer hits it, so never build over one.
if command -v php >/dev/null 2>&1; then
	echo "==> Linting"
	fail=0
	for p in "${PLUGINS[@]}"; do
		while IFS= read -r f; do
			php -l "$f" >/dev/null 2>&1 || { echo "  PARSE ERROR: $f"; fail=1; }
		done < <(find "$p" -name '*.php')
	done
	if [ "$fail" -ne 0 ]; then
		echo "Aborting: fix the parse errors above." >&2
		exit 1
	fi
	echo "  all PHP files parse"
else
	echo "==> php not found, skipping lint" >&2
fi

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

rm -rf dist
mkdir -p dist

echo "==> Building"
for p in "${PLUGINS[@]}"; do
	rsync -a \
		--exclude '.git' \
		--exclude 'node_modules' \
		--exclude 'src' \
		--exclude 'test' \
		--exclude 'package.json' \
		--exclude 'package-lock.json' \
		--exclude 'webpack.config.js' \
		--exclude '.DS_Store' \
		"$p" "$STAGE/"
	( cd "$STAGE" && zip -rq "$ROOT/dist/$p.zip" "$p" )
	printf '  %-40s %s\n' "$p.zip" "$(du -h "dist/$p.zip" | cut -f1)"
done

echo "==> Done. Upload dist/*.zip via Plugins -> Add New -> Upload Plugin."
