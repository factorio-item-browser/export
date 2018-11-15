<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Export\Utils;

use FactorioItemBrowser\Export\Utils\LocalisedStringUtils;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the LocalisedStringUtils class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Export\Utils\LocalisedStringUtils
 */
class LocalisedStringUtilsTest extends TestCase
{
    /**
     * Provides the data for the areEqual test.
     * @return array
     */
    public function provideAreEqual(): array
    {
        $localisedString1 = new LocalisedString();
        $localisedString1->setTranslation('en', 'abc')
                         ->setTranslation('de', 'def');

        $localisedString2 = new LocalisedString();
        $localisedString2->setTranslation('en', 'abc');

        $localisedString3 = new LocalisedString();
        $localisedString3->setTranslation('en', 'abc')
                         ->setTranslation('de', 'ghi');

        return [
            [$localisedString1, $localisedString1, true],
            [$localisedString1, $localisedString2, true],
            [$localisedString1, $localisedString3, false],
        ];
    }

    /**
     * Tests the areEqual method.
     * @param LocalisedString $left
     * @param LocalisedString $right
     * @param bool $expectedResult
     * @covers ::areEqual
     * @dataProvider provideAreEqual
     */
    public function testAreEqual(LocalisedString $left, LocalisedString $right, bool $expectedResult): void
    {
        $result = LocalisedStringUtils::areEqual($left, $right);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the merge method.
     * @covers ::merge
     */
    public function testMerge(): void
    {
        $destination = new LocalisedString();
        $destination->setTranslation('en', 'abc')
                    ->setTranslation('de', 'def');

        $source = new LocalisedString();
        $source->setTranslation('en', 'ghi')
               ->setTranslation('fr', 'jkl');

        $expectedDestination = new LocalisedString();
        $expectedDestination->setTranslation('en', 'ghi')
                            ->setTranslation('de', 'def')
                            ->setTranslation('fr', 'jkl');

        LocalisedStringUtils::merge($destination, $source);
        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the reduce method.
     * @covers ::reduce
     */
    public function testReduce(): void
    {
        $localisedString = new LocalisedString();
        $localisedString->setTranslation('en', 'abc')
                        ->setTranslation('de', 'def')
                        ->setTranslation('fr', 'ghi');

        $parentLocalisedString = new LocalisedString();
        $parentLocalisedString->setTranslation('en', 'jkl')
                              ->setTranslation('de', 'def')
                              ->setTranslation('jp', 'jkl');

        $expectedLocalisedString = new LocalisedString();
        $expectedLocalisedString->setTranslation('en', 'abc')
                                ->setTranslation('fr', 'ghi');

        LocalisedStringUtils::reduce($localisedString, $parentLocalisedString);
        $this->assertEquals($expectedLocalisedString, $localisedString);
    }

    /**
     * Provides the data for the isEmpty test.
     * @return array
     */
    public function provideIsEmpty(): array
    {
        return [
            [(new LocalisedString())->setTranslation('en', 'abc'), false],
            [new LocalisedString(), true],
        ];
    }

    /**
     * Tests the isEmpty method.
     * @param LocalisedString $localisedString
     * @param bool $expectedResult
     * @covers ::isEmpty
     * @dataProvider provideIsEmpty
     */
    public function testIsEmpty(LocalisedString $localisedString, bool $expectedResult): void
    {
        $result = LocalisedStringUtils::isEmpty($localisedString);
        $this->assertSame($expectedResult, $result);
    }
}
