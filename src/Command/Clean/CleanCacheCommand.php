<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command\Clean;

use FactorioItemBrowser\Export\Cache\AbstractCache;
use FactorioItemBrowser\Export\Command\CommandInterface;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * The command for cleaning the caches.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanCacheCommand implements CommandInterface
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
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $console): int
    {
        $modName = $route->getMatchedParam('modName', '');
        foreach ($this->caches as $cache) {
            if ($modName !== '') {
                $cache->clearMod($modName);
            } else {
                $cache->clear();
            }
        }
        return 0;
    }
}
