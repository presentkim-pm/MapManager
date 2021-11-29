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

use Closure;
use Exception;
use JetBrains\PhpStorm\Pure;
use pocketmine\color\Color;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class MapManager implements Listener{
    use SingletonTrait;

    /**
     * @var array<int, MapImage|Closure>
     * - Closure(int $mapId, Player|null $player) : MapImage>
     */
    private array $mapImages = [];

    private MapImage $emptyMapImage;

    public function __construct(){
        $this->emptyMapImage = new MapImage([[new Color(0, 0, 0, 0)]]);
    }

    #[Pure]
    public function getEmptyMapImage() : MapImage{
        return $this->emptyMapImage;
    }

    public function getRandomUuid() : int{
        while(true){
            try{
                $uuid = random_int(0, PHP_INT_MAX);
                if(!$this->isRegisteredMap($uuid)){
                    return $uuid;
                }
            }catch(Exception){
            }
        }
    }

    public function registerMapImage(int $mapUuid, MapImage|Closure $mapImage, bool $force = false) : bool{
        if(!$force && isset($this->mapImages[$mapUuid])){
            return false;
        }

        $this->mapImages[$mapUuid] = $mapImage;
        return true;
    }

    public function unregisterMapImage(int $mapUuid) : bool{
        if($this->isRegisteredMap($mapUuid)){
            unset($this->mapImages[$mapUuid]);
            return true;
        }
        return false;
    }

    public function isRegisteredMap(int $mapUuid) : bool{
        return isset($this->mapImages[$mapUuid]);
    }

    public function getMapImage(int $mapUuid, Player|null $player = null) : MapImage{
        $mapImage = $this->mapImages[$mapUuid] ?? $this->emptyMapImage;
        if($mapImage instanceof MapImage){
            return $mapImage;
        }

        return $mapImage($mapUuid, $player);
    }

    public function onDataPacketReceived(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if($packet instanceof MapInfoRequestPacket){
            $session = $event->getOrigin();

            $pk = new ClientboundMapItemDataPacket();
            $pk->mapId = $packet->mapId;
            $pk->colors = $this->getMapImage($packet->mapId, $session->getPlayer());
            $pk->type = ClientboundMapItemDataPacket::BITFLAG_MAP_CREATION;
            $pk->scale = 1;
            $session->sendDataPacket($pk);
        }
    }
}