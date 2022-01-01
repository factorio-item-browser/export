<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\DataProcessor;

use BluePsyduck\FactorioTranslator\Translator as FactorioTranslator;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\Export\Output\Console;
use FactorioItemBrowser\ExportData\Collection\DictionaryInterface;
use FactorioItemBrowser\ExportData\Entity\LocalisedEntity;
use FactorioItemBrowser\ExportData\ExportData;
use Generator;

/**
 * The data processor translating the localised entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Translator implements DataProcessorInterface
{
    public function __construct(
        private readonly Console $console,
        private readonly FactorioTranslator $translator,
    ) {
    }

    public function process(ExportData $exportData): void
    {
        $progressBar = $this->console->createProgressBar('Translate entities');
        $progressBar->setNumberOfSteps($this->countEntities($exportData));

        foreach ($this->getEntities($exportData) as $entity) {
            /* @var LocalisedEntity $entity */
            $this->translate($entity->localisedName, $entity->labels);
            $this->translate($entity->localisedDescription, $entity->descriptions);

            $progressBar->finish('');
        }
    }

    protected function countEntities(ExportData $exportData): int
    {
        return $exportData->getItems()->count()
            + $exportData->getMachines()->count()
            + $exportData->getMods()->count()
            + $exportData->getRecipes()->count()
            + $exportData->getTechnologies()->count();
    }

    /**
     * @param ExportData $exportData
     * @return Generator<int, LocalisedEntity, void, void>
     */
    protected function getEntities(ExportData $exportData): Generator
    {
        yield from $exportData->getItems();
        yield from $exportData->getMachines();
        yield from $exportData->getMods();
        yield from $exportData->getRecipes();
        yield from $exportData->getTechnologies();
    }

    protected function translate(mixed $localisedString, DictionaryInterface $dictionary): void
    {
        $defaultTranslation = $this->translator->translate(Defaults::LOCALE, $localisedString);
        if ($defaultTranslation !== '') {
            $dictionary->set(Defaults::LOCALE, $defaultTranslation);
        }

        foreach ($this->translator->getAllLocales() as $locale) {
            if ($locale === Defaults::LOCALE) {
                continue;
            }

            $translation = $this->translator->translate($locale, $localisedString);
            if ($translation !== '' && $translation !== $defaultTranslation) {
                $dictionary->set($locale, $translation);
            }
        }
    }
}
