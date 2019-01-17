<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\I18n\Translator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the machine parser.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineParserFactory implements FactoryInterface
{
    /**
     * Creates the parser.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MachineParser
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var IconParser $iconParser */
        $iconParser = $container->get(IconParser::class);
        /* @var ItemParser $itemParser */
        $itemParser = $container->get(ItemParser::class);
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var Translator $translator */
        $translator = $container->get(Translator::class);

        return new MachineParser($iconParser, $itemParser, $rawExportDataService->getMachineRegistry(), $translator);
    }
}
