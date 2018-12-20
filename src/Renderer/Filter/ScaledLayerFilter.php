<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer\Filter;

use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use Imagine\Filter\FilterInterface;
use Imagine\Filter\ImagineAware;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Image\PointInterface;

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
     * @param ImageInterface $layerImage
     * @return ImageInterface
     */
    public function apply(ImageInterface $layerImage)
    {
        $layerImage = $this->scaleLayer($layerImage);
        $layerImage = $this->offsetLayer($layerImage);
        $layerImage = $this->cropLayer($layerImage);
        return $layerImage;
    }

    /**
     * Scales the layer to the correct size.
     * @param ImageInterface $layerImage
     * @return ImageInterface
     */
    protected function scaleLayer(ImageInterface $layerImage): ImageInterface
    {
        $layerScale = $this->layer->getScale();
        if ($layerScale !== 1.) {
            $layerImage->resize($layerImage->getSize()->scale($layerScale));
        }
        return $layerImage;
    }

    /**
     * Offsets the layer on a temporary image.
     * @param ImageInterface $layerImage
     * @return ImageInterface
     */
    protected function offsetLayer(ImageInterface $layerImage): ImageInterface
    {
        $temporaryImage = $this->createTemporaryImage($layerImage);
        $drawPoint = $this->calculateDrawPoint($temporaryImage, $layerImage);
        $temporaryImage->paste($layerImage, $drawPoint);
        return $temporaryImage;
    }

    /**
     * Crops the image to the desired size.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    protected function cropLayer(ImageInterface $image): ImageInterface
    {
        $imageSize = $image->getSize();

        $size = new Box($this->size, $this->size);
        $point = new Point(
            (int) round(($imageSize->getWidth() - $this->size) / 2),
            (int) round(($imageSize->getHeight() - $this->size) / 2)
        );
        return $image->crop($point, $size);
    }

    /**
     * Creates the temporary image to draw the offset layer on.
     * @param ImageInterface $layerImage
     * @return ImageInterface
     */
    protected function createTemporaryImage(ImageInterface $layerImage): ImageInterface
    {
        $newSize = $layerImage->getSize()->scale(2)->increase($this->size);
        return $this->getImagine()->create($newSize, $layerImage->palette()->color(0xFFFFFF, 0));
    }

    /**
     * Calculates the point where to draw the layer onto the temporary image.
     * @param ImageInterface $temporaryImage
     * @param ImageInterface $layerImage
     * @return PointInterface
     */
    protected function calculateDrawPoint(ImageInterface $temporaryImage, ImageInterface $layerImage): PointInterface
    {
        $temporarySize = $temporaryImage->getSize();
        $layerSize = $layerImage->getSize();

        return new Point(
            $this->calculateDrawPosition(
                $temporarySize->getWidth(),
                $layerSize->getWidth(),
                $this->layer->getOffsetX()
            ),
            $this->calculateDrawPosition(
                $temporarySize->getHeight(),
                $layerSize->getHeight(),
                $this->layer->getOffsetY()
            )
        );
    }

    /**
     * Calculates the position of the layer in the temporary image of one coordinate.
     * @param int $temporarySize
     * @param int $layerSize
     * @param int $offset
     * @return int
     */
    protected function calculateDrawPosition(int $temporarySize, int $layerSize, int $offset): int
    {
        $position = (int) round(($temporarySize - $layerSize) / 2 + $offset);
        return min(max($position, 0), $temporarySize - $layerSize);
    }
}
