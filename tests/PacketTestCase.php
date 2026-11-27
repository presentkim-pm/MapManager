<?php

declare(strict_types=1);

namespace ref\api\mapmanager\tests;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use PHPUnit\Framework\TestCase;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;

/**
 * Base class that provides a network session which records every packet sent to it, and a helper to prove that a
 * map packet survives a real encode / decode round trip of the bedrock protocol.
 */
abstract class PacketTestCase extends TestCase{

    /** @var ClientboundPacket[] */
    protected array $sent = [];

    protected function createSession() : NetworkSession{
        $session = $this->createStub(NetworkSession::class);
        $session->method('sendDataPacket')->willReturnCallback(function(ClientboundPacket $pk) : bool{
            $this->sent[] = $pk;
            return true;
        });
        return $session;
    }

    protected function setUp() : void{
        parent::setUp();
        $this->sent = [];
    }

    /** @return ClientboundMapItemDataPacket The only packet that was sent, decoded from its encoded form. */
    protected function sentOneRoundTripped() : ClientboundMapItemDataPacket{
        self::assertCount(1, $this->sent);
        $packet = $this->sent[0];
        self::assertInstanceOf(ClientboundMapItemDataPacket::class, $packet);

        $out = new ByteBufferWriter();
        $packet->encode($out);
        $bytes = $out->getData();

        $copy = new ClientboundMapItemDataPacket();
        $in = new ByteBufferReader($bytes);
        $copy->decode($in);
        self::assertSame(0, $in->getUnreadLength(), "The packet left unread bytes after decoding");

        $out2 = new ByteBufferWriter();
        $copy->encode($out2);
        self::assertSame(bin2hex($bytes), bin2hex($out2->getData()), "The packet changed after a round trip");

        return $copy;
    }
}
