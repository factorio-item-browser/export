<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\I18n;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the translator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslatorFactory implements FactoryInterface
{
    /**
     * Creates the translator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return Translator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var LocaleReader $localeReader */
        $localeReader = $container->get(LocaleReader::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var TranslatorInterface $translatorInterface */
        $translator = $container->get(TranslatorInterface::class);

        return new Translator($localeReader, $rawExportDataService->getModRegistry(), $translator);
    }
}
