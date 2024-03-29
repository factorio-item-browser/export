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

    public const OUTPUT_PROCESSORS = 'output-processors';
    public const OUTPUT_DUMP_PROCESSORS = 'output-dump-processors';

    /**
     * The key holding the number of parallel downloads to use.
     */
    public const PARALLEL_DOWNLOADS = 'parallel-downloads';

    /**
     * The key holding the number of parallel render processes to use.
     */
    public const PARALLEL_RENDERS = 'parallel-renders';

    /**
     * The key holding the parser classes.
     */
    public const PARSERS = 'parsers';

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

    /**
     * The key holding the configuration for the upload FTP server.
     */
    public const UPLOAD_FTP = 'upload-ftp';

    /**
     * The key holding the host of the upload FTP.
     */
    public const UPLOAD_FTP_HOST = 'host';

    /**
     * The key holding the username of the upload FTP.
     */
    public const UPLOAD_FTP_USERNAME = 'username';

    /**
     * The key holding the password pf the upload FTP.
     */
    public const UPLOAD_FTP_PASSWORD = 'password';
}
