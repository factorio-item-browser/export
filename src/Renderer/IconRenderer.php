<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\Renderer;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\Icon;
use FactorioItemBrowser\ExportData\Entity\Icon\Color;
use FactorioItemBrowser\ExportData\Entity\Icon\Layer;
use FactorioItemBrowser\ExportData\Entity\Mod;

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
    private const REGEXP_LAYER_IMAGE = '#^__(.*)__/(.*)$#';

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * Initializes the icon renderer.
     * @param ModFileManager $modFileManager
     */
    public function __construct(ModFileManager $modFileManager)
    {
        $this->modFileManager = $modFileManager;
    }

    /**
     * Renders the specified icon.
     * @param Icon $icon
     * @param int $width
     * @param int $height
     * @return string
     * @throws ExportException
     */
    public function render(Icon $icon, int $width, int $height): string
    {
        $image = $this->createTransparentLayer($width, $height);
        try {
            foreach ($icon->getLayers() as $layer) {
                $this->renderLayer($image, $layer, $width, $height);
            }
            $imageContent = $this->getImageContents($image);
        } finally {
            imagedestroy($image);
        }
        return $imageContent;
    }

    /**
     * Creates and returns a transparent layer ready to be drawn on.
     * @param int $width
     * @param int $height
     * @return resource
     */
    protected function createTransparentLayer(int $width, int $height)
    {
        $layer = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($layer, 255, 255, 255, 127);
        imagealphablending($layer, false);
        imagefilledrectangle($layer, 0, 0, $width, $height, $transparent);
        return $layer;
    }

    /**
     * Renders the specified layer to the image.
     * @param resource $image
     * @param Layer $layer
     * @param int $width
     * @param int $height
     * @return $this
     * @throws ExportException
     */
    protected function renderLayer($image, Layer $layer, int $width, int $height)
    {
        $layerImage = $this->createdScaledLayerImage($layer, $width, $height);
        try {
            $this->applyTintedLayer($image, $layerImage, $layer->getTintColor(), $width, $height);
        } finally {
            imagedestroy($layerImage);
        }
        return $this;
    }

    /**
     * Creates the scaled image of the specified layer.
     * @param Layer $layer
     * @param int $width
     * @param int $height
     * @return resource
     * @throws ExportException
     */
    protected function createdScaledLayerImage(Layer $layer, int $width, int $height)
    {
        $image = $this->createTransparentLayer($width, $height);

        $layerImageContents = $this->loadLayerImage($layer->getFileName());
        $layerImage = imagecreatefromstring($layerImageContents);
        $layerWidth = imagesx($layerImage);
        $layerHeight = imagesy($layerImage);

        $scale = $layer->getScale();
        $x = ($width - $layerWidth * $scale) / 2 + $layer->getOffsetX();
        $y = ($height - $height * $scale) / 2 + $layer->getOffsetY();

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
            (int) $layerWidth,
            (int) $layerHeight
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
        if (!preg_match(self::REGEXP_LAYER_IMAGE, $layerFileName, $match)) {
            throw new ExportException('Unable to understand image file name: ' . $layerFileName);
        }
        $mod = $this->modFileManager->getMod($match[1]);
        if (!$mod instanceof Mod) {
            throw new ExportException('Mod not known: ' . $match[1]);
        }
        return $this->modFileManager->getFileContents($mod, $match[2]);
    }


    /**
     * Applies the layer image with the specified tint color.
     * @param resource $image
     * @param resource $layerImage
     * @param Color $tnt
     * @param int $width
     * @param int $height
     * @return $this
     */
    protected function applyTintedLayer($image, $layerImage, Color $tnt, int $width, int $height)
    {
        imagealphablending($image, false);
        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
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
        return ob_get_clean();
    }
}