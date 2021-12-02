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

use pocketmine\color\Color;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;

use function array_values;
use function max;
use function min;
use function spl_object_id;

abstract class Map{
    private int $id;

    /** @var array<string, NetworkSession> */
    protected array $listeners = [];

    /** @var array<MapMarker> */
    protected array $markers = [];

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

    /** @return MapMarker[] */
    final public function getMarkers() : array{
        return array_values($this->markers);
    }

    /** @param $markers MapMarker[] */
    final public function setMarkers(array $markers = [], bool $sendUpdate = true) : void{
        $this->markers = $markers;
        if($sendUpdate){
            $this->broadcastMarkers();
        }
    }

    final public function addMarker(MapMarker $marker, bool $sendUpdate = true) : void{
        $this->markers[spl_object_id($marker)] = $marker;
        if($sendUpdate){
            $this->broadcastMarkers();
        }
    }

    final public function removeMarker(MapMarker $marker, bool $sendUpdate = true) : void{
        unset($this->markers[spl_object_id($marker)]);
        if($sendUpdate){
            $this->broadcastMarkers();
        }
    }

    /**  @param NetworkSession[]|null $listeners */
    final public function broadcastMarkers(array $listeners = null) : void{
        if($listeners === null){
            $listeners = $this->listeners;
        }
        foreach($listeners as $session){
            $this->sendMarkers($session);
        }
    }

    final public function sendMarkers(NetworkSession $session) : void{
        $pk = new ClientboundMapItemDataPacket();
        $pk->mapId = $this->getId();
        $pk->scale = 0; // TODO: Implement scaling
        foreach($this->markers as $hashId => $marker){
            $pk->decorations[] = new MapDecoration(
                $marker->icon,
                (int) ($marker->rotation / 22.5),
                (int) max(-128, min(127, $marker->x * 2 - 128)),
                (int) max(-128, min(127, $marker->y * 2 - 128)),
                "",
                $marker->color ?? new Color(255, 255, 255)
            );
            $tracking = new MapTrackedObject();
            $tracking->type = MapTrackedObject::TYPE_ENTITY;
            $tracking->actorUniqueId = $hashId;
            $pk->trackedEntities[] = $tracking;
        }
        $session->sendDataPacket($pk);
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
        $pk->scale = 0; // TODO: Implement scaling
        $pk->xOffset = $xOffset;
        $pk->yOffset = $yOffset;
        $session->sendDataPacket($pk);

        $this->addListener($session);
    }

    final public function sendDeco(NetworkSession $session, int $icon, int $rotation, int $xOffset, int $yOffset, string $label, Color $color) : void{
        $pk = new ClientboundMapItemDataPacket();
        $pk->mapId = $this->getId();
        $pk->scale = 0; // TODO: Implement scaling
        $pk->decorations = [new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color)];
        $session->sendDataPacket($pk);

        $this->addListener($session);
    }
}