#!/bin/bash

# Undo side effects outside the plugin directory. FPP deletes the plugin
# folder after this script exits. Safe to run more than once.

: "${FPPDIR:=/opt/fpp}"
. "${FPPDIR}/scripts/common" 2>/dev/null || true
: "${MEDIADIR:=/home/fpp/media}"

MARKER='fpp-festivly-poll'

# Remove the minutely poll cron for the fpp user (added when settings are saved).
if command -v crontab >/dev/null 2>&1; then
  for CRON_USER in fpp root; do
    CURRENT="$(crontab -u "${CRON_USER}" -l 2>/dev/null || true)"
    if printf '%s\n' "${CURRENT}" | grep -q "${MARKER}"; then
      printf '%s\n' "${CURRENT}" | grep -v "${MARKER}" | crontab -u "${CRON_USER}" - 2>/dev/null || true
    fi
  done
  # Also clear the installing user's crontab if it somehow got the marker.
  CURRENT="$(crontab -l 2>/dev/null || true)"
  if printf '%s\n' "${CURRENT}" | grep -q "${MARKER}"; then
    printf '%s\n' "${CURRENT}" | grep -v "${MARKER}" | crontab - 2>/dev/null || true
  fi
fi

# Config + handoff lock are outside the plugin dir.
rm -f "${MEDIADIR}/config/plugin.fpp-festivly.json" 2>/dev/null || true
rm -f "${MEDIADIR}/tmp/festivly-fpp-handoff.lock" 2>/dev/null || true
rm -f /tmp/festivly-fpp-handoff.lock 2>/dev/null || true

setSetting restartFlag 1 2>/dev/null || true
