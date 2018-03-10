<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Command;

use FactorioItemBrowser\Export\I18n\Translator;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

/**
 * A command for testing purposes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TestCommand implements CommandInterface
{
    /**
     * The translator.
     * @var Translator
     */
    protected $translator;

    /**
     * TestCommand constructor.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Invokes the command.
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $this->translator->setEnabledModNames(['base']);

        $l = new LocalisedString();
        $this->translator->addTranslations($l, 'name', ['item-description.copper-cable'], '');

        var_dump($l);
    }
}