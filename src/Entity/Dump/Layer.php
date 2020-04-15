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
    /**
     * The file used by the layer.
     * @var string
     */
    protected $file = '';

    /**
     * The size of the layer in pixel.
     * @var int
     */
    protected $size = 0;

    /**
     * The scale of the layer.
     * @var float
     */
    protected $scale = 1.;

    /**
     * The horizontal shift of the layer.
     * @var int
     */
    protected $shiftX = 0;

    /**
     * The vertical shift of the layer.
     * @var int
     */
    protected $shiftY = 0;

    /**
     * The red tint of the layer.
     * @var float
     */
    protected $tintRed = 1.;

    /**
     * The green tint of the layer.
     * @var float
     */
    protected $tintGreen = 1.;

    /**
     * The blue tint of the layer.
     * @var float
     */
    protected $tintBlue = 1.;

    /**
     * The alpha tint of the layer.
     * @var float
     */
    protected $tintAlpha = 1.;

    /**
     * Sets the file used by the layer.
     * @param string $file
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Returns the file used by the layer.
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Sets the size of the layer in pixel.
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Returns the size of the layer in pixel.
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Sets the scale of the layer.
     * @param float $scale
     * @return $this
     */
    public function setScale(float $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * Returns the scale of the layer.
     * @return float
     */
    public function getScale(): float
    {
        return $this->scale;
    }

    /**
     * Sets the horizontal shift of the layer.
     * @param int $shiftX
     * @return $this
     */
    public function setShiftX(int $shiftX): self
    {
        $this->shiftX = $shiftX;
        return $this;
    }

    /**
     * Returns the horizontal shift of the layer.
     * @return int
     */
    public function getShiftX(): int
    {
        return $this->shiftX;
    }

    /**
     * Sets the vertical shift of the layer.
     * @param int $shiftY
     * @return $this
     */
    public function setShiftY(int $shiftY): self
    {
        $this->shiftY = $shiftY;
        return $this;
    }

    /**
     * Returns the vertical shift of the layer.
     * @return int
     */
    public function getShiftY(): int
    {
        return $this->shiftY;
    }

    /**
     * Sets the red tint of the layer.
     * @param float $tintRed
     * @return $this
     */
    public function setTintRed(float $tintRed): self
    {
        $this->tintRed = $tintRed;
        return $this;
    }

    /**
     * Returns the red tint of the layer.
     * @return float
     */
    public function getTintRed(): float
    {
        return $this->tintRed;
    }

    /**
     * Sets the green tint of the layer.
     * @param float $tintGreen
     * @return $this
     */
    public function setTintGreen(float $tintGreen): self
    {
        $this->tintGreen = $tintGreen;
        return $this;
    }

    /**
     * Returns the green tint of the layer.
     * @return float
     */
    public function getTintGreen(): float
    {
        return $this->tintGreen;
    }

    /**
     * Sets the blue tint of the layer.
     * @param float $tintBlue
     * @return $this
     */
    public function setTintBlue(float $tintBlue): self
    {
        $this->tintBlue = $tintBlue;
        return $this;
    }

    /**
     * Returns the blue tint of the layer.
     * @return float
     */
    public function getTintBlue(): float
    {
        return $this->tintBlue;
    }

    /**
     * Sets the alpha tint of the layer.
     * @param float $tintAlpha
     * @return $this
     */
    public function setTintAlpha(float $tintAlpha): self
    {
        $this->tintAlpha = $tintAlpha;
        return $this;
    }

    /**
     * Returns the alpha tint of the layer.
     * @return float
     */
    public function getTintAlpha(): float
    {
        return $this->tintAlpha;
    }
}
