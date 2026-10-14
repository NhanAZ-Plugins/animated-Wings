# animated-Wings

This repository contains the historical `CustomWing` plugin, which offers wing skins through a FormAPI menu. The manifest credits Yukari, and the preserved Git history includes asset uploads by LyrexGit.

## Compatibility and dependencies

`plugin.yml` declares PocketMine-MP API `4.8.1` and requires FormAPI. The source uses the API 4 login packet/client skin classes. It has not been ported or tested on Axolotl-PM 5. Do not deploy this source on a current Axolotl-PM server.

## Player skin data

The plugin saves a copy of a player's login skin so it can apply and reset wing overlays. New player skin files use a SHA-256-derived filename rather than a client-provided name. The new-install default `DeleteSkinAfterQuitting: true` removes that saved skin when the player leaves; an existing server's explicit configuration is preserved. Invalid or unsupported skin payloads are rejected before image conversion. Temporary composite images use unique files and are removed after use.

These files still contain player appearance data while present. Protect the plugin data directory and review the retention setting for the server. Historical saves created by older versions with raw player-name filenames are not renamed or deleted automatically.

## Bundled asset status

The bundled wing PNG and geometry assets are preserved unchanged. The repository has no LICENSE or documented redistribution grant for those assets; the manifest credit and Git history do not establish reuse rights. The repository has no GitHub Actions workflow. Poggit-CI automatic builds are disabled while asset rights are unverified, so this update does not publish a PHAR. No stable release or verified Axolotl-PM 5 runtime test exists.

For questions about the NhanAZ ecosystem, use [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
