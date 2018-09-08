<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Color;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;

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
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the icon renderer.
     * @param ModRegistry $modRegistry
     * @param ModFileManager $modFileManager
     */
    public function __construct(ModRegistry $modRegistry, ModFileManager $modFileManager)
    {
        $this->modRegistry = $modRegistry;
        $this->modFileManager = $modFileManager;
    }

    /**
     * Renders the specified icon.
     * @param Icon $icon
     * @param int $size
     * @return string
     * @throws ExportException
     */
    public function render(Icon $icon, int $size): string
    {
        $image = $this->createTransparentLayer($icon->getSize());
        try {
            foreach ($icon->getLayers() as $layer) {
                $this->renderLayer($image, $layer, $icon->getSize());
            }
            $image = $this->resizeImage($image, $icon->getSize(), $size);
            $imageContent = $this->getImageContents($image);
        } finally {
            imagedestroy($image);
        }
        return $imageContent;
    }

    /**
     * Creates and returns a transparent layer ready to be drawn on.
     * @param int $size
     * @return resource
     * @throws ExportException
     */
    protected function createTransparentLayer(int $size)
    {
        $layer = $this->verifyImageResource(imagecreatetruecolor($size, $size));
        $transparent = imagecolorallocatealpha($layer, 255, 255, 255, 127);
        imagealphablending($layer, false);
        imagefilledrectangle($layer, 0, 0, $size, $size, $transparent);
        return $layer;
    }

    /**
     * Renders the specified layer to the image.
     * @param resource $image
     * @param Layer $layer
     * @param int $size
     * @return $this
     * @throws ExportException
     */
    protected function renderLayer($image, Layer $layer, $size)
    {
        $layerImage = $this->createdScaledLayerImage($layer, $size);
        try {
            $this->applyTintedLayer($image, $layerImage, $layer->getTintColor(), $size);
        } finally {
            imagedestroy($layerImage);
        }
        return $this;
    }

    /**
     * Creates the scaled image of the specified layer.
     * @param Layer $layer
     * @param int $size
     * @return resource
     * @throws ExportException
     */
    protected function createdScaledLayerImage(Layer $layer, int $size)
    {
        $image = $this->createTransparentLayer($size);

        $layerImageContents = $this->loadLayerImage($layer->getFileName());
        $layerImage = $this->verifyImageResource(imagecreatefromstring($layerImageContents));
        $layerWidth = imagesx($layerImage);
        $layerHeight = imagesy($layerImage);

        $scale = $layer->getScale();
        $x = ($size - $layerWidth * $scale) / 2 + $layer->getOffsetX();
        $y = ($size - $layerHeight * $scale) / 2 + $layer->getOffsetY();

        imagealphablending($image, true);
        imagecopyresampled(
            $image,
            $layerImage,
            (int) $x,
            (int) $y,
            0,
            0,
            (int) ($layerWidth * $scale),
            (int) ($layerHeight * $scale),
            $layerWidth,
            $layerHeight
        );

        return $image;
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

        $mod = $this->modRegistry->get($match[1]);
        if (!$mod instanceof Mod) {
            throw new ExportException('Mod not known: ' . $match[1]);
        }
        return $this->modFileManager->getFile($mod, $match[2]);
    }


    /**
     * Applies the layer image with the specified tint color.
     * @param resource $image
     * @param resource $layerImage
     * @param Color $tnt
     * @param int $size
     * @return $this
     */
    protected function applyTintedLayer($image, $layerImage, Color $tnt, int $size)
    {
        imagealphablending($image, false);
        for ($x = 0; $x < $size; ++$x) {
            for ($y = 0; $y < $size; ++$y) {
                $dst = $this->createColorFromPixel($image, $x, $y);
                $src = $this->createColorFromPixel($layerImage, $x, $y);

                $calculatedColor = new Color();
                $calculatedColor
                    ->setRed($src->getRed() * $tnt->getRed() * $src->getAlpha()
                        + $dst->getRed() * $dst->getAlpha() * (1 - $src->getAlpha() * $tnt->getAlpha()))
                    ->setGreen($src->getGreen() * $tnt->getGreen() * $src->getAlpha()
                        + $dst->getGreen() * $dst->getAlpha() * (1 - $src->getAlpha() * $tnt->getAlpha()))
                    ->setBlue($src->getBlue() * $tnt->getBlue() * $src->getAlpha()
                        + $dst->getBlue() * $dst->getAlpha() * (1 - $src->getAlpha() * $tnt->getAlpha()))
                    ->setAlpha($src->getAlpha() + $dst->getAlpha() * (1 - $src->getAlpha() * $tnt->getAlpha()));

                $color = imagecolorallocatealpha(
                    $image,
                    max(min(intval($calculatedColor->getRed(255)), 255), 0),
                    max(min(intval($calculatedColor->getGreen(255)), 255), 0),
                    max(min(intval($calculatedColor->getBlue(255)), 255), 0),
                    max(min(intval($calculatedColor->getAlpha(-127)), 127), 0)
                );

                imagesetpixel($image, $x, $y, $color);
            }
        }
        return $this;
    }

    /**
     * Creates and returns a color instance from the specified pixel of the image.
     * @param resource $image
     * @param int $x
     * @param int $y
     * @return Color
     */
    protected function createColorFromPixel($image, int $x, int $y): Color
    {
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        $result = new Color();
        $result
            ->setRed($color['red'], 255)
            ->setGreen($color['green'], 255)
            ->setBlue($color['blue'], 255)
            ->setAlpha($color['alpha'], -127);

        return $result;
    }

    /**
     * Resize the image to the specified size.
     * @param resource $image
     * @param int $originalSize
     * @param int $requestedSize
     * @return resource
     * @throws ExportException
     */
    protected function resizeImage($image, int $originalSize, int $requestedSize)
    {
        if ($originalSize !== $requestedSize) {
            $newImage = $this->createTransparentLayer($requestedSize);
            imagealphablending($newImage, true);
            imagecopyresampled(
                $newImage,
                $image,
                0,
                0,
                0,
                0,
                $requestedSize,
                $requestedSize,
                $originalSize,
                $originalSize
            );
            imagedestroy($image);
            $image = $newImage;
        }
        return $image;
    }

    /**
     * Returns the contents of the specified image.
     * @param resource $image
     * @return string
     */
    protected function getImageContents($image): string
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagepng($image);
        return (string) ob_get_clean();
    }

    /**
     * Verifies that the passed parameter is indeed a resource.
     * @param resource|false $resource
     * @return resource
     * @throws ExportException
     */
    protected function verifyImageResource($resource)
    {
        if (!is_resource($resource)) {
            throw new ExportException('Rendering error: Missing resource.');
        }
        return $resource;
    }
}
