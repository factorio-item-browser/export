<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Entity\Dump;

/**
 * The icon layer written to the dumped data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Layer
{
    public string $file = '';
    public int $size = 0;
    public float $scale = 1.;
    public int $shiftX = 0;
    public int $shiftY = 0;
    public float $tintRed = 1.;
    public float $tintGreen = 1.;
    public float $tintBlue = 1.;
    public float $tintAlpha = 1.;
}
