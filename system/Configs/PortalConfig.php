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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Configs;


use Configula\ConfigFactory;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Services\TemplateService;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class TemplateConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$portalConfig
 */
class PortalConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * PortalConfig constructor.
     * @param DefaultConfig $defaultConfig
     * @see TemplateService::__construct()
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();

        /**
         * Build portal options
         */
        $portalConfig = ["portal_options" => $this->config->get("portal_options", [])];
        $portalConfig = ConfigFactory::fromArray($this->getOptionsDefault())->mergeValues($portalConfig);

        /**
         * Finished
         */
        $this->configValues = $portalConfig;
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $htmlLang = strtolower(substr($this->config->get("language"), 0, 2));

        return [
            "portal_options" => [
                //DOM HTML attribute
                "s_html_lang" => $htmlLang,
                //CoreUI or CoreUI Pro
                "b_core_ui_pro" => false,
                //Default or dark layout
                "b_dark_layout" => false,
                //Sidebar folded in by default
                "b_sidebar_unfoldable" => false,
                //Any visitor can register themselves
                "b_allow_registration" => false,
                //When logging in, users can select the option to remain permanently logged on
                "b_allow_stay_logged_in" => true,
                //Array of Js files that should not be minimized and permanently integrated
                "a_non_minified_js" => [
                    "assets/vendors/@coreui/coreui/js/coreui.bundle.min.js"
                ],
                //Array of CSS files that should not be minimized and permanently integrated
                "a_non_minified_css" => [
                    "assets/vendors/@coreui/coreui/css/coreui.min.css",
                    "assets/vendors/@coreui/icons/css/brand.min.css",
                    "assets/vendors/@coreui/icons/css/flag.min.css",
                    "assets/vendors/@coreui/icons/css/free.min.css"
                ],
            ]
        ];
    }
}