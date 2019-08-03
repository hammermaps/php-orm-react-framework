<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Configs\CoreConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\TemplateConfig;
use Exceptions\ConfigException;
use Exceptions\DoctrineException;
use Exceptions\LoggerException;
use Exceptions\MinifyCssException;
use Exceptions\MinifyJsException;
use Exceptions\TemplateException;
use Handlers\ErrorHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Throwable;
use Traits\AbstractBaseTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AbstractBase
 * @package Controllers
 */
abstract class AbstractBase
{
    use AbstractBaseTrait;

    /**
     * AbstractBase constructor.
     * @param string $baseDir
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws TemplateException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->initCore();
    }

    /**
     * @throws ConfigException
     * @throws DoctrineException
     * @throws LoggerException
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws TemplateException
     */
    private function initCore()
    {
        $this->coreConfig = CoreConfig::init($this->getBaseDir());

        // 1. Logging
        $this->initLogger();

        // 2. Error handling, etc
        $this->initHandlers();

        // 3. Database, ORM
        $this->initDoctrine();

        // 4. Twig template engine
        $this->initTemplate();
    }

    /**
     * 1. Logging
     * @throws LoggerException
     */
    private function initLogger(): void
    {
        $this->logger = LoggerConfig::init(
            $this->getCoreConfig(),
            $this->getLogLevel()
        );
    }

    /**
     * 2. Error handling, etc
     */

    /**
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    private function initHandlers(): void
    {
        ErrorHandler::init(
            $this->getCoreConfig(),
            $this->getLogger()
        );

        $this->cssHandler = MinifyCssHandler::init(
            $this->getCoreConfig()
        );

        $this->cssHandler->addCss("test", true);

        $this->jsHandler = MinifyJsHandler::init(
            $this->getCoreConfig()
        );
    }

    /**
     * 3. Database, ORM
     * @throws DoctrineException
     */
    private function initDoctrine(): void
    {
        $this->doctrine = DoctrineConfig::init(
            $this->getCoreConfig(),
            $this->getConnectionOption()
        );

        $this->entityManager = $this->doctrine->getEntityManager();
    }

    /**
     * 4. Twig template engine
     * @throws TemplateException
     */
    private function initTemplate(): void
    {
        $this->twig = TemplateConfig::init(
            $this->getCoreConfig()
        );
    }

    /**
     * @param string $action
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     */
    public function run(string $action): void
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';
        $this->setTemplate($methodName);

        if (method_exists($this, $methodName))
        {
            $this->$methodName();
        }
        else
        {
            $this->render404();
        }

        $this->render();
    }

    /**
     *
     */
    public function render404(): void
    {
        header('HTTP/1.0 404 Not Found');
        die('Error 404');
    }

    /**
     * @param $action
     * @param $controller
     */
    protected function recall(string $action, string $controller): void
    {
        $controllerName = __NAMESPACE__ . '\\' . ucfirst($controller) . 'Controller';

        if(!class_exists($controllerName))
        {
            $this->render404();
        }
        elseif(!method_exists($controller, "run"))
        {
            $this->render404();
        }
        else
        {
            $controller = new $controllerName($this->baseDir);

            $controller->run($action);
        }

        exit;
    }

    /**
     * @param string|null $action
     * @param string|null $controller
     */
    protected function redirect(?string $action = null, ?string $controller = null): void
    {
        $params = [];

        if (!empty($controller)) {
            $params[] = 'controller=' . $controller;
        }

        if (!empty($action)) {
            $params[] = 'action=' . $action;
        }

        $to = '';
        if (!empty($params)) {
            $to = '?' . implode('&', $params);
        }

        header('Location: index.php' . $to);
        exit;
    }

    /**
     * @throws Throwable
     */
    protected function render(): void
    {
        $this->cssHandler->compileAndGet();
        $this->jsHandler->compileAndGet();

        $this->addContext("message", $this->getMessage());
        $this->addContext("minified_css", $this->cssHandler->getDefaultMinifyCssFile(true));
        $this->addContext("minified_js", $this->jsHandler->getDefaultMinifyJsFile(true));

        echo $this->template->render($this->context);
    }
}
