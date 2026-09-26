# Festivly plugin for Falcon Player

Connects your [Falcon Player (FPP)](https://github.com/FalconChristmas/fpp) show to a [festivly.app](https://festivly.app) listing: live on/off, current song, time left, what’s next, and nightly hours from the FPP schedule. Visitors can request the next song from your Festivly page while the show is playing.

Settings live under **Status/Control → Festivly** in the FPP UI.

**Install from:** this repository (`Smith-Happens/fpp-festivly`). The copy under the festivly app repo’s `fpp-plugin/` folder is for development sync only — Plugin Manager does not install from the app monorepo.

## Install (Plugin Manager)

1. In the FPP web UI, open **Content Setup → Plugin Manager** (wording may vary slightly by FPP version).
2. Find **Festivly** → **Install**.
3. Restart FPPD if prompted.
4. Open **Status/Control → Festivly**.
5. On [festivly.app](https://festivly.app): **Dashboard →** your display → **Edit → Integrations** → **Enable FPP Integration**. Copy **Display ID** and **Token**.
6. Paste them in the Festivly plugin page, leave **Sync nightly hours…** checked unless you want manual hours, then **Save** → **Send test update**.

### Test before the official listing appears

If Festivly is not in the shared plugin list yet, open Plugin Manager and use **Retrieve Plugin Info** with:

```text
https://raw.githubusercontent.com/Smith-Happens/fpp-festivly/refs/heads/main/pluginInfo.json
```

Then install from that card the same way.

### Fallback (SSH) — only if Plugin Manager is unavailable

```bash
cd /home/fpp/media/plugins
git clone --depth 1 https://github.com/Smith-Happens/fpp-festivly.git fpp-festivly
chmod +x fpp-festivly/callbacks
```

Restart FPPD, then finish steps 4–6 above.

(Older sparse-checkout of the festivly *app* repo still works for existing players; new installs should use this plugin repo.)

## Updating

Plugin Manager → **Festivly** → **Update**, or:

```bash
cd /home/fpp/media/plugins/fpp-festivly && git pull
```

Restart FPPD if prompted.

## What the plugin does

- Publishes playlist songs to the festivly.app listing.
- While a sequence is playing, about five seconds before it ends, inserts the oldest visitor request with **Insert Playlist After Current** (does not cut the current song).
- Schedule sync overwrites nightly on/off times and days on Festivly. Season start/end dates stay on Festivly.
- The signing token stays in `/home/fpp/media/config/plugin.fpp-festivly.json` and is never sent.

Owner walkthrough: [festivly.app/help/display-setup](https://festivly.app/help/display-setup#fpp-falcon-player-live-onoff-status).

## Developer sync (festivly app → this repo)

Edits often land first in the festivly monorepo under `fpp-plugin/`. Publish them here with:

```bash
# from a checkout of Smith-Happens/festivly
./scripts/publish-fpp-plugin.sh /path/to/fpp-festivly
```

See that script’s header for options. Plugin Manager’s source of truth is **this** repository’s `main` branch.
