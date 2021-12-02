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

/** Wrapper class for processing tracked object and decoration of Map */
final class MapMarker{
    public const ICON_PLAYER = 0;
    public const ICON_X = 4;
    public const ICON_TRIANGLE_RED = 5;
    public const ICON_LARGE_DOT = 6;
    public const ICON_ITEM_FRAME = 7;
    public const ICON_TRIANGLE_GREEN = 12;
    public const ICON_SMALL_DOT = 13;
    public const ICON_HOUSE = 14;
    public const ICON_OCEAN_MONUMENT = 15;

    /** @url https://minecraft.fandom.com/wiki/Map#Map_icons */
    public int $icon;

    /** It is limited from 0 to 127 to fit the map size. */
    public float $x;

    /** It is limited from 0 to 127 to fit the map size. */
    public float $y;

    /** It is limited from 0 to 360. */
    public float $rotation;

    /** If null it means as rgb(255,255,255). */
    public ?Color $color;

    public function __construct(
        int $icon = self::ICON_PLAYER,
        float $x = 0,
        float $y = 0,
        float $rotation = 0,
        Color|null $color = null
    ){
        $this->icon = $icon;
        $this->x = $x;
        $this->y = $y;
        $this->rotation = $rotation;
        $this->color = $color;
    }
}