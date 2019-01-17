<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer\Filter;

use Imagine\Filter\FilterInterface;
use Imagine\Image\Fill\FillInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\PointInterface;

/**
 * The filter for applying a tinted layer to an image.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TintedLayerFilter implements FilterInterface, FillInterface
{
    /**
     * The image to apply as new layer.
     * @var ImageInterface
     */
    protected $layerImage;

    /**
     * The color of the tint to apply.
     * @var ColorInterface
     */
    protected $tintColor;

    /**
     * The image being processed.
     * @var ImageInterface
     */
    protected $image;

    /**
     * Initializes the filter.
     * @param ImageInterface $layerImage
     * @param ColorInterface $tintColor
     */
    public function __construct(ImageInterface $layerImage, ColorInterface $tintColor)
    {
        $this->layerImage = $layerImage;
        $this->tintColor = $tintColor;
    }

    /**
     * Applies the filter to the specified image.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    public function apply(ImageInterface $image): ImageInterface
    {
        $this->image = $image;
        $this->image->fill($this);
        return $image;
    }

    /**
     * Returns the new color to be used for the specified color.
     * @param PointInterface $point
     * @return ColorInterface
     */
    public function getColor(PointInterface $point): ColorInterface
    {
        $imageColor = $this->image->getColorAt($point);
        $layerColor = $this->layerImage->getColorAt($point);

        return $this->image->palette()->color([
            $this->calculateComponent(ColorInterface::COLOR_RED, $layerColor, $imageColor),
            $this->calculateComponent(ColorInterface::COLOR_GREEN, $layerColor, $imageColor),
            $this->calculateComponent(ColorInterface::COLOR_BLUE, $layerColor, $imageColor),
        ], $this->calculateAlpha($layerColor, $imageColor));
    }

    /**
     * Calculates a color component of the new pixel.
     * @param string $component
     * @param ColorInterface $source
     * @param ColorInterface $dest
     * @return int
     */
    protected function calculateComponent(string $component, ColorInterface $source, ColorInterface $dest): int
    {
        $colorSource = (int) $source->getValue($component) / 255;
        $colorDest = (int) $dest->getValue($component) / 255;
        $colorTint = (int) $this->tintColor->getValue($component) / 255;

        $alphaSource = (int) $source->getAlpha() / 100;
        $alphaDest = (int) $dest->getAlpha() / 100;
        $alphaTint = (int) $this->tintColor->getAlpha() / 100;

        $newColor = $colorSource * $colorTint * $alphaSource
            + $colorDest * $alphaDest * (1 - $alphaSource * $alphaTint);

        return min((int) round($newColor * 255), 255);
    }

    /**
     * Calculates the alpha component of the new pixel.
     * @param ColorInterface $source
     * @param ColorInterface $dest
     * @return int
     */
    protected function calculateAlpha(ColorInterface $source, ColorInterface $dest): int
    {
        $alphaSource = (int) $source->getAlpha() / 100;
        $alphaDest = (int) $dest->getAlpha() / 100;
        $alphaTint = (int) $this->tintColor->getAlpha() / 100;

        $newAlpha = $alphaSource + $alphaDest * (1 - $alphaSource * $alphaTint);
        return min((int) round($newAlpha * 100), 100);
    }
}
