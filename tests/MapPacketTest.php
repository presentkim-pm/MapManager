<?php

declare(strict_types=1);

namespace ref\api\mapmanager\tests;

use pocketmine\color\Color;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\MapImage;
use ref\api\mapmanager\map\DynamicMap;
use ref\api\mapmanager\map\MapMarker;
use ref\api\mapmanager\map\StaticMap;
use ref\api\mapmanager\utils\MapImageUtils;

final class MapPacketTest extends PacketTestCase{

    public function testSendImageEncodesAndRegistersListener() : void{
        $map = new StaticMap(1234, MapImageUtils::fromOneColor(new Color(1, 2, 3, 255), 4, 2));
        $session = $this->createSession();

        $map->sendImage($session);

        $packet = $this->sentOneRoundTripped();
        self::assertSame(1234, $packet->mapId);
        self::assertSame(0, $packet->scale);
        self::assertSame(4, $packet->colors?->getWidth());
        self::assertSame(2, $packet->colors?->getHeight());
        self::assertSame([$session], $map->getListeners());
    }

    public function testPartialImageUpdateKeepsOffsets() : void{
        $map = new StaticMap(5, MapImageUtils::fromOneColor(new Color(0, 0, 0, 255), 8, 8));
        $session = $this->createSession();
        $map->sendImage($session);
        $this->sent = [];

        // fits exactly into the bottom right corner
        $map->updateImage(MapImageUtils::fromOneColor(new Color(100, 0, 0, 255), 2, 2), 6, 6);

        $packet = $this->sentOneRoundTripped();
        self::assertSame(6, $packet->xOffset);
        self::assertSame(6, $packet->yOffset);
        self::assertSame(2, $packet->colors?->getWidth());
        self::assertSame(100, $map->getImage()->getPixels()[7][7]->getR());
        self::assertSame(0, $map->getImage()->getPixels()[5][5]->getR());
    }

    public function testImageUpdateOutOfRangeThrows() : void{
        $map = new StaticMap(5, MapImageUtils::fromOneColor(new Color(0, 0, 0, 255), 8, 8));

        $this->expectException(\OverflowException::class);
        $map->updateImage(MapImageUtils::fromOneColor(new Color(100, 0, 0, 255), 2, 2), 7, 0);
    }

    public function testMarkersWithNegativeOffsetsAreEncodable() : void{
        $map = new StaticMap(9, MapImageUtils::fromOneColor(new Color(0, 0, 0, 255), 2, 2));
        // x = 0 / y = 0 are mapped to the offset -128, which the wire format holds as a byte
        $map->addMarker(new MapMarker(MapMarker::ICON_X, 0, 0, 90, new Color(10, 20, 30)), false);
        $map->addMarker(new MapMarker(MapMarker::ICON_PLAYER, 127, 127, 359.9), false);

        $map->sendMarkers($this->createSession());

        $packet = $this->sentOneRoundTripped();
        self::assertCount(2, $packet->decorations ?? []);
        self::assertCount(2, $packet->trackedEntities ?? []);
        [$first, $second] = $packet->decorations;
        self::assertSame(MapMarker::ICON_X, $first->getIcon());
        self::assertSame(4, $first->getRotation());
        self::assertSame(128, $first->getXOffset()); // -128 as an unsigned byte
        self::assertSame(126, $second->getXOffset());
        self::assertSame(15, $second->getRotation());
        self::assertSame(255, $second->getColor()->getR());
    }

    public function testSendingNoMarkersClearsThem() : void{
        $map = new StaticMap(9, MapImageUtils::fromOneColor(new Color(0, 0, 0, 255), 2, 2));

        $map->sendMarkers($this->createSession());

        $packet = $this->sentOneRoundTripped();
        self::assertSame([], $packet->decorations);
        self::assertSame([], $packet->trackedEntities);
    }

    public function testDynamicMapUsesTheProviderPerSession() : void{
        $calls = 0;
        $map = new DynamicMap(77, static function(?NetworkSession $session = null) use (&$calls) : MapImage{
            ++$calls;
            return MapImageUtils::fromOneColor(new Color(9, 9, 9), 1, 1);
        });

        $map->sendImage($this->createSession());

        self::assertSame(1, $calls);
        self::assertSame(77, $this->sentOneRoundTripped()->mapId);
    }

    public function testSendDecoWithNegativeOffset() : void{
        $map = new StaticMap(3, MapImageUtils::fromOneColor(new Color(0, 0, 0, 255), 1, 1));

        $map->sendDeco($this->createSession(), MapMarker::ICON_HOUSE, 0, -10, 5, "home", new Color(1, 2, 3));

        $deco = $this->sentOneRoundTripped()->decorations[0];
        self::assertSame(246, $deco->getXOffset());
        self::assertSame("home", $deco->getLabel());
    }
}
