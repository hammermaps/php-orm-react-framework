<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Controllers\PublicController;
use Controllers\RestrictedController;
use Helpers\FileHelper;
use Managers\ModuleManager;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ReactHandler
 * @package Handlers
 */
class ReactHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var string
     */
    private $moduleBaseDir = "";

    /**
     * @var string
     */
    private $moduleBaseUrl = "";

    /**
     * @var string
     */
    private $moduleControllerShortName = "";

    /**
     * @var FileHelper|null
     */
    private $systemControllerEntryPointFile;

    /**
     * @var ConfigValues
     */
    private $systemControllerEntryPointConfig;

    /**
     * @var array
     */
    private $systemControllerEntryPoints = [];

    /**
     * @var FileHelper|null
     */
    private $moduleControllerEntryPointFile;

    /**
     * @var ConfigValues
     */
    private $moduleControllerEntryPointConfig;

    /**
     * @var array
     */
    private $moduleControllerJsEntryPoints = [];

    /**
     * @var array
     */
    private $moduleControllerCssEntryPoints = [];

    /**
     * @var string
     */
    private $moduleControllerAction = "";

    /**
     * ReactHandler constructor.
     * @param AbstractBase $controllerInstance
     * @param ModuleManager $moduleManager
     */
    private final function __construct(AbstractBase $controllerInstance, ModuleManager $moduleManager)
    {
        $this->baseDir = $controllerInstance->getBaseDir();
        $this->systemControllerEntryPointFile = FileHelper::init(sprintf("%s/assets/js/react/entrypoints.json", $this->baseDir));
        $this->systemControllerEntryPointConfig = $this->systemControllerEntryPointFile->isReadable()
            ? new ConfigValues(json_decode($this->systemControllerEntryPointFile->getContents("[]"), true)) : new ConfigValues([]);

        if($controllerInstance instanceof RestrictedController){
            $this->systemControllerEntryPoints = $this->systemControllerEntryPointConfig->get("entrypoints.RestrictedController.js", []);
        }elseif($controllerInstance instanceof PublicController){
            $this->systemControllerEntryPoints = $this->systemControllerEntryPointConfig->get("entrypoints.PublicController.js", []);
        }

        if(!empty($this->systemControllerEntryPoints)){
            $this->systemControllerEntryPoints = array_map([$this, "addRelativeBaseAssetJsReactPath"], $this->systemControllerEntryPoints);
        }

        $this->moduleBaseDir = $controllerInstance->getModuleBaseDir();
        $this->moduleBaseUrl = $moduleManager->getBaseUrl();
        $this->moduleControllerShortName = $moduleManager->getControllerShortName();
        $this->moduleControllerEntryPointFile = FileHelper::init(sprintf("%s/views/entrypoints.json", $this->moduleBaseDir));
        $this->moduleControllerEntryPointConfig = $this->moduleControllerEntryPointFile->isReadable()
            ? new ConfigValues(json_decode($this->moduleControllerEntryPointFile->getContents("{}"), true)) : new ConfigValues([]);
    }

    /**
     * @return bool
     */
    public function hasModuleEntryPoint()
    {
        return $this->moduleControllerEntryPointFile->isReadable();
    }

    /**
     * @param $file
     * @return string
     */
    public function addRelativeBaseAssetJsReactPath($file)
    {
        return sprintf("assets/js/react%s", $file);
    }

    /**
     * @param $file
     * @return string
     */
    public function addRelativeModuleViewsPath($file)
    {
        return sprintf("%s/views%s", substr($this->moduleBaseUrl, 1), $file);
    }

    /**
     * @param AbstractBase $controllerInstance
     * @param ModuleManager $moduleManager
     * @return ReactHandler|null
     */
    public static final function init(AbstractBase $controllerInstance, ModuleManager $moduleManager)
    {
        if (is_null(self::$instance) || serialize(get_class($controllerInstance).get_class($moduleManager)) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance, $moduleManager);
            self::$instanceKey = serialize(get_class($controllerInstance).get_class($moduleManager));
        }

        return self::$instance;
    }

    /**
     * @param MinifyCssHandler $minifyCssHandler
     * @internal Works perfect
     * @see AbstractBase::preRun()
     */
    public function addReactCss(MinifyCssHandler $minifyCssHandler): void
    {
        if (empty($this->getModuleControllerCssEntryPoints())) {
            return;
        }

        foreach ($this->getModuleControllerCssEntryPoints() as $css) {
            $minifyCssHandler->addCss($css);
        }
    }

    /**
     * @return array
     */
    public function getSystemControllerEntryPoints(): array
    {
        return $this->systemControllerEntryPoints;
    }

    /**
     * @return array
     */
    public function getModuleControllerCssEntryPoints(): array
    {
        if(empty($this->getModuleControllerAction())){
            return [];
        }

        $moduleEntryPointTag = ucfirst(sprintf("%s/%s", $this->moduleControllerShortName, $this->getModuleControllerAction()));
        $this->moduleControllerCssEntryPoints = $this->moduleControllerEntryPointConfig->get(sprintf("entrypoints.%s.css", $moduleEntryPointTag), []);
        if(!empty($this->moduleControllerCssEntryPoints)){
            $this->moduleControllerCssEntryPoints = array_map([$this, "addRelativeModuleViewsPath"], $this->moduleControllerCssEntryPoints);
        }

        return $this->moduleControllerCssEntryPoints;
    }

    /**
     * @return array
     */
    public function getModuleControllerJsEntryPoints(): array
    {
        if(empty($this->getModuleControllerAction())){
            return [];
        }

        $moduleEntryPointTag = ucfirst(sprintf("%s/%s", $this->moduleControllerShortName, $this->getModuleControllerAction()));
        $this->moduleControllerJsEntryPoints = $this->moduleControllerEntryPointConfig->get(sprintf("entrypoints.%s.js", $moduleEntryPointTag), []);
        if(!empty($this->moduleControllerJsEntryPoints)){
            $this->moduleControllerJsEntryPoints = array_map([$this, "addRelativeModuleViewsPath"], $this->moduleControllerJsEntryPoints);
        }

        return $this->moduleControllerJsEntryPoints;
    }

    /**
     * @return string
     */
    public function getModuleControllerAction(): string
    {
        return $this->moduleControllerAction;
    }

    /**
     * @param string $moduleControllerAction
     */
    public function setModuleControllerAction(string $moduleControllerAction): void
    {
        $this->moduleControllerAction = $moduleControllerAction;
    }
}
