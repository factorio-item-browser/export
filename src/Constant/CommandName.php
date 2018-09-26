<?php

namespace FactorioItemBrowser\Export\Constant;

/**
 * The interface holding the command names.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CommandName
{
    public const CLEAN_CACHE = 'clean cache';

    public const EXPORT_COMBINATION = 'export combination';
    public const EXPORT_MOD = 'export mod';

    public const LIST = 'list';

    public const REDUCE_COMBINATION = 'reduce combination';

    public const RENDER_ICON = 'render icon';
    public const RENDER_MOD_ICONS = 'render mod-icons';

    public const UPDATE_DEPENDENCIES = 'update dependencies';
    public const UPDATE_LIST = 'update list';
    public const UPDATE_ORDER = 'update order';
}
