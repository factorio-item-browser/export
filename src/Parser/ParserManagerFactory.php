<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Parser;

use FactorioItemBrowser\Export\I18n\Translator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the parser manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParserManagerFactory implements FactoryInterface
{
    /**
     * The parser classes to use.
     */
    const PARSER_CLASSES = [
        ItemParser::class,
        RecipeParser::class,
        IconParser::class,
    ];

    /**
     * Creates the parser manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ParserManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var Translator $translator */
        $translator = $container->get(Translator::class);

        $parsers = [];
        foreach (self::PARSER_CLASSES as $parserClass) {
            $parsers[] = $container->get($parserClass);
        }

        return new ParserManager($translator, $parsers);
    }
}