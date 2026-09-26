#!/bin/bash
set -e

# Festivly FPP plugin install. Idempotent. Runs as root.
: "${FPPDIR:=/opt/fpp}"
. "${FPPDIR}/scripts/common" 2>/dev/null || true

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
chmod +x "${PLUGIN_DIR}/callbacks" 2>/dev/null || true

# Playlist/media callbacks are picked up after fppd restarts.
setSetting restartFlag 1 2>/dev/null || true
