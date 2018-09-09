<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\ExportData\RawExportDataService;
use FactorioItemBrowser\Export\I18n\Translator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the recipe parser.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeParserFactory implements FactoryInterface
{
    /**
     * Creates the parser.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RecipeParser
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var RawExportDataService $rawExportDataService */
        $rawExportDataService = $container->get(RawExportDataService::class);
        /* @var Translator $translator */
        $translator = $container->get(Translator::class);

        return new RecipeParser($rawExportDataService->getRecipeRegistry(), $translator);
    }
}
