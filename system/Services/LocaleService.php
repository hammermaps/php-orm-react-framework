<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Services;


use Exceptions\LocaleException;
use Gettext\Loader\PoLoader;
use Gettext\Translations;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LocaleService
 * @package Services
 */
class LocaleService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var string
     */
    const DOMAIN = "messages";

    /**
     * @var Translations
     */
    private Translations $modTranslations;

    /**
     * @var Translations
     */
    private Translations $sysTranslations;

    /**
     * @var string
     */
    private string $sysLocaleDir;

    /**
     * @var string
     */
    private string $modLocaleDir;

    /**
     * @var string
     */
    private string $languageCode;

    /**
     * @var PoLoader
     */
    private PoLoader $poLoader;

    /**
     * LocaleService constructor.
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        DeclarationHelper::init(null, null, "gettext",
            LocaleException::class)->functionExists();

        $config = $moduleManager->getConfig();
        $baseDir = $config->get("base_dir");
        $this->languageCode = $config->get("language");

        $this->poLoader = new PoLoader();

        $this->sysLocaleDir = sprintf("%s/locale", $baseDir);
        $this->modLocaleDir = sprintf("%s/locale", $moduleManager->getModuleBaseDir());
        if(!FileHelper::init($this->modLocaleDir)->isReadable()){
            $this->modLocaleDir = $this->sysLocaleDir;
        }

        /**
         * @see LocaleService::getModuleTranslations()
         * Module translation
         */
        $this->modTranslations = $this->loadModuleTranslations($this->getLanguageCode());

        /**
         * @see LocaleService::getSystemTranslations()
         * System translation
         */
        $this->sysTranslations = $this->getTranslations($this->getLanguageCode());

        $this->registerTranslationFunctions();
    }

    /**
     * Register global helper functions for translations.
     */
    private function registerTranslationFunctions(): void
    {
        if (!function_exists('__')) {
            function __(string $original): string
            {
                return LocaleService::translate($original);
            }
        }

        if (!function_exists('n__')) {
            function n__(string $original, string $plural, int $value): string
            {
                return LocaleService::translatePlural($original, $plural, $value);
            }
        }
    }

    /**
     * Translates a singular message using the merged system and module translations.
     *
     * @param string $original
     * @return string
     */
    public static function translate(string $original): string
    {
        $translations = self::$instance instanceof self
            ? self::$instance->sysTranslations
            : Translations::create();

        $translation = $translations->find(null, $original);

        return $translation ? $translation->getTranslation() : $original;
    }

    /**
     * Translates a plural message using the merged system and module translations.
     *
     * @param string $original
     * @param string $plural
     * @param int $value
     * @return string
     */
    public static function translatePlural(string $original, string $plural, int $value): string
    {
        $translations = self::$instance instanceof self
            ? self::$instance->sysTranslations
            : Translations::create();

        $translation = $translations->find(null, $original);

        if (!$translation || $translation->getPlural() === null) {
            return $value === 1 ? $original : $plural;
        }

        return $translation->getPluralTranslation($value - 1) ?? ($value === 1 ? $original : $plural);
    }

    /**
     * Contains the global functions for the Twig extension il8n for translation in template files.
     * For translation in twig files with {% trans %} text {% endtrans %}.
     * Important: Here only language files of the respective module are accessed!
     * @return Translations
     */
    public final function getModuleTranslations(): Translations
    {
        return $this->modTranslations;
    }

    /**
     * Contains the global function __() for translations.
     * Important: Here files of the current module and the system are accessed!
     * @return Translations
     */
    public final function getSystemTranslations(): Translations
    {
        return $this->sysTranslations;
    }

    /**
     * @param string $localeCode
     */
    public final function setLanguage(string $localeCode): void
    {
        $this->languageCode = $localeCode;
        $this->modTranslations = $this->loadModuleTranslations($localeCode);
        $this->sysTranslations = $this->getTranslations($localeCode);
    }

    /**
     * @param string $localeCode
     * @return Translations
     */
    private function getSystemTranslationsByLocale(string $localeCode): Translations
    {
        $poFile = sprintf("%s/%s/LC_%s/%s.po", $this->sysLocaleDir,
            $localeCode, strtoupper(self::DOMAIN), self::DOMAIN);

        if(!FileHelper::init($poFile)->isReadable()){
            return Translations::create(self::DOMAIN, $localeCode);
        }

        return $this->poLoader->loadFile($poFile);
    }

    /**
     * @param string $localeCode
     * @return Translations
     */
    private function loadModuleTranslations(string $localeCode): Translations
    {
        $poFile = sprintf("%s/%s/LC_%s/%s.po", $this->modLocaleDir,
            $localeCode, strtoupper(self::DOMAIN), self::DOMAIN);

        if(!FileHelper::init($poFile)->isReadable()){
            return Translations::create(self::DOMAIN, $localeCode);
        }

        return $this->poLoader->loadFile($poFile);
    }

    /**
     * @param string|null $localeCode
     * @return Translations
     */
    public function getTranslations(?string $localeCode = null): Translations
    {
        $localeCode = is_null($localeCode) ? $this->getLanguageCode() : $localeCode;
        return $this->getSystemTranslationsByLocale($localeCode)->mergeWith($this->loadModuleTranslations($localeCode));
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
}
