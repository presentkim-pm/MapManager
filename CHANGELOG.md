# Changelog

## 2.0.0 (2026-11-27)

The version for the old [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) API 4 is kept on the
[`legacy/pmmp-5.44`](https://github.com/presentkim-pm/mapmanager/tree/legacy/pmmp-5.44) branch.

### ⚠️ Breaking changes
- Targets [axolotl-pm/PocketMine-MP](https://github.com/axolotl-pm/PocketMine-MP) `5.51` (BedrockProtocol 64,
  Bedrock `1.26.60`) and **PHP 8.4+**.
- Distributed as a phar from GitHub Releases instead of Poggit.
- The filled map item has no numeric id any more: it is registered with an item type id allocated by
  `ItemTypeIds::newId()`, which is exposed as `FilledMap::typeId()`, and with the item serializer / deserializer for
  `minecraft:filled_map` and the `filled_map` / `map` string aliases. `FilledMap::register()` does that (called by the plugin).
- The source files moved from `src/ref/api/mapmanager/` to `src/` (`src-namespace-prefix` in `plugin.yml`); the namespaces are unchanged.
- `Map::setListeners()` takes any list of sessions, `Map::broadcastMarkers()` / `broadcastImage()` take `?array`.
- `StaticMap::setImage()` accepts only a `MapImage` (a `Closure` was accepted by the signature but could not work).

### ✨ Features
- PHPUnit tests that send maps through a stub network session and encode / decode every packet with the real
  BedrockProtocol serializer.

### 🔄 Changes
- `ClientboundMapItemDataPacket` is built with the BedrockProtocol 64 fields (`origin` is always set).
- `MapMarker` and `Map` use constructor promotion; `composer.json` added (Composer is only used for development and
  testing).
- Map requests for an unregistered id are answered with an empty map of the requested id.

### 🐛 Fixes
- Marker offsets / `sendDeco()` offsets below `0` were encoded as negative numbers, which the protocol's unsigned byte
  cannot hold; they are now written in two's complement as the client expects. Marker rotation is wrapped into `0..15`.
- Sending markers when the map has none now clears the markers of the client (the lists were omitted).
- `MapManager::register($map, true)` could register the same listener twice, because listeners were copied with the
  wrong keys.
- `StaticMap::updateImage()` rejected images that exactly fit the map (off by one).
- `FilledMap::isDisplayPlayers()` threw when the tag was missing.
- The error message of `FilledMap::setNameIndex()` named the wrong field.
- `MapManager::nextId()` used the deprecated `lcg_value()` and could produce an out-of-range integer.
- Every unregistered map request used to subscribe the player to the shared fallback map, which was never cleaned up.
- `MapImageUtils::fromPixels()` failed with a `TypeError` on empty input instead of `InvalidArgumentException`.
