<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\NewModFileManager;
use FactorioItemBrowser\Export\Renderer\Filter\ScaledLayerFilter;
use FactorioItemBrowser\Export\Renderer\Filter\TintedLayerFilter;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Color;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use Imagine\Filter\FilterInterface;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\RGB;

/**
 * The class rendering the layered icons to PNG images.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconRenderer
{
    /**
     * The regular expression to recognize the image files.
     */
    protected const REGEXP_LAYER_IMAGE = '#^__(.*)__/(.*)$#';

    /**
     * The imagine instance.
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * The mod file manager.
     * @var NewModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the icon renderer.
     * @param ImagineInterface $imagine
     * @param NewModFileManager $modFileManager
     */
    public function __construct(ImagineInterface $imagine, NewModFileManager $modFileManager)
    {
        $this->imagine = $imagine;
        $this->modFileManager = $modFileManager;
    }

    /**
     * Renders the specified icon.
     * @param Icon $icon
     * @return string
     * @throws ExportException
     */
    public function render(Icon $icon): string
    {
        $image = $this->createImage($icon->getSize());
        foreach ($icon->getLayers() as $layer) {
            $image = $this->renderLayer($image, $layer, $icon->getSize());
        }

        $this->resizeImage($image, $icon->getRenderedSize());
        return $image->get('png');
    }

    /**
     * Creates a new transparent image with the specified size.
     * @param int $size
     * @return ImageInterface
     */
    protected function createImage(int $size): ImageInterface
    {
        return $this->imagine->create(new Box($size, $size), (new RGB())->color(0xFFFFFF, 0));
    }

    /**
     * Renders the specified layer to the image.
     * @param ImageInterface $image
     * @param Layer $layer
     * @param int $size
     * @return ImageInterface
     * @throws ExportException
     */
    protected function renderLayer(ImageInterface $image, Layer $layer, int $size): ImageInterface
    {
        $scaledLayerFilter = $this->createScaledLayerFilter($layer, $size);
        $layerImage = $scaledLayerFilter->apply($this->createLayerImage($layer));
        $tintedLayerFilter = $this->createTintedLayerFilter($layer, $layerImage);
        return $tintedLayerFilter->apply($image);
    }

    /**
     * Creates an image instance of the specified layer file.
     * @param Layer $layer
     * @return ImageInterface
     * @throws ExportException
     */
    protected function createLayerImage(Layer $layer): ImageInterface
    {
        $content = $this->loadLayerImage($layer->getFileName());
        return $this->imagine->load($content);
    }

    /**
     * Loads the specified layer image file.
     * @param string $layerFileName
     * @return string
     * @throws ExportException
     */
    protected function loadLayerImage(string $layerFileName): string
    {
        $count = (int) preg_match(self::REGEXP_LAYER_IMAGE, $layerFileName, $match);
        if ($count === 0) {
            throw new ExportException('Unable to understand image file name: ' . $layerFileName);
        }
        return $this->modFileManager->readFile($match[1], $match[2]);
    }

    /**
     * Creates the filter to scale and offset the layer.
     * @param Layer $layer
     * @param int $size
     * @return FilterInterface
     */
    protected function createScaledLayerFilter(Layer $layer, int $size): FilterInterface
    {
        $filter = new ScaledLayerFilter($layer, $size);
        $filter->setImagine($this->imagine);
        return $filter;
    }

    /**
     * Creates the filter to apply the tinted layer.
     * @param Layer $layer
     * @param ImageInterface $layerImage
     * @return FilterInterface
     */
    protected function createTintedLayerFilter(Layer $layer, ImageInterface $layerImage): FilterInterface
    {
        return new TintedLayerFilter($layerImage, $this->convertColor($layer->getTint()));
    }

    /**
     * Converts the specified color to an Imagine instance.
     * @param Color $color
     * @return ColorInterface
     */
    protected function convertColor(Color $color): ColorInterface
    {
        return (new RGB())->color([
            (int) round($color->getRed(255)),
            (int) round($color->getGreen(255)),
            (int) round($color->getBlue(255)),
        ], (int) round($color->getAlpha(100)));
    }

    /**
     * Resizes the image if it does not already have the desired size.
     * @param ImageInterface $image
     * @param int $size
     */
    protected function resizeImage(ImageInterface $image, int $size): void
    {
        if ($image->getSize()->getWidth() !== $size || $image->getSize()->getHeight() !== $size) {
            $image->resize(new Box($size, $size));
        }
    }
}
