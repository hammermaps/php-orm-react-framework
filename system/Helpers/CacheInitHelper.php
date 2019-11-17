<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Exception;
use Exceptions\CacheException;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheHelper
 * @package Helpers
 */
class CacheInitHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cacheInstance;

    /**
     * @var bool
     */
    private $hasFallback = false;

    /**
     * CacheHelper constructor.
     * @param ConfigValues $config
     * @param string $instanceId
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    private function __construct(ConfigValues $config, ?string $instanceId)
    {
        $cacheOptions = ConfigFactory::fromArray(
            $config->get(sprintf("cache_options.%s", $instanceId))
        );

        /**
         * @var $defaultCacheDriverName string
         * @var $defaultCacheDriverClass string
         * @var $defaultCacheConfiguration ConfigurationOption
         * |\Phpfastcache\Drivers\Memcache\Config|\Phpfastcache\Drivers\Cassandra\Config
         * |\Phpfastcache\Drivers\Couchbase\Config|\Phpfastcache\Drivers\Couchdb\Config
         * |\Phpfastcache\Drivers\Memcached\Config|\Phpfastcache\Drivers\Mongodb\Config
         * |\Phpfastcache\Drivers\Predis\Config|\Phpfastcache\Drivers\Redis\Config
         * |\Phpfastcache\Drivers\Riak\Config|\Phpfastcache\Drivers\Ssdb\Config
         */
        $defaultCacheDriverName = $cacheOptions->get("driver.driverName");
        $defaultCacheDriverClass = $cacheOptions->get("driver.driverClass");
        $defaultCacheDriverConfig = $cacheOptions->get("driver.driverConfig");

        try {
            $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);
        } catch (Exception $e) {

            /**
             * Filter invalid options
             * @see CacheInitHelper::getInvalidConfigOptions()
             */
            $invalidConfigOptions = $this->getInvalidConfigOptions($e);
            if (!empty($invalidConfigOptions)) {

                foreach ($invalidConfigOptions as $value) {
                    unset($defaultCacheDriverConfig[$value]);
                }

                /**
                 * Re-Declare Corrected Cache Configuration
                 */
                $defaultCacheConfiguration = new $defaultCacheDriverClass($defaultCacheDriverConfig);

            } else {
                throw new CacheException($e->getMessage(), $e->getCode(), $e);
            }
        }

        /**
         * Hack for triggered errors on Fallback
         */
        $errorReportingLevel = error_reporting();
        error_reporting(E_USER_ERROR);

        try {

            $this->cacheInstance = CacheManager::getInstance(
                $defaultCacheDriverName,
                $defaultCacheConfiguration,
                $instanceId
            );

        } catch (Exception $e) {

            $this->cacheInstance = CacheManager::getInstance(
                $defaultCacheConfiguration->getFallback(),
                $defaultCacheConfiguration->getFallbackConfig(),
                $instanceId
            );
        }

        $this->hasFallback = !(strcasecmp(
                $this->cacheInstance->getDriverName(),
                $cacheOptions->get("driver.driverName",
                    ConfigValues::NOT_SET)
            ) === 0
        );

        /**
         * Reset reporting level
         */
        error_reporting($errorReportingLevel);
    }

    /**
     * @param ConfigValues $config
     * @param string|null $instanceId
     * @return CacheInitHelper|null
     * @throws CacheException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    public static final function init(ConfigValues $config, ?string $instanceId = null)
    {
        if (is_null(self::$instance) || serialize($config).serialize($instanceId) !== self::$instanceKey) {
            self::$instance = new self($config, $instanceId);
            self::$instanceKey = serialize($config).serialize($instanceId);
        }

        return self::$instance;
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getInvalidConfigOptions(Exception $e): array
    {
        $result = [];
        $message = $e->getMessage();
        if (strpos($e->getMessage(), ":") !== false) {
            $messageParts = explode(":", $message);
            if (count($messageParts) > 1) {
                $result = array_map(
                    "trim", explode(",", $messageParts[1])
                );
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public final function hasFallback(): bool
    {
        return $this->hasFallback;
    }

    /**
     * @return ExtendedCacheItemPoolInterface
     */
    public final function getCacheInstance(): ExtendedCacheItemPoolInterface
    {
        return $this->cacheInstance;
    }
}