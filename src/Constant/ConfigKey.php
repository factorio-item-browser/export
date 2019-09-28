<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Constant;

/**
 * The interface holding the keys of the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ConfigKey
{
    /**
     * The key holding the name of the project.
     */
    public const PROJECT = 'factorio-item-browser';

    /**
     * The key holding the name of the export project itself.
     */
    public const EXPORT = 'export';

    /**
     * The key holding the directories of the project.
     */
    public const DIRECTORIES = 'directories';

    /**
     * The key holding the mods directory.
     */
    public const DIRECTORY_MODS = 'mods';

    /**
     * The key holding the temp directory.
     */
    public const DIRECTORY_TEMP = 'temp';

    /**
     * The key holding the cache directory.
     */
    public const DIRECTORY_CACHE = 'cache';

    /**
     * The key holding the number of parallel downloads to use.
     */
    public const PARALLEL_DOWNLOADS = 'parallel-downloads';
}
