<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Clean;

use Exception;
use FactorioItemBrowser\Export\Cache\LocaleCache;
use FactorioItemBrowser\Export\Cache\ModFileCache;
use FactorioItemBrowser\Export\Factorio\DumpExtractor;
use FactorioItemBrowser\Export\Parser\ParserManager;
use FactorioItemBrowser\Export\Renderer\IconRenderer;
use FactorioItemBrowser\ExportData\Entity\Combination;
use FactorioItemBrowser\ExportData\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the clear cache command.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanCacheCommandFactory implements FactoryInterface
{
    /**
     * Creates the command.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CleanCacheCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var IconRenderer $iconRenderer */
        $iconRenderer = $container->get(IconRenderer::class);
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        $export = $exportDataService->createExport('abc');

        /* @var DumpExtractor $dumpExtractor */
        $dumpExtractor = $container->get(DumpExtractor::class);
        /* @var ParserManager $parserManager */
        $parserManager = $container->get(ParserManager::class);

        echo "Extracting dump\n";
        $dump = $dumpExtractor->extract(file_get_contents(__DIR__ . '/../../../data/log.txt'));

        echo "Parsing\n";
        $parserManager->parse($dump, $export->getCombination());

        echo "Render icons\n";
        foreach ($export->getCombination()->getIcons() as $icon) {
            try {
                $export->addRenderedIcon($icon, $iconRenderer->render($icon));
            } catch (Exception $e) {
                echo "RENDER ERROR: {$icon->getHash()} > {$e->getMessage()}\n";
            }
        }

        echo "Persisting\n";
        $export->persist();

        echo "Done.\n";
//        var_dump($export->getCombination()->getMods()[10]);
        die;


        /* @var LocaleCache $localeCache */
        $localeCache = $container->get(LocaleCache::class);
        /* @var ModFileCache $modFileCache */
        $modFileCache = $container->get(ModFileCache::class);

        return new CleanCacheCommand([
            $localeCache,
            $modFileCache
        ]);
    }
}
