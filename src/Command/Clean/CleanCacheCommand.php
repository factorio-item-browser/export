<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Clean;

use FactorioItemBrowser\Export\Cache\AbstractCache;
use FactorioItemBrowser\Export\Command\AbstractCommand;
use ZF\Console\Route;

/**
 * The command for cleaning the caches.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanCacheCommand extends AbstractCommand
{
    /**
     * The caches to clear.
     * @var array|AbstractCache[]
     */
    protected $caches;

    /**
     * Initializes the command
     * @param array|AbstractCache[] $caches
     */
    public function __construct(array $caches)
    {
        $this->caches = $caches;
    }

    /**
     * Executes the command.
     * @param Route $route
     */
    protected function execute(Route $route): void
    {
        $modName = $route->getMatchedParam('modName', '');
        foreach ($this->caches as $cache) {
            if ($modName !== '') {
                $cache->clearMod($modName);
            } else {
                $cache->clear();
            }
        }
    }
}
