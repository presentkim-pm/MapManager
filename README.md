<!-- PROJECT BADGES -->
<div align="center">

![Version][version-badge]
[![Stars][stars-badge]][stars-url]
[![License][license-badge]][license-url]

</div>


<!-- PROJECT LOGO -->
<br />
<div align="center">
  <h3>mapmanager (refMapManager)</h3>
  <p align="center">
    A simple map data manager for Minecraft Bedrock (axolotl-pm/PocketMine-MP), which adds the filled map item.

[Contact to me][author-discord] · [Report a bug][issues-url] · [Request a feature][issues-url]

  </p>
</div>


<!-- ABOUT THE PROJECT -->

### Overview

- **What it does**
  - Adds the `minecraft:filled_map` item, which PocketMine-MP does not provide, so players can hold and see maps.
  - Keeps a registry of maps by id and answers the client's `MapInfoRequestPacket` with the registered image.
  - Sends `ClientboundMapItemDataPacket` for images, partial image updates and markers (decorations).
- **Key concepts**
  - `MapManager` – singleton registry of maps (`register()`, `registerFrom()`, `unregister()`, `get()`, `getOrNull()`, `nextId()`).
  - `StaticMap` – a map with a fixed image, which can be replaced or partially updated and is pushed to its listeners.
  - `DynamicMap` – a map whose image is produced by a closure, per viewing `NetworkSession`.
  - `MapMarker` – a decoration on the map (player arrow, X, house, ...).
  - `FilledMap` – the item. `FilledMap::create($mapId)` makes an item that shows the map with that id.
  - `MapImageUtils` – helpers to build `MapImage` objects (one color, nearest-neighbor enlargement).

---

### Installation & Requirements

- **Server**
  - [axolotl-pm/PocketMine-MP](https://github.com/axolotl-pm/PocketMine-MP) `5.51` or higher (`plugin.yml` `api: [5.51.0]`),
    which uses BedrockProtocol 64 (Bedrock `1.26.60`).
  - PHP `8.4` or higher.
- **Installation**
  - Download `refMapManager.phar` from [GitHub Releases][releases-url] and place it into your server's `plugins` directory.
  - Restart the server; the plugin will load automatically according to `plugin.yml`.
- **Old versions**
  - The version for the old PocketMine-MP API 4 (`1.0.0`) is kept on the
    [`legacy/pmmp-5.44`](https://github.com/presentkim-pm/mapmanager/tree/legacy/pmmp-5.44) branch.
- **Development**
  - `composer install` then `composer test` runs the PHPUnit tests, which encode the map packets the plugin sends with
    the real BedrockProtocol serializer.

---

### Quick Start

```php
use pocketmine\color\Color;
use pocketmine\player\Player;
use ref\api\mapmanager\item\FilledMap;
use ref\api\mapmanager\map\MapMarker;
use ref\api\mapmanager\MapManager;
use ref\api\mapmanager\utils\MapImageUtils;

function giveMap(Player $player) : void{
    $manager = MapManager::getInstance();

    // Register a red 128x128 map under a free id
    $id = MapManager::nextId();
    $manager->registerFrom($id, MapImageUtils::fromOneColor(new Color(100, 0, 0), 128, 128));

    // Put an "X" at the middle of the map
    $manager->get($id)->addMarker(new MapMarker(MapMarker::ICON_X, 64, 64));

    // Give the map item to the player
    $player->getInventory()->addItem(FilledMap::create($id));
}
```

- Maps are sent to a player when its client requests them, so register the map before giving the item.
- `StaticMap::setImage()` / `updateImage()` and `Map::setMarkers()` / `addMarker()` push changes to every player that has
  requested the map.
- Image offsets and sizes follow the Bedrock limit of `128x128` pixels.

---

### License

Distributed under the **LGPL-3.0-or-later**. See [`LICENSE`][license-url] for more information.


<!-- MARKDOWN LINKS & IMAGES -->

[author-discord]: https://discordapp.com/users/345772340279508993

[version-badge]: https://img.shields.io/github/v/release/presentkim-pm/mapmanager?display_name=tag&style=for-the-badge&label=VERSION

[stars-badge]: https://img.shields.io/github/stars/presentkim-pm/mapmanager.svg?style=for-the-badge

[license-badge]: https://img.shields.io/github/license/presentkim-pm/mapmanager.svg?style=for-the-badge

[stars-url]: https://github.com/presentkim-pm/mapmanager/stargazers

[releases-url]: https://github.com/presentkim-pm/mapmanager/releases

[issues-url]: https://github.com/presentkim-pm/mapmanager/issues

[license-url]: https://github.com/presentkim-pm/mapmanager/blob/main/LICENSE
