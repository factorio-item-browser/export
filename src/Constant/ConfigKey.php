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
     * The main key of the config.
     */
    public const MAIN = 'export';

    /**
     * The key holding the aliases of the data processors.
     */
    public const DATA_PROCESSORS = 'data-processors';

    /**
     * The key holding the directories of the project.
     */
    public const DIRECTORIES = 'directories';

    /**
     * The key holding the cache directory.
     */
    public const DIRECTORY_CACHE = 'cache';

    /**
     * The key holding the directory of the installed full Factorio game.
     */
    public const DIRECTORY_FACTORIO_FULL = 'factorio-full';

    /**
     * The key holding the directory of the installed headless Factorio game.
     */
    public const DIRECTORY_FACTORIO_HEADLESS = 'factorio-headless';

    /**
     * The key holding the instances directory.
     */
    public const DIRECTORY_INSTANCES = 'instances';

    /**
     * The key holding the log directory.
     */
    public const DIRECTORY_LOGS = 'logs';

    /**
     * The key holding the mods directory.
     */
    public const DIRECTORY_MODS = 'mods';

    /**
     * The key holding the temp directory.
     */
    public const DIRECTORY_TEMP = 'temp';

    /**
     * The key holding the aliases of the output processors.
     */
    public const OUTPUT_PROCESSORS = 'output-processors';

    /**
     * The key holding the number of parallel downloads to use.
     */
    public const PARALLEL_DOWNLOADS = 'parallel-downloads';

    /**
     * The key holding the number of parallel render processes to use.
     */
    public const PARALLEL_RENDERS = 'parallel-renders';

    /**
     * The key holding the step classes.
     */
    public const PROCESS_STEPS = 'process-steps';

    /**
     * The key holding the path to the render-icon binary.
     */
    public const RENDER_ICON_BINARY = 'render-icon-binary';

    /**
     * The key holding the configuration of the serializer.
     */
    public const SERIALIZER = 'serializer';
}
