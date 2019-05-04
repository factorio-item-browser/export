<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Reducer\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\ReducerException;
use FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducer;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\EntityRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ThumbnailReducer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Reducer\Mod\ThumbnailReducer
 */
class ThumbnailReducerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked raw icon registry.
     * @var EntityRegistry&MockObject
     */
    protected $rawIconRegistry;

    /**
     * The mocked reduced icon registry.
     * @var EntityRegistry&MockObject
     */
    protected $reducedIconRegistry;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rawIconRegistry = $this->createMock(EntityRegistry::class);
        $this->reducedIconRegistry = $this->createMock(EntityRegistry::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $reducer = new ThumbnailReducer($this->rawIconRegistry, $this->reducedIconRegistry);

        $this->assertSame($this->rawIconRegistry, $this->extractProperty($reducer, 'rawIconRegistry'));
        $this->assertSame($this->reducedIconRegistry, $this->extractProperty($reducer, 'reducedIconRegistry'));
    }

    /**
     * Tests the reduce method.
     * @throws ReducerException
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        $thumbnailHash = 'abc';

        /* @var Icon&MockObject $icon */
        $icon = $this->createMock(Icon::class);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getThumbnailHash')
            ->willReturn($thumbnailHash);

        $this->rawIconRegistry->expects($this->once())
                              ->method('get')
                              ->with($this->identicalTo($thumbnailHash))
                              ->willReturn($icon);

        $this->reducedIconRegistry->expects($this->once())
                                  ->method('set')
                                  ->with($this->identicalTo($icon));

        $reducer = new ThumbnailReducer($this->rawIconRegistry, $this->reducedIconRegistry);
        $reducer->reduce($mod);
    }

    /**
     * Tests the reduce method without an actual thumbnail hash.
     * @throws ReducerException
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduceWithoutThumbnailHash(): void
    {
        $thumbnailHash = '';

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getThumbnailHash')
            ->willReturn($thumbnailHash);

        $this->rawIconRegistry->expects($this->never())
                              ->method('get');

        $this->reducedIconRegistry->expects($this->never())
                                  ->method('set');

        $reducer = new ThumbnailReducer($this->rawIconRegistry, $this->reducedIconRegistry);
        $reducer->reduce($mod);
    }

    /**
     * Tests the reduce method without an actual icon.
     * @throws ReducerException
     * @throws ReflectionException
     * @covers ::reduce
     */
    public function testReduceWithoutIcon(): void
    {
        $thumbnailHash = 'abc';
        $icon = null;

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getThumbnailHash')
            ->willReturn($thumbnailHash);

        $this->rawIconRegistry->expects($this->once())
                              ->method('get')
                              ->with($this->identicalTo($thumbnailHash))
                              ->willReturn($icon);

        $this->reducedIconRegistry->expects($this->never())
                                  ->method('set');

        $this->expectException(ReducerException::class);

        $reducer = new ThumbnailReducer($this->rawIconRegistry, $this->reducedIconRegistry);
        $reducer->reduce($mod);
    }
}
