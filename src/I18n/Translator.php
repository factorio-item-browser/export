<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Export\I18n;

use FactorioItemBrowser\Export\Exception\ExportException;
use FactorioItemBrowser\Export\Mod\LocaleReader;
use FactorioItemBrowser\ExportData\Entity\LocalisedString;
use FactorioItemBrowser\ExportData\Entity\Mod;
use FactorioItemBrowser\ExportData\Registry\ModRegistry;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\Stdlib\ArrayUtils;

/**
 * The translator of the mods. Not a Zend-Translator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Translator
{
    /**
     * The regular expression used to detect references to other translations.
     */
    protected const REGEXP_REFERENCE = '#__(.+?)__(.+?)__#';

    /**
     * The locale reader.
     * @var LocaleReader
     */
    protected $localeReader;

    /**
     * The mod registry.
     * @var ModRegistry
     */
    protected $modRegistry;

    /**
     * The translator used for the placeholders.
     * @var ZendTranslator
     */
    protected $placeholderTranslator;

    /**
     * The translations of the currently active mods.
     * @var string[][]
     */
    protected $translations = [];

    /**
     * Initializes the translator.
     * @param LocaleReader $localeReader
     * @param ModRegistry $modRegistry
     * @param ZendTranslator $placeholderTranslator
     */
    public function __construct(
        LocaleReader $localeReader,
        ModRegistry $modRegistry,
        ZendTranslator $placeholderTranslator
    ) {
        $this->localeReader = $localeReader;
        $this->modRegistry = $modRegistry;
        $this->placeholderTranslator = $placeholderTranslator;
    }

    /**
     * Loads the translations from the specified mod names.
     * @param array|string[] $modNames
     * @throws ExportException
     */
    public function loadFromModNames(array $modNames): void
    {
        $this->translations = [];
        foreach ($modNames as $modName) {
            $mod = $this->modRegistry->get($modName);
            if ($mod instanceof Mod) {
                $this->translations = ArrayUtils::merge(
                    $this->translations,
                    $this->localeReader->read($mod)
                );
            }
        }
    }

    /**
     * Adds the translations to the specified entity.
     * @param LocalisedString $entity The entity to add the translations to.
     * @param string $type The type of string to translate.
     * @param string|array $localisedString The raw localised string to translate.
     * @param string|array|null $fallbackLocalisedString The fallback localised string to use.
     * @return $this
     */
    public function addTranslationsToEntity(
        LocalisedString $entity,
        string $type,
        $localisedString,
        $fallbackLocalisedString = null
    ) {
        foreach (array_keys($this->translations['base'] ?? []) as $locale) {
            $this->placeholderTranslator
                 ->setLocale($locale)
                 ->setFallbackLocale('en');

            $value = $this->translate($type, $localisedString, $locale, true);
            if (strlen($value) === 0 && $fallbackLocalisedString !== null) {
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
            if ($key === '') {
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
