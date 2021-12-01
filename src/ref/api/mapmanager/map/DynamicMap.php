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
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use DaveRandom\CallbackValidator\Type;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\utils\Utils;

final class DynamicMap extends Map{
    /** @var Closure(NetworkSession|null $session = null) : MapImage */
    private Closure $mapImageProvider;

    /** @param $mapImageProvider Closure(NetworkSession|null $session = null) : MapImage */
    public function __construct(int $id, Closure $mapImageProvider){
        parent::__construct($id);

        Utils::validateCallableSignature(new CallbackType(
            new ReturnType(MapImage::class),
            new ParameterType("session", NetworkSession::class, Type::NULLABLE | ParameterType::OPTIONAL)
        ), $mapImageProvider);
        $this->mapImageProvider = $mapImageProvider;
    }

    public function getImage(NetworkSession|null $session = null) : MapImage{
        return ($this->mapImageProvider)($session);
    }
}