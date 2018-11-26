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

    public const EXPORT_COMBINATION = 'export combination <combinationHash>';
    public const EXPORT_MOD = 'export mod <modName>';
    public const EXPORT_MOD_DEPENDENCIES = 'export mod <modName> with-dependencies';
    public const EXPORT_MOD_META = 'export mod <modName> meta';
    public const EXPORT_MOD_STEP = 'export mod <modName> step <step>';
    public const EXPORT_PREPARE = 'export prepare';

    public const LIST = 'list';

    public const REDUCE_COMBINATION = 'reduce combination <combinationHash>';
    public const REDUCE_MOD = 'reduce mod <modName>';

    public const RENDER_ICON = 'render icon <iconHash>';
    public const RENDER_MOD_ICONS = 'render mod-icons <modName>';

    public const UPDATE_DEPENDENCIES = 'update dependencies';
    public const UPDATE_LIST = 'update list';
    public const UPDATE_ORDER = 'update order';
}
