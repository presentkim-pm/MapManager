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

namespace ref\api\mapmanager\utils;

use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\types\MapImage;

use function array_fill;

final class MapImageUtils{
    private function __construct(){ }

    /** Returns a one color image object of the given size. */
    public static function generateMapImage(Color $color, int $width, int $height) : MapImage{
        return new MapImage(array_fill(0, $height, array_fill(0, $width, $color)));
    }

    public static function validateSize(MapImage $image) : bool{
        return $image->getWidth() > 0 && $image->getHeight() > 0 && $image->getWidth() <= 128 && $image->getHeight() <= 128;
    }
}