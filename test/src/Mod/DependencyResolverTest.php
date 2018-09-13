<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Export\Mod\DependencyResolver;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DependencyResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Mod\DependencyResolver
 */
class DependencyResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $resolver = new DependencyResolver($modRegistry);
        $this->assertSame($modRegistry, $this->extractProperty($resolver, 'modRegistry'));
    }

    /**
     * Tests the resolveMandatoryDependencies method.
     * @covers ::resolveMandatoryDependencies
     */
    public function testResolveMandatoryDependencies(): void
    {
        $modNames = ['def', 'abc'];
        $sortedModNames = ['abc', 'def'];
        $expectedResult = ['cba', 'fed'];

        /* @var DependencyResolver|MockObject $resolver */
        $resolver = $this->getMockBuilder(DependencyResolver::class)
                         ->setMethods(['sortModNames', 'processMod'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('sortModNames')
                 ->with($modNames)
                 ->willReturn($sortedModNames);
        $resolver->expects($this->exactly(2))
                 ->method('processMod')
                 ->withConsecutive(
                     [$modNames, 'abc', true],
                     [$modNames, 'def', true]
                 )
                 ->willReturnCallback(function (array $_, string $modName) use ($resolver): void {
                     $modNames = $this->extractProperty($resolver, 'resolvedModNames');
                     $modNames[strrev($modName)] = true;
                     $this->injectProperty($resolver, 'resolvedModNames', $modNames);
                 });

        $result = $resolver->resolveMandatoryDependencies($modNames);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the resolveOptionalDependencies method.
     * @covers ::resolveOptionalDependencies
     */
    public function testResolveOptionalDependencies(): void
    {
        $modNames = ['def', 'abc'];
        $mandatoryModNames = ['abc', 'cba'];
        $sortedModNames = ['abc', 'def'];
        $expectedResult = ['fed'];

        /* @var DependencyResolver|MockObject $resolver */
        $resolver = $this->getMockBuilder(DependencyResolver::class)
                         ->setMethods(['resolveMandatoryDependencies', 'sortModNames', 'processMod'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('resolveMandatoryDependencies')
                 ->with($modNames)
                 ->willReturn($mandatoryModNames);
        $resolver->expects($this->once())
                 ->method('sortModNames')
                 ->with($modNames)
                 ->willReturn($sortedModNames);
        $resolver->expects($this->exactly(2))
                 ->method('processMod')
                 ->withConsecutive(
                     [$modNames, 'abc', false],
                     [$modNames, 'def', false]
                 )
                 ->willReturnCallback(function (array $_, string $modName) use ($resolver): void {
                     $modNames = $this->extractProperty($resolver, 'resolvedModNames');
                     $modNames[strrev($modName)] = true;
                     $this->injectProperty($resolver, 'resolvedModNames', $modNames);
                 });

        $result = $resolver->resolveOptionalDependencies($modNames);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the isDependencyMandatory test.
     * @return array
     */
    public function provideIsDependencyMandatory(): array
    {
        $dependency1 = new Dependency();
        $dependency1->setRequiredModName('abc')
                    ->setIsMandatory(true);

        $dependency2 = new Dependency();
        $dependency2->setRequiredModName('abc')
                    ->setIsMandatory(false);

        return [
            [true, $dependency1, ['abc', 'def'], true],
            [true, $dependency1, ['def', 'ghi'], true],
            [true, $dependency2, ['abc', 'def'], true],
            [true, $dependency2, ['def', 'ghi'], false],
            [false, $dependency1, ['abc', 'def'], false],
        ];
    }

    /**
     * Tests the isDependencyMandatory method.
     * @param bool $isMandatory
     * @param Dependency $dependency
     * @param array $modNames
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isDependencyMandatory
     * @dataProvider provideIsDependencyMandatory
     */
    public function testIsDependencyMandatory(
        bool $isMandatory,
        Dependency $dependency,
        array $modNames,
        bool $expectedResult
    ): void {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $resolver = new DependencyResolver($modRegistry);
        $result = $this->invokeMethod($resolver, 'isDependencyMandatory', $isMandatory, $dependency, $modNames);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the isDependencyOptional test.
     * @return array
     */
    public function provideIsDependencyOptional(): array
    {
        $dependency1 = new Dependency();
        $dependency1->setIsMandatory(false);

        $dependency2 = new Dependency();
        $dependency2->setIsMandatory(true);

        return [
            [false, $dependency1, true],
            [false, $dependency2, false],
            [true, $dependency1, false],
            [true, $dependency2, false],
        ];
    }

    /**
     * Tests the isDependencyOptional method.
     * @param bool $isMandatory
     * @param Dependency $dependency
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isDependencyOptional
     * @dataProvider provideIsDependencyOptional
     */
    public function testIsDependencyOptional(bool $isMandatory, Dependency $dependency, bool $expectedResult): void
    {
        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $resolver = new DependencyResolver($modRegistry);
        $result = $this->invokeMethod($resolver, 'isDependencyOptional', $isMandatory, $dependency);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the sortModNames method.
     * @throws ReflectionException
     * @covers ::sortModNames
     */
    public function testSortModNames(): void
    {
        $modNames = ['abc', 'Abba', 'abd', 'Abu'];
        $expectedResult = ['Abba', 'abc', 'abd', 'Abu'];

        /* @var ModRegistry $modRegistry */
        $modRegistry = $this->createMock(ModRegistry::class);

        $resolver = new DependencyResolver($modRegistry);
        $result = $this->invokeMethod($resolver, 'sortModNames', $modNames);
        $this->assertEquals($expectedResult, $result);
    }
}
