<?php

declare(strict_types=1);

namespace ref\api\mapmanager\tests;

use PHPUnit\Framework\TestCase;
use pocketmine\item\StringToItemParser;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ref\api\mapmanager\item\FilledMap;

final class FilledMapTest extends TestCase{

    public function testCreateStoresTheMapId() : void{
        $map = FilledMap::create(42, false);

        self::assertSame(42, $map->getUuid());
        self::assertFalse($map->isDisplayPlayers());
        self::assertTrue(FilledMap::create(1)->isDisplayPlayers());
    }

    public function testMissingTagsHaveDefaults() : void{
        $map = new FilledMap(new \pocketmine\item\ItemIdentifier(FilledMap::typeId()), "Filled Map");

        self::assertSame(0, $map->getUuid());
        self::assertSame(0, $map->getNameIndex());
        self::assertFalse($map->isDisplayPlayers());
    }

    public function testNegativeValuesAreRejected() : void{
        $this->expectException(\UnderflowException::class);
        FilledMap::create(0)->setUuid(-1);
    }

    public function testSerializationRoundTrip() : void{
        FilledMap::register();

        $item = FilledMap::create(123456789012, true)->setNameIndex(3);
        $data = GlobalItemDataHandlers::getSerializer()->serializeStack($item);
        self::assertSame("minecraft:filled_map", $data->getTypeData()->getName());

        $copy = GlobalItemDataHandlers::getDeserializer()->deserializeStack($data);
        self::assertInstanceOf(FilledMap::class, $copy);
        self::assertSame(123456789012, $copy->getUuid());
        self::assertSame(3, $copy->getNameIndex());
        self::assertTrue($copy->isDisplayPlayers());
        self::assertTrue($item->equals($copy));
    }

    public function testStringParser() : void{
        FilledMap::register();

        $parsed = StringToItemParser::getInstance()->parse("filled_map");
        self::assertInstanceOf(FilledMap::class, $parsed);
        self::assertSame(0, $parsed->getUuid());
    }
}
