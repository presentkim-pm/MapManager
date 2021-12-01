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

use InvalidArgumentException;
use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\utils\SingletonTrait;
use ref\api\mapmanager\map\Map;
use ref\api\mapmanager\map\StaticMap;

use ref\api\mapmanager\utils\MapImageUtils;

use function array_fill;
use function lcg_value;

final class MapManager{
    use SingletonTrait;

    /** @var array<int, Map> */
    private array $maps = [];

    private function __construct(){ }

    /**
     * Register the given map to map manager.
     *
     * @param bool $force Whether to override existing registrations
     *
     * @throws InvalidArgumentException if attempted to override an already-registered map ID without specifying the $force parameter.
     */
    public function register(Map $map, bool $force = false) : void{
        $id = $map->getId();
        if(isset($this->maps[$id])){
            if($force){
                $map->setListeners($this->maps[$id]->getListeners());
            }else{
                throw new InvalidArgumentException("Map registration $id conflicts with an existing map");
            }
        }
        $this->maps[$id] = $map;
        $map->broadcastMapImage();
    }

    /**
     * Register the given map ID and map image to map manager.
     * It automatically create StaticMap instance amd register that.
     *
     * @param bool $force Whether to override existing registrations
     *
     * @throws InvalidArgumentException if attempted to override an already-registered map ID without specifying the $force parameter.
     */
    public function registerFrom(int $mapId, MapImage $map, bool $force = false) : void{
        $this->register(new StaticMap($mapId, $map), $force);
    }

    /**
     * Unregister the given map to map manager.
     *
     * @param bool $clear Whether to remove map data from map listeners
     */
    public function unregister(int $id, bool $clear = true) : void{
        if(isset($this->maps[$id])){
            if($clear){
                $this->maps[$id]->broadcastMapImage(MapImageUtils::largestMapImage(new Color(0, 0, 0, 0)));
            }
            unset($this->maps[$id]);
        }
    }

    /** Returns whether a specified map ID is already registered in the map manager. */
    public function isRegistered(int $id) : bool{
        return isset($this->maps[$id]);
    }

    /** Returns the map if the specified map ID is already registered in the map manager. */
    public function get(int $id) : ?Map{
        return $this->maps[$id] ?? null;
    }

    /** @return Map[] */
    public function getAllMap() : array{
        return array_values($this->maps);
    }

    public static function nextId() : int{
        $instance = self::getInstance();
        while(true){
            $nextId = (int) (lcg_value() * PHP_INT_MAX);
            if(!$instance->isRegistered($nextId)){
                return $nextId;
            }
        }
    }
}