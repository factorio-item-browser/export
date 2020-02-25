<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer\Filter;

use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use Imagine\Filter\FilterInterface;
use Imagine\Filter\ImagineAware;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

/**
 * The filter for scaling and offsetting a layer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ScaledLayerFilter extends ImagineAware implements FilterInterface
{
    /**
     * The layer information.
     * @var Layer
     */
    protected $layer;

    /**
     * The size the layer should have in the end.
     * @var int
     */
    protected $size;

    /**
     * Initializes the filter.
     * @param Layer $layer
     * @param int $size
     */
    public function __construct(Layer $layer, int $size)
    {
        $this->layer = $layer;
        $this->size = $size;
    }

    /**
     * Applies scheduled transformation to an ImageInterface instance.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    public function apply(ImageInterface $image): ImageInterface
    {
        $image = $this->scaleLayer($image);
        $image = $this->offsetLayer($image);
        $image = $this->adjustLayer($image);
        return $image;
    }

    /**
     * Creates the temporary image to draw the offset layer on.
     * @param ImageInterface $image
     * @param int $size
     * @return ImageInterface
     */
    protected function createTemporaryImage(ImageInterface $image, int $size): ImageInterface
    {
        return $this->getImagine()->create(new Box($size, $size), $image->palette()->color(0xFFFFFF, 0));
    }

    /**
     * Scales the layer.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    protected function scaleLayer(ImageInterface $image): ImageInterface
    {
        $scale = $this->layer->getScale();
        if ($scale !== 1.) {
            $scaledSize = (int) floor($image->getSize()->getWidth() * $scale);
            $image->resize(new Box($scaledSize, $scaledSize));
        }
        return $image;
    }

    /**
     * Offsets the layer on a temporary image. The returned layer is three times the original size.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    protected function offsetLayer(ImageInterface $image): ImageInterface
    {
        $offset = $this->layer->getOffset();
        if ($offset->getX() === 0 && $offset->getY() === 0) {
            return $image;
        }

        $size = $image->getSize()->getWidth();
        $left = min(max($size + $offset->getX(), 0), 2 * $size);
        $top = min(max($size + $offset->getY(), 0), 2 * $size);

        $offsetImage = $this->createTemporaryImage($image, 3 * $size);
        $offsetImage->paste($image, new Point($left, $top));
        return $offsetImage;
    }

    /**
     * Adjusts the layer to the desired size by cropping or extending it.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    protected function adjustLayer(ImageInterface $image): ImageInterface
    {
        $imageSize = $image->getSize()->getWidth();
        if ($imageSize > $this->size) {
            $image = $this->cropLayer($image, $this->size);
        } elseif ($imageSize < $this->size) {
            $image = $this->extendLayer($image, $this->size);
        }
        return $image;
    }

    /**
     * Crops the layer to the specified size. The size MUST be smaller as the layer's one.
     * @param ImageInterface $image
     * @param int $size
     * @return ImageInterface
     */
    protected function cropLayer(ImageInterface $image, int $size): ImageInterface
    {
        $position = (int) floor(($image->getSize()->getWidth() - $size) / 2);
        $image->crop(new Point($position, $position), new Box($size, $size));
        return $image;
    }

    /**
     * Extends the layer to the specified size. The size MUST be larger than the layer's one.
     * @param ImageInterface $image
     * @param int $size
     * @return ImageInterface
     */
    protected function extendLayer(ImageInterface $image, int $size): ImageInterface
    {
        $position = (int) floor(($size - $image->getSize()->getWidth()) / 2);
        $result = $this->createTemporaryImage($image, $size);
        $result->paste($image, new Point($position, $position));
        return $result;
    }
}
