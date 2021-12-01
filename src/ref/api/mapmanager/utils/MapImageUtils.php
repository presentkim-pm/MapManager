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

use InvalidArgumentException;
use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\types\MapImage;

use function array_fill;
use function count;
use function intdiv;

final class MapImageUtils{
    private function __construct(){ }

    /** Returns a one color image object of the given size. */
    public static function fromOneColor(Color $color, int $targetWidth, int $targetHeight) : MapImage{
        return new MapImage(array_fill(0, $targetHeight, array_fill(0, $targetWidth, $color)));
    }

    /** Auto resize with nearest-neighbor algorithm */
    public static function fromPixels(array $input, int $targetWidth, int $targetHeight) : MapImage{
        $sourceWidth = null;
        foreach($input as $row){
            if($sourceWidth === null){
                $sourceWidth = count($row);
            }elseif(count($row) !== $sourceWidth){
                throw new InvalidArgumentException("All rows must have the same number of pixels");
            }
        }
        $sourceHeight = count($input);
        if($sourceWidth > $targetWidth || $sourceHeight > $targetHeight){
            throw new InvalidArgumentException("Only map image enlargement is supported. Shrinkage is not supported.");
        }

        $output = [];
        $x_ratio = intdiv($sourceWidth << 16, $targetWidth);
        $y_ratio = intdiv($sourceHeight << 16, $targetHeight);
        for($y = 0; $y < $targetHeight; ++$y){
            $sourceY = ($y * $y_ratio) >> 16;
            $output[$y] = [];
            for($x = 0; $x < $targetWidth; ++$x){
                $sourceX = ($x * $x_ratio) >> 16;
                $output[$y][$x] = $input[$sourceY][$sourceX];
            }
        }
        return new MapImage($output);
    }

    public static function validateSize(MapImage $image) : bool{
        return $image->getWidth() > 0 && $image->getHeight() > 0 && $image->getWidth() <= 128 && $image->getHeight() <= 128;
    }

    /** Scale up with nearest-neighbor algorithm */
    public static function resizeMapImage(MapImage $mapImage, int $targetWidth, int $targetHeight) : MapImage{
        return self::fromPixels($mapImage->getPixels(), $targetWidth, $targetHeight);
    }
}