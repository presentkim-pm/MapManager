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

namespace ref\api\mapmanager\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use UnderflowException;

final class FilledMap extends Item{
    /**
     * @url https://minecraft.fandom.com/wiki/Bedrock_Edition_level_format/Item_format#Filled_Map
     */
    public const TAG_MAP_IS_DISPLAY_PLAYERS = "map_display_players"; // bool (Whether the map displays player markers)

    public const TAG_MAP_UUID = "map_uuid";                          // long (The uuid of the map used in this item)
    public const TAG_MAP_NAME_INDEX = "map_name_index";              // int (The index of the map's name)

    /**
     * MAP_IS_INIT is unnecessary
     * MAP_IS_SCALING and TAG_MAP_SCALE is not working
     */
    //public const TAG_MAP_IS_INIT = "map_is_init";                    // bool (Whether the map is initialized)
    //public const TAG_MAP_IS_SCALING = "map_is_scaling";              // bool (Whether the map is scaled)
    //public const TAG_MAP_SCALE = "map_scale";                        // int (The scale of map)

    public function isDisplayPlayers() : bool{
        return (bool) $this->getNamedTag()->getByte(self::TAG_MAP_IS_DISPLAY_PLAYERS);
    }

    public function setDisplayPlayers(bool $value) : void{
        $this->getNamedTag()->setByte(self::TAG_MAP_IS_DISPLAY_PLAYERS, (int) $value);
    }

    public function getUuid() : int{
        return $this->getNamedTag()->getLong(self::TAG_MAP_UUID, 0);
    }

    public function setUuid(int $value) : void{
        if($value < 0){
            throw new UnderflowException("Uuid of Map must be greater than or equal to 0. '$value' given.");
        }
        $this->getNamedTag()->setLong(self::TAG_MAP_UUID, $value);
    }

    public function getNameIndex() : int{
        return $this->getNamedTag()->getInt(self::TAG_MAP_NAME_INDEX, 0);
    }

    public function setNameIndex(int $value) : void{
        if($value < 0){
            throw new UnderflowException("Uuid of Map must be greater than or equal to 0. '$value' given.");
        }
        $this->getNamedTag()->setInt(self::TAG_MAP_NAME_INDEX, $value);
    }

    public static function create(int $mapId, bool $displayPlayers = true) : self{
        $map = new self(new ItemIdentifier(ItemIds::FILLED_MAP, 0), "Filled Map");
        $map->setUuid($mapId);
        $map->setDisplayPlayers($displayPlayers);
        return $map;
    }
}