<?php

declare(strict_types=1);

namespace ref\api\mapmanager\tests;

use PHPUnit\Framework\TestCase;
use pocketmine\color\Color;
use ref\api\mapmanager\utils\MapImageUtils;

final class MapImageUtilsTest extends TestCase{

    public function testFromOneColor() : void{
        $image = MapImageUtils::fromOneColor(new Color(1, 2, 3, 4), 3, 2);

        self::assertSame(3, $image->getWidth());
        self::assertSame(2, $image->getHeight());
        self::assertSame(2, $image->getPixels()[1][2]->getG());
    }

    public function testEnlargementUsesNearestNeighbor() : void{
        $a = new Color(1, 0, 0);
        $b = new Color(2, 0, 0);
        $c = new Color(3, 0, 0);
        $d = new Color(4, 0, 0);

        $image = MapImageUtils::fromPixels([[$a, $b], [$c, $d]], 4, 4);

        $reds = array_map(static fn(array $row) => array_map(static fn(Color $c) => $c->getR(), $row), $image->getPixels());
        self::assertSame([[1, 1, 2, 2], [1, 1, 2, 2], [3, 3, 4, 4], [3, 3, 4, 4]], $reds);
    }

    public function testShrinkIsRejected() : void{
        $this->expectException(\InvalidArgumentException::class);
        MapImageUtils::fromPixels(MapImageUtils::fromOneColor(new Color(0, 0, 0), 4, 4)->getPixels(), 2, 2);
    }

    public function testRaggedRowsAreRejected() : void{
        $this->expectException(\InvalidArgumentException::class);
        MapImageUtils::fromPixels([[new Color(0, 0, 0)], [new Color(0, 0, 0), new Color(0, 0, 0)]], 4, 4);
    }

    public function testEmptyInputIsRejected() : void{
        $this->expectException(\InvalidArgumentException::class);
        MapImageUtils::fromPixels([], 4, 4);
    }

    public function testValidateSize() : void{
        self::assertTrue(MapImageUtils::validateSize(MapImageUtils::fromOneColor(new Color(0, 0, 0), 128, 128)));
        self::assertTrue(MapImageUtils::validateSize(MapImageUtils::fromOneColor(new Color(0, 0, 0), 1, 1)));
    }
}
