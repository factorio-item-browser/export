<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Constant;

/**
 * The interface holding the names of the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CommandName
{
    /**
     * The name of the processing command.
     */
    public const PROCESS = 'process';

    /**
     * The name of the command rendering an icon.
     */
    public const RENDER_ICON = 'render-icon';
}
