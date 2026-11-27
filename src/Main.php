<?php

/**            __   _____
 *  _ __ ___ / _| |_   _|__  __ _ _ __ ___
 * | '__/ _ \ |_    | |/ _ \/ _` | '_ ` _ \
 * | | |  __/  _|   | |  __/ (_| | | | | | |
 * |_|  \___|_|     |_|\___|\__,_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  ref-team
 * @link    https://github.com/refteams
 *
 *  &   ／l、
 *    （ﾟ､ ｡ ７
 *   　\、ﾞ ~ヽ   *
 *   　じしf_, )ノ
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace ref\api\mapmanager;

use pocketmine\color\Color;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\plugin\PluginBase;
use ref\api\mapmanager\item\FilledMap;
use ref\api\mapmanager\map\StaticMap;
use ref\api\mapmanager\utils\MapImageUtils;

final class Main extends PluginBase implements Listener{
    private MapManager $mapManager;

    protected function onEnable() : void{
        $this->mapManager = MapManager::getInstance();

        FilledMap::register();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /** @priority LOWEST */
    public function onDataPacketReceived(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if($packet instanceof MapInfoRequestPacket){
            // Answer with the requested map id, and never register the session as a listener of the shared fallback map
            $map = $this->mapManager->getOrNull($packet->mapId)
                ?? new StaticMap($packet->mapId, MapImageUtils::fromOneColor(new Color(0, 0, 0, 0), 1, 1));
            $map->sendImage($event->getOrigin());
        }
    }

    /** @priority LOWEST */
    public function onPlayerQuitEvent(PlayerQuitEvent $event) : void{
        foreach($this->mapManager->getAllMap() as $map){
            $map->removeListener($event->getPlayer()->getNetworkSession());
        }
    }
}