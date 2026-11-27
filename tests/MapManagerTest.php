<?php

declare(strict_types=1);

namespace ref\api\mapmanager\tests;

use pocketmine\color\Color;
use ref\api\mapmanager\MapManager;
use ref\api\mapmanager\map\StaticMap;
use ref\api\mapmanager\utils\MapImageUtils;

final class MapManagerTest extends PacketTestCase{

    private function image() : \pocketmine\network\mcpe\protocol\types\MapImage{
        return MapImageUtils::fromOneColor(new Color(0, 0, 0), 2, 2);
    }

    public function testRegisterAndLookup() : void{
        $manager = MapManager::getInstance();
        $id = MapManager::nextId();

        self::assertFalse($manager->isRegistered($id));
        $manager->registerFrom($id, $this->image());
        try{
            self::assertTrue($manager->isRegistered($id));
            self::assertSame($id, $manager->get($id)->getId());
            self::assertNotNull($manager->getOrNull($id));
            self::assertNotSame($id, $manager->get(-1)->getId(), "Unknown ids give the fallback map");
            self::assertNull($manager->getOrNull(-1));
        }finally{
            $manager->unregister($id);
        }
        self::assertFalse($manager->isRegistered($id));
    }

    public function testConflictNeedsForceAndKeepsListeners() : void{
        $manager = MapManager::getInstance();
        $id = MapManager::nextId();
        $first = new StaticMap($id, $this->image());
        $manager->register($first);
        try{
            $first->addListener($this->createSession());

            try{
                $manager->register(new StaticMap($id, $this->image()));
                self::fail("Registering an existing id must fail without force");
            }catch(\InvalidArgumentException){
            }

            $second = new StaticMap($id, $this->image());
            $manager->register($second, true);
            self::assertSame($second, $manager->getOrNull($id));
            self::assertCount(1, $second->getListeners());
        }finally{
            $manager->unregister($id, false);
        }
    }

    public function testNextIdIsPositive() : void{
        for($i = 0; $i < 50; ++$i){
            self::assertGreaterThan(0, MapManager::nextId());
        }
    }
}
