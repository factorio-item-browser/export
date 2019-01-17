<?php

namespace FactorioItemBrowserTest\Export\Merger;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Exception\MergerException;
use FactorioItemBrowser\Export\Merger\MergerInterface;
use FactorioItemBrowser\Export\Merger\MergerManager;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MergerManager class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Merger\MergerManager
 */
class MergerManagerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var MergerInterface $merger1 */
        $merger1 = $this->createMock(MergerInterface::class);
        /* @var MergerInterface $merger2 */
        $merger2 = $this->createMock(MergerInterface::class);
        $mergers = [$merger1, $merger2];
        
        $mergerManager = new MergerManager($mergers);
        $this->assertSame($mergers, $this->extractProperty($mergerManager, 'mergers'));
    }

    /**
     * Tests the merge method.
     * @throws MergerException
     * @covers ::merge
     */
    public function testMerge(): void
    {
        /* @var Combination $destination */
        $destination = $this->createMock(Combination::class);
        /* @var Combination $source */
        $source = $this->createMock(Combination::class);
        
        /* @var MergerInterface|MockObject $merger1 */
        $merger1 = $this->getMockBuilder(MergerInterface::class)
                        ->setMethods(['merge'])
                        ->getMockForAbstractClass();
        $merger1->expects($this->once())
                ->method('merge')
                ->with($destination, $source);

        /* @var MergerInterface|MockObject $merger2 */
        $merger2 = $this->getMockBuilder(MergerInterface::class)
                        ->setMethods(['merge'])
                        ->getMockForAbstractClass();
        $merger2->expects($this->once())
                ->method('merge')
                ->with($destination, $source);
        
        $mergerManager = new MergerManager([$merger1, $merger2]);

        $mergerManager->merge($destination, $source);
    }
}
