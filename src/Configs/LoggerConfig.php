<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Exceptions\LoggerException;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LoggerConfig
 * @package Configs
 */
class LoggerConfig
{
    /**
     *
     */

    const DEBUG = Logger::DEBUG;

    const INFO = Logger::INFO;

    const NOTICE = Logger::NOTICE;

    const WARNING = Logger::WARNING;

    const ERROR = Logger::ERROR;

    const CRITICAL = Logger::CRITICAL;

    const ALERT = Logger::ALERT;

    const EMERGENCY = Logger::EMERGENCY;

    /**
     * @var LoggerConfig|null
     */
    private static $instance = null;

    /**
     * @var Logger|null
     */
    private $defaultLogger = null;

    /**
     * LoggerConfig constructor.
     * @param CoreConfig $config
     * @param int $level
     * @param string $application
     * @throws LoggerException
     */
    public function __construct(CoreConfig $config, $level = self::ERROR, $application = "tsi")
    {
        if($config->isDebugMode())
        {
            $level = self::DEBUG;
        }

        $baseDir = $config->getBaseDir();
        $application = trim(str_replace(" ", "", $application));

        $defaultLogDir = sprintf("%s/log/%s", $baseDir, $application);
        $defaultLogFile = sprintf("%s/%s.log", $defaultLogDir, date("Y_m_d"));

        if(!file_exists($defaultLogDir))
        {
            if(!@mkdir($defaultLogDir, 0777, true))
            {
                throw new LoggerException(sprintf("The required log directory '%s' can not be created, please check the directory permissions or create it manually.", $defaultLogDir), E_ERROR);
            }
        }

        if(!is_writable($defaultLogDir))
        {
            if(!@chmod($defaultLogDir, 0777))
            {
                throw new LoggerException(sprintf("The required log directory '%s' can not be written, please check the directory permissions.", $defaultLogDir), E_ERROR);
            }
        }

        try
        {
            $this->defaultLogger = new Logger(strtoupper($application));
            $this->defaultLogger->pushHandler(new StreamHandler($defaultLogFile, $level));
            $this->defaultLogger->pushHandler(new FirePHPHandler($level));
            //$this->defaultLogger->debug("Logger successfully initialized");
        }
        catch (\Exception $e)
        {
            throw new LoggerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param CoreConfig $config
     * @param int $level
     * @param string $application
     * @return Logger|null
     * @throws LoggerException
     */
    public static function init(CoreConfig $config, $level = self::ERROR, $application = "tsi")
    {
        if (is_null(self::$instance)) {
            self::$instance = new LoggerConfig($config, $level, $application);
        }

        return self::$instance->getDefaultLogger();
    }

    /**
     * @return Logger|null
     */
    public function getDefaultLogger(): ?Logger
    {
        return $this->defaultLogger;
    }
}