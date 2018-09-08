<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Command\Update;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Command\Update\UpdateOrderCommand;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The PHPUnit test of the UpdateOrderCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Command\Update\UpdateOrderCommand
 */
class UpdateOrderCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $command = new UpdateOrderCommand($dependencyResolver, $modRegistry);
        $this->assertSame($dependencyResolver, $this->extractProperty($command, 'dependencyResolver'));
        $this->assertSame($modRegistry, $this->extractProperty($command, 'modRegistry'));
    }
    
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $modNames = ['abc', 'def'];
        
        /* @var Route $route */
        $route = $this->createMock(Route::class);
        
        /* @var AdapterInterface|MockObject $console */
        $console = $this->getMockBuilder(AdapterInterface::class)
                        ->setMethods(['writeLine'])
                        ->getMockForAbstractClass();
        $console->expects($this->once())
                ->method('writeLine')
                ->with('Updating order...');
        
        /* @var UpdateOrderCommand|MockObject $command */
        $command = $this->getMockBuilder(UpdateOrderCommand::class)
                        ->setMethods(['getOrderedModNames', 'assignModOrder'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $command->expects($this->once())
                ->method('getOrderedModNames')
                ->willReturn($modNames);
        $command->expects($this->once())
                ->method('assignModOrder')
                ->with($modNames);
        
        $result = $command($route, $console);
        $this->assertSame(0, $result);
    }

    /**
     * Tests the getOrderedModNames method.
     * @covers ::getOrderedModNames
     * @throws ReflectionException
     */
    public function testGetOrderedModNames(): void
    {
        $modNames = ['abc', 'def'];
        $orderedModNames = ['ghi', 'jkl'];
        
        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['getAllNames'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->once())
                    ->method('getAllNames')
                    ->willReturn($modNames);
        
        /* @var DependencyResolver|MockObject $dependencyResolver */
        $dependencyResolver = $this->getMockBuilder(DependencyResolver::class)
                                   ->setMethods(['resolveMandatoryDependencies'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $dependencyResolver->expects($this->once())
                           ->method('resolveMandatoryDependencies')
                           ->with($modNames)
                           ->willReturn($orderedModNames);
        
        $command = new UpdateOrderCommand($dependencyResolver, $modRegistry);
        $result = $this->invokeMethod($command, 'getOrderedModNames');
        $this->assertSame($orderedModNames, $result);
    }

    /**
     * Tests the assignModOrder method.
     * @covers ::assignModOrder
     * @throws ReflectionException
     */
    public function testAssignModOrder(): void
    {
        $orderedModNames = ['abc', 'def', 'ghi'];

        /* @var DependencyResolver $dependencyResolver */
        $dependencyResolver = $this->createMock(DependencyResolver::class);

        /* @var Mod|MockObject $mod1 */
        $mod1 = $this->getMockBuilder(Mod::class)
                     ->setMethods(['setOrder'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $mod1->expects($this->once())
             ->method('setOrder')
             ->with(1);   
        /* @var Mod|MockObject $mod2 */
        $mod2 = $this->getMockBuilder(Mod::class)
                     ->setMethods(['setOrder'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $mod2->expects($this->once())
             ->method('setOrder')
             ->with(2);  

        /* @var ModRegistry|MockObject $modRegistry */
        $modRegistry = $this->getMockBuilder(ModRegistry::class)
                            ->setMethods(['get', 'set', 'saveMods'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $modRegistry->expects($this->exactly(3))
                    ->method('get')
                    ->withConsecutive(
                        ['abc'],
                        ['def'],
                        ['ghi']
                    )
                    ->willReturnOnConsecutiveCalls(
                        $mod1,
                        null,
                        $mod2
                    );
        $modRegistry->expects($this->exactly(2))
                    ->method('set')
                    ->withConsecutive(
                        [$mod1],
                        [$mod2]
                    );
        $modRegistry->expects($this->once())
                    ->method('saveMods');

        $command = new UpdateOrderCommand($dependencyResolver, $modRegistry);
        $this->invokeMethod($command, 'assignModOrder', $orderedModNames);
    }
}
