<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModFileManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\ModFileManager
 */
class ModFileManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked serializer interface.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $modsDirectory = 'abc';
        $manager = new ModFileManager($this->serializer, $modsDirectory);

        $this->assertSame($this->serializer, $this->extractProperty($manager, 'serializer'));
        $this->assertSame($modsDirectory, $this->extractProperty($manager, 'modsDirectory'));
    }

    /**
     * Tests the getLocalDirectory method.
     * @covers ::getLocalDirectory
     */
    public function testGetLocalDirectory(): void
    {
        $modsDirectory = 'abc';
        $modName = 'def';
        $expectedResult = 'abc/def';

        $manager = new ModFileManager($this->serializer, $modsDirectory);
        $result = $manager->getLocalDirectory($modName);

        $this->assertSame($expectedResult, $result);
    }
}
