# animated-Wings

This repository contains the historical `CustomWing` plugin, which offers wing skins through a FormAPI menu. The manifest credits Yukari, and the preserved Git history includes work by LyrexGit.

## Compatibility and dependencies

`plugin.yml` declares PocketMine-MP API `4.8.1`. The source uses `LoginPacket` client skin data and imports FormAPI but does not declare that dependency in the manifest. It has not been ported or tested on Axolotl-PM 5. Do not deploy this source on a current server.

## Data and asset safety

The plugin stores skin images under its data folder and can delete them after a player quits when `DeleteSkinAfterQuitting` is enabled. The legacy code builds a save path from a client supplied name without an evident path validation step. A port needs a validated storage path and tests for player data retention before it can be used with real players.

The bundled wing images and geometry files have no provenance or redistribution license recorded in this repository. Preserve the existing assets and credits while rights are investigated. There is no LICENSE file, DevTools PHAR, Axolotl-PM 5 runtime test or verified current release. No compatibility or distribution permission is claimed by this README.

For questions about the NhanAZ ecosystem, use [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
