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
use JetBrains\PhpStorm\Pure;
use pocketmine\color\Color;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

use function lcg_value;
use function spl_object_hash;
use function spl_object_id;

final class MapManager{
    use SingletonTrait;

    /**
     * @var array<int, MapImage|Closure>
     * - Closure(int $mapId, Player|null $player) : MapImage>
     */
    private array $mapImages = [];

    /** @var array<int, array<string, NetworkSession>> */
    private array $mapListeners = [];

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
            $uuid = (int) (lcg_value() * PHP_INT_MAX);
            if(!$this->hasMapImage($uuid)){
                return $uuid;
            }
        }
    }

    public function setMapImage(int $mapUuid, MapImage|Closure $mapImage) : void{
        if(isset($this->mapImages[$mapUuid])){
            $this->mapImages[$mapUuid] = $mapImage;
            foreach($this->mapListeners[$mapUuid] as $session){
                $this->sendMapImage($mapUuid, $session);
            }
        }else{
            $this->mapImages[$mapUuid] = $mapImage;
            $this->mapListeners[$mapUuid] = [];
        }
    }

    public function removeMapImage(int $mapUuid) : void{
        unset($this->mapImages[$mapUuid], $this->mapListeners[$mapUuid]);
    }

    public function hasMapImage(int $mapUuid) : bool{
        return isset($this->mapImages[$mapUuid]);
    }

    public function getMapImage(int $mapUuid, Player|null $player = null) : MapImage{
        $mapImage = $this->mapImages[$mapUuid] ?? $this->emptyMapImage;
        if($mapImage instanceof MapImage){
            return $mapImage;
        }

        return $mapImage($mapUuid, $player);
    }

    /** @internal */
    public function addMapListener(int $mapUuid, NetworkSession $session) : void{
        $this->mapListeners[$mapUuid][spl_object_id($session)] = $session;
    }

    /** @internal */
    public function removeMapListener(NetworkSession $session) : void{
        $hash = spl_object_hash($session);
        foreach($this->mapListeners as &$sessions){
            if(isset($sessions[$hash])){
                unset($sessions[$hash]);
            }
        }
    }

    public function sendMapImage(int $mapUuid, NetworkSession $session) : void{
        $pk = new ClientboundMapItemDataPacket();
        $pk->mapId = $mapUuid;
        $pk->colors = $this->getMapImage($mapUuid, $session->getPlayer());
        $pk->scale = 1; // TODO: Implement scaling
        $session->sendDataPacket($pk);

        $this->addMapListener($mapUuid, $session);
    }
}