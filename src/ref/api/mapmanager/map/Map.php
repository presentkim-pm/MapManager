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
 * @noinspection PhpDocSignatureIsNotCompleteInspection
 */

declare(strict_types=1);

namespace ref\api\mapmanager\map;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\types\MapImage;

use function array_values;
use function spl_object_id;

abstract class Map{
    private int $id;

    /** @var array<string, NetworkSession> */
    protected array $listeners = [];

    protected function __construct(int $id){
        $this->id = $id;
    }

    final public function getId() : int{
        return $this->id;
    }

    abstract public function getImage(NetworkSession|null $session = null) : MapImage;

    public function setImage(MapImage $mapImage, bool $sendUpdate = true) : void{ }

    public function updateImage(MapImage $mapImage, int $xOffset = 0, int $yOffset = 0) : void{ }

    /** @return NetworkSession[] */
    final public function getListeners() : array{
        return array_values($this->listeners);
    }

    /** @param $listeners array<int, NetworkSession> */
    final public function setListeners(array $listeners = []) : void{
        $this->listeners = $listeners;
    }

    /** @internal */
    final public function addListener(NetworkSession $session) : void{
        $this->listeners[spl_object_id($session)] = $session;
    }

    /** @internal */
    final public function removeListener(NetworkSession $session) : void{
        unset($this->listeners[spl_object_id($session)]);
    }

    /**  @param NetworkSession[]|null $listeners */
    final public function broadcastImage(MapImage|null $mapImage = null, int $xOffset = 0, int $yOffset = 0, array $listeners = null) : void{
        if($listeners === null){
            $listeners = $this->listeners;
        }
        foreach($listeners as $session){
            $this->sendImage($session, $mapImage ?? null, $xOffset, $yOffset);
        }
    }

    final public function sendImage(NetworkSession $session, MapImage|null $mapImage = null, int $xOffset = 0, int $yOffset = 0) : void{
        $pk = new ClientboundMapItemDataPacket();
        $pk->mapId = $this->getId();
        $pk->colors = $mapImage ?? $this->getImage($session);
        $pk->scale = 1; // TODO: Implement scaling
        $pk->xOffset = $xOffset;
        $pk->yOffset = $yOffset;
        $session->sendDataPacket($pk);

        $this->addListener($session);
    }
}