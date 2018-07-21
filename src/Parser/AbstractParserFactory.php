<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\I18n\Translator;
use Interop\Container\ContainerInterface;

/**
 * The abstract factory of the parsers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AbstractParserFactory
{
    /**
     * Creates the parser.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return AbstractParser
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var Translator $translator */
        $translator = $container->get(Translator::class);

        return new $requestedName($translator);
    }
}
