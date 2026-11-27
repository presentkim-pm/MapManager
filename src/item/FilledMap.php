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

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\world\format\io\GlobalItemDataHandlers;
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

    private static ?int $typeId = null;
    private static bool $registered = false;

    /** Returns the item type id of the filled map item, which is allocated on first use. */
    public static function typeId() : int{
        return self::$typeId ??= ItemTypeIds::newId();
    }

    /**
     * Registers the filled map item to the item (de)serializer and the string parser.
     * PocketMine-MP has no filled map item, so this plugin provides it as `minecraft:filled_map`.
     */
    public static function register() : void{
        if(self::$registered){
            return;
        }
        self::$registered = true;

        GlobalItemDataHandlers::getSerializer()->map(
            new self(new ItemIdentifier(self::typeId()), "Filled Map"),
            static fn(self $item) => new SavedItemData(ItemTypeNames::FILLED_MAP)
        );
        GlobalItemDataHandlers::getDeserializer()->map(
            ItemTypeNames::FILLED_MAP,
            // Prevent map id set to -1, -1 will be broken client
            static fn() => self::create(0, false)
        );
        $parser = StringToItemParser::getInstance();
        $parser->register("filled_map", static fn() => self::create(0, false));
        $parser->register("map", static fn() => self::create(0, false));
    }

    public function isDisplayPlayers() : bool{
        return $this->getNamedTag()->getByte(self::TAG_MAP_IS_DISPLAY_PLAYERS, 0) !== 0;
    }

    /** @return $this */
    public function setDisplayPlayers(bool $value) : self{
        $this->getNamedTag()->setByte(self::TAG_MAP_IS_DISPLAY_PLAYERS, (int) $value);

        return $this;
    }

    public function getUuid() : int{
        return $this->getNamedTag()->getLong(self::TAG_MAP_UUID, 0);
    }

    /** @return $this */
    public function setUuid(int $value) : self{
        if($value < 0){
            throw new UnderflowException("Uuid of Map must be greater than or equal to 0. '$value' given.");
        }
        $this->getNamedTag()->setLong(self::TAG_MAP_UUID, $value);

        return $this;
    }

    public function getNameIndex() : int{
        return $this->getNamedTag()->getInt(self::TAG_MAP_NAME_INDEX, 0);
    }

    /** @return $this */
    public function setNameIndex(int $value) : self{
        if($value < 0){
            throw new UnderflowException("Name index of Map must be greater than or equal to 0. '$value' given.");
        }
        $this->getNamedTag()->setInt(self::TAG_MAP_NAME_INDEX, $value);

        return $this;
    }

    public static function create(int $mapId, bool $displayPlayers = true) : self{
        $map = new self(new ItemIdentifier(self::typeId()), "Filled Map");
        $map->setUuid($mapId);
        $map->setDisplayPlayers($displayPlayers);
        return $map;
    }
}