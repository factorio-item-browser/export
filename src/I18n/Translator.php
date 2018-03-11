<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\I18n;

use FactorioItemBrowser\Export\Mod\ModFileManager;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\Stdlib\ArrayUtils;

/**
 * The translator of the mods. Not a Zend-Translator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @todo Maybe cache the prepared translation to not always have to read and parse them again?
 */
class Translator
{
    /**
     * The regular expression used to detect references to other translations.
     */
    private const REGEXP_REFERENCE = '#__(.+?)__(.+?)__#';

    /**
     * The mod file manager.
     * @var ModFileManager
     */
    protected $modFileManager;

    /**
     * The translator used for the placeholders.
     * @var ZendTranslator
     */
    protected $placeholderTranslator;

    /**
     * The loaded translations of all mods. Keys are mod name, locale and the language key.
     * @var array|string[][][]
     */
    protected $allTranslations;

    /**
     * The translations of the currently active mods.
     * @var string[][]
     */
    protected $translations;

    /**
     * Initializes the translator.
     * @param ModFileManager $modFileManager
     * @param ZendTranslator $placeHolderTranslator
     */
    public function __construct(ModFileManager $modFileManager, ZendTranslator $placeHolderTranslator)
    {
        $this->modFileManager = $modFileManager;
        $this->placeholderTranslator = $placeHolderTranslator;

        $this->setEnabledModNames(['base']);
    }

    /**
     * Sets the mods to be enabled in the translator.
     * @param array|string[] $enabledModNames
     * @return $this
     */
    public function setEnabledModNames(array $enabledModNames)
    {
        $translations = [];
        foreach ($enabledModNames as $modName) {
            $mod = $this->modFileManager->getMod($modName);
            if ($mod instanceof Mod) {
                if (!isset($this->allTranslations[$mod->getName()])) {
                    $this->allTranslations[$mod->getName()] = $this->modFileManager->getLocaleData($mod);
                }
                $translations = ArrayUtils::merge($translations, $this->allTranslations[$mod->getName()]);
            }
        }
        $this->translations = $translations;
        return $this;
    }

    /**
     * Adds the translations to the specified entity.
     * @param LocalisedString $entity The entity to add the translations to.
     * @param string $type The type of string to translate.
     * @param string|array $localisedString The raw localised string to translate.
     * @param string|array $fallbackLocalisedString The fallback localised string to use.
     * @return $this
     */
    public function addTranslations(
        LocalisedString $entity,
        string $type,
        $localisedString,
        $fallbackLocalisedString
    ) {
        foreach (array_keys($this->allTranslations['base']) as $locale) {
            $this->placeholderTranslator
                ->setLocale($locale)
                ->setFallbackLocale('en');

            $value = $this->translate($type, $localisedString, $locale, true);
            if (strlen($value) === 0 && !empty($fallbackLocalisedString)) {
                $value = $this->translate($type, $fallbackLocalisedString, $locale, true);
            }
            if (strlen($value) > 0) {
                $entity->setTranslation($locale, $value);
            }
        }
        return $this;
    }

    /**
     * Translates the specified string.
     * @param string $localisedKey
     * @param string|array $localisedString
     * @param string $locale
     * @param bool $isFirstLevel
     * @return string
     */
    protected function translate(string $localisedKey, $localisedString, string $locale, bool $isFirstLevel): string
    {
        $result = '';
        $originalLocalisedString = $localisedString;
        if (is_string($localisedString)) {
            $result = $localisedString;
        } elseif (is_array($localisedString)) {
            $key = array_shift($localisedString);
            if (empty($key)) {
                $result = strval(array_shift($localisedString));
            } else {
                $parameters = [];
                $index = 1;
                foreach ($localisedString as $parameter) {
                    $parameters['__' . $index . '__'] =
                        $this->translate($localisedKey, $parameter, $locale, false);
                    ++$index;
                }

                $translation = $this->translations[$locale][$key] ?? '';
                if (count($parameters) === 0) {
                    $result = $translation;
                } else {
                    $result = str_replace(array_keys($parameters), array_values($parameters), $translation);
                }
            }
        }
        if (strlen($result) === 0 && $locale !== 'en' && !$isFirstLevel) {
            // Fall back to English if a translation is not available.
            $result = $this->translate($localisedKey, $originalLocalisedString, 'en', $isFirstLevel);
        }
        return trim($this->resolveReferences($localisedKey, $result, $locale));
    }

    /**
     * Resolves any references which remain in the specified string.
     * @param string $key
     * @param string $string
     * @param string $locale
     * @return string
     */
    protected function resolveReferences(string $key, string $string, string $locale)
    {
        if (preg_match_all(self::REGEXP_REFERENCE, $string, $matches) > 0) {
            for ($i = 0; $i < count($matches[0]); ++$i) {
                $match = $matches[0][$i];
                $section = strtolower($matches[1][$i]);
                $reference = $matches[2][$i];

                $translationKey = $section . '-' . $key . '.' . $reference;
                if (isset($this->translations[$locale][$translationKey])) {
                    $string = str_replace($match, $this->translations[$locale][$translationKey], $string);
                } else {
                    $languageKey = 'placeholder ' . $section . ' ' . $reference;
                    $translatedReference = $this->placeholderTranslator->translate($languageKey);
                    if ($translatedReference !== $languageKey) {
                        $string = str_replace($match, $translatedReference, $string);
                    }
                }
            }
        }
        return $string;
    }
}