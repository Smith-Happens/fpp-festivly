#!/bin/bash
# Festivly FPP Plugin uninstall script
# Removes the cron entry added by this plugin

MARKER="fpp-festivly-poll"

CURRENT=$(crontab -l 2>/dev/null)

if [ -z "$CURRENT" ]; then
    exit 0
fi

FILTERED=$(echo "$CURRENT" | grep -v "$MARKER")

if [ -z "$FILTERED" ]; then
    crontab -r 2>/dev/null
else
    echo "$FILTERED" | crontab -
fi

exit 0
