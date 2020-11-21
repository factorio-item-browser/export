<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\OutputProcessor\DumpProcessor;

use FactorioItemBrowser\Export\Entity\Dump\Dump;

/**
 * The interface for the actual dump processors.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface DumpProcessorInterface
{
    /**
     * Returns the type of dump this processor handles.
     * @return string
     */
    public function getType(): string;

    /**
     * Processes the serialized dump, adding the content to the dump instance.
     * @param string $serializedDump
     * @param Dump $dump
     */
    public function process(string $serializedDump, Dump $dump): void;
}
