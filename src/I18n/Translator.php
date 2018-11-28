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
     * The fallback value of the locale.
     */
    protected const FALLBACK_LOCALE = 'en';

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
     * @var array|string[][]
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
     */
    public function addTranslationsToEntity(
        LocalisedString $entity,
        string $type,
        $localisedString,
        $fallbackLocalisedString = null
    ): void {
        foreach (array_keys($this->translations) as $locale) {
            $value = $this->translateWithFallback($locale, $type, $localisedString, $fallbackLocalisedString);
            if ($value !== '') {
                $entity->setTranslation($locale, $value);
            }
        }
    }

    /**
     * Translates the localised string with the specified fallback.
     * @param string $locale
     * @param string $type
     * @param string|array $localisedString
     * @param string|array|null $fallbackLocalisedString
     * @return string
     */
    protected function translateWithFallback(
        string $locale,
        string $type,
        $localisedString,
        $fallbackLocalisedString
    ): string {
        $result = $this->translate($locale, $type, $localisedString, 1);
        if ($result === '' && $fallbackLocalisedString !== null) {
            $result = $this->translate($locale, $type, $fallbackLocalisedString, 1);
        }
        return $result;
    }

    /**
     * Translates the specified string.
     * @param string $type
     * @param string|array $localisedString
     * @param string $locale
     * @param int $level
     * @return string
     */
    protected function translate(string $locale, string $type, $localisedString, int $level): string
    {
        $result = $this->translateLocalisedString($locale, $type, $localisedString, $level);
        if ($result === '' && $locale !== self::FALLBACK_LOCALE && $level > 1) {
            $result = $this->translateLocalisedString(self::FALLBACK_LOCALE, $type, $localisedString, $level);
        }
        return trim($this->resolveReferences($locale, $type, $result));
    }

    /**
     * Translates the localised string.
     * @param string $locale
     * @param string $type
     * @param string|array $localisedString
     * @param int $level
     * @return string
     */
    protected function translateLocalisedString(string $locale, string $type, $localisedString, int $level): string
    {
        $result = '';
        if (is_string($localisedString)) {
            $result = $localisedString;
        } elseif (is_array($localisedString)) {
            $name = array_shift($localisedString);
            if ($name === '') {
                $result = (string) array_shift($localisedString);
            } else {
                $result = $this->translations[$locale][$name] ?? '';
                if ($result !== '' && count($localisedString) > 0) {
                    $result = $this->translateParameters($locale, $type, $result, $localisedString, $level);
                }
            }
        }
        return $result;
    }

    /**
     * Translates the parameters in the specified string.
     * @param string $locale
     * @param string $type
     * @param string $string
     * @param array $parameters
     * @param int $level
     * @return string
     */
    protected function translateParameters(
        string $locale,
        string $type,
        string $string,
        array $parameters,
        int $level
    ): string {
        $translatedParameters = [];
        $index = 1;
        foreach ($parameters as $parameter) {
            $translatedParameters['__' . $index . '__'] = $this->translate($locale, $type, $parameter, $level + 1);
            ++$index;
        }

        return str_replace(array_keys($translatedParameters), array_values($translatedParameters), $string);
    }

    /**
     * Resolves any references which remained in the specified string.
     * @param string $locale
     * @param string $type
     * @param string $string
     * @return string
     */
    protected function resolveReferences(string $locale, string $type, string $string)
    {
        if (preg_match_all(self::REGEXP_REFERENCE, $string, $matches) > 0) {
            for ($i = 0; $i < count($matches[0]); ++$i) {
                $match = $matches[0][$i];
                $section = strtolower($matches[1][$i]);
                $name = $matches[2][$i];

                $translatedReference = $this->translateReference($locale, $section, $type, $name);
                if ($translatedReference !== null) {
                    $string = str_replace($match, $translatedReference, $string);
                }
            }
        }
        return $string;
    }

    /**
     * Translates a reference.
     * @param string $locale
     * @param string $section
     * @param string $type
     * @param string $name
     * @return string|null
     */
    protected function translateReference(string $locale, string $section, string $type, string $name): ?string
    {
        $translationKey = $section . '-' . $type . '.' . $name;
        if (isset($this->translations[$locale][$translationKey])) {
            $result = $this->translations[$locale][$translationKey];
        } else {
            $result = $this->translatePlaceholder($locale, $section, $name);
        }
        return $result;
    }

    /**
     * Translates a common placeholder provided by the export's language files.
     * @param string $locale
     * @param string $section
     * @param string $name
     * @return string|null
     */
    protected function translatePlaceholder(string $locale, string $section, string $name): ?string
    {
        $this->placeholderTranslator->setLocale($locale)
                                    ->setFallbackLocale(self::FALLBACK_LOCALE);

        $languageKey = implode(' ', ['placeholder', $section, $name]);
        $result = $this->placeholderTranslator->translate($languageKey);
        if ($result === $languageKey) {
            $result = null;
        }
        return $result;
    }
}
