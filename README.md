# animated-Wings

`animated-Wings` is the historical `CustomWing` plugin. It offers bundled wing skins through an in-game menu and is maintained by NhanAZ.

## Compatibility

- Minimum Axolotl-PM API: `5.0.0`.
- The plugin uses Axolotl-PM's built-in form API and does not require FormAPI.
- The pinned CI matrix checks API 5.0.0 and 5.49.1 source revisions. This does not establish compatibility with every API 5.x release.
- The plugin requires PHP's GD extension to process player skin images.

## Player skin data

The plugin saves a copy of a player's login skin so it can apply and reset wing overlays. New player skin files use a SHA-256-derived filename rather than a client-provided name. The new-install default `DeleteSkinAfterQuitting: true` removes that saved skin when the player leaves; an existing server's explicit configuration is preserved. Invalid or unsupported skin payloads are rejected before image conversion. Temporary composite images use unique files and are removed after use.

These files still contain player appearance data while present. Protect the plugin data directory and review the retention setting for the server. Historical saves created by older versions with raw player-name filenames are not renamed or deleted automatically.

## Bundled asset status

The bundled wing PNG and geometry assets are authored by NhanAZ and distributed under the MIT License. The GitHub Actions workflow checks PHPStan, API 5 PHAR builds, artifact contents and plugin startup.

For questions about the NhanAZ ecosystem, use [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
