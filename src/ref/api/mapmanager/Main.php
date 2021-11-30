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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\plugin\PluginBase;
use ref\api\mapmanager\item\FilledMap;

final class Main extends PluginBase implements Listener{
    protected function onEnable() : void{
        $filledMap = new FilledMap(new ItemIdentifier(ItemIds::FILLED_MAP, 0), "Filled Map");
        $filledMap->setUuid(0); // Prevent map id set to -1, -1 will be broken client
        ItemFactory::getInstance()->register($filledMap, true);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDataPacketReceived(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if($packet instanceof MapInfoRequestPacket){
            MapManager::getInstance()->sendMapImage($packet->mapId, $event->getOrigin());
        }
    }

    public function onPlayerQuitEvent(PlayerQuitEvent $event) : void{
        MapManager::getInstance()->removeMapListener($event->getPlayer()->getNetworkSession());
    }
}