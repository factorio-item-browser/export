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
     * The key holding the working directory of the mod file manager.
     */
    public const MOD_FILE_MANAGER_WORKING_DIRECTORY = 'mod-file-manager-working-directory';
}
