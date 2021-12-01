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

namespace ref\api\mapmanager\map;

use Closure;
use OverflowException;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\MapImage;

class StaticMap extends Map{
    private MapImage $mapImage;

    public function __construct(int $id, MapImage $mapImageProvider){
        parent::__construct($id);
        $this->mapImage = $mapImageProvider;
    }

    public function getImage(NetworkSession|null $session = null) : MapImage{
        return $this->mapImage;
    }

    public function setImage(MapImage|Closure $mapImage, bool $sendUpdate = true) : void{
        $this->mapImage = $mapImage;
        if($sendUpdate){
            foreach($this->listeners as $session){
                $this->sendImage($session, $mapImage);
            }
        }
    }

    public function updateImage(MapImage $mapImage, int $xOffset = 0, int $yOffset = 0) : void{
        if(($maxX = $xOffset + $mapImage->getWidth()) >= $this->mapImage->getWidth()){
            throw new OverflowException("Max x is out of range({$this->mapImage->getWidth()}), given $maxX.");
        }
        if(($maxY = $yOffset + $mapImage->getHeight()) >= $this->mapImage->getHeight()){
            throw new OverflowException("Max y is out of range({$this->mapImage->getHeight()}), given $maxY");
        }
        $pixels = $this->mapImage->getPixels();
        $overlap = $mapImage->getPixels();
        foreach($overlap as $y => $row){
            foreach($row as $x => $color){
                $pixels[$y + $yOffset][$x + $xOffset] = $color;
            }
        }
        $this->mapImage = new MapImage($pixels);
        $this->broadcastImage($mapImage, $xOffset, $yOffset);
    }
}