<?php

require_once 'inc/functions.inc.php';
require_once 'inc/helper.inc.php';
require_once 'inc/bootstrap.inc.php';

session_start();

$module = $_GET['module'] ?? null;
$module = $module && strpos($module, "/") ? explode("/", $module)[0] : $module;
$module = is_null($module) ? $module : htmlentities(lcfirst($module));

$controller = $_GET['controller'] ?? (!is_null($module) ? 'index' : 'dispatch');
$controller = strpos($controller, "/") ? explode("/", $controller)[0] : $controller;
$controller = htmlentities(lcfirst($controller));

$action = $_GET['action'] ?? 'index';
$action = strpos($action, "/") ? explode("/", $action)[0] : $action;
$action = htmlentities(lcfirst($action));

$controllerNamespace = is_null($module) ? 'Controllers\\'
    : sprintf("Modules\\%s\\Controllers\\", ucfirst($module));

$controllerName = $controllerNamespace . ucfirst($controller) . 'Controller';

if (class_exists($controllerName)) {
    $requestController = new $controllerName($baseDir);
    if (!method_exists($requestController, "run")) {
        $requestController = new Controllers\PublicController($baseDir);
        $requestController->render404();
    } else {
        $requestController->run($action);
    }
} else {
    $requestController = new Controllers\PublicController($baseDir);
    $requestController->render404();
}
