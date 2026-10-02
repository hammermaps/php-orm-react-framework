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

namespace Services\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;
use Exception;
use Gedmo\DoctrineExtensions;

/**
 * Factory for creating Doctrine EntityManager instances.
 *
 * This class replaces the functionality previously provided by
 * `Webmasters\Doctrine\Bootstrap` and `Webmasters\Doctrine\ORM\EntityManager`.
 */
final class EntityManagerFactory
{
    /**
     * Create a standard Doctrine EntityManager from the given application and connection options.
     *
     * @param array $applicationOptions Options like entity_dir, entity_namespace, proxy_dir, etc.
     * @param array $connectionOptions  DBAL connection options like driver, path, host, dbname, etc.
     * @return EntityManager
     * @throws Exception
     */
    public static function create(array $applicationOptions, array $connectionOptions): EntityManager
    {
        $entityDir = $applicationOptions['entity_dir'] ?? null;
        $entityNamespace = $applicationOptions['entity_namespace'] ?? '';
        $proxyDir = $applicationOptions['proxy_dir'] ?? null;
        $autoGenerateProxyClasses = $applicationOptions['autogenerate_proxy_classes'] ?? true;
        $debugMode = $applicationOptions['debug_mode'] ?? false;
        $cache = $applicationOptions['cache'] ?? null;

        if (empty($entityDir) || !is_dir($entityDir)) {
            throw new Exception(sprintf("Entity directory '%s' does not exist", (string)$entityDir));
        }

        $config = self::createConfiguration(
            $entityDir,
            $proxyDir,
            $autoGenerateProxyClasses,
            $debugMode,
            $cache
        );

        $eventManager = new EventManager();
        self::registerGedmoExtensions($eventManager);

        $connection = DriverManager::getConnection($connectionOptions, $config, $eventManager);

        return new EntityManager($connection, $config, $connection->getEventManager());
    }

    /**
     * Create the ORM configuration.
     *
     * @param string $entityDir
     * @param string|null $proxyDir
     * @param bool $autoGenerateProxyClasses
     * @param bool $debugMode
     * @param mixed|null $cache
     * @return Configuration
     */
    private static function createConfiguration(
        string $entityDir,
        ?string $proxyDir,
        bool $autoGenerateProxyClasses,
        bool $debugMode,
        $cache = null
    ): Configuration {
        $config = ORMSetup::createAnnotationMetadataConfiguration(
            [$entityDir],
            $debugMode,
            $proxyDir,
            $cache,
            false
        );

        if (!empty($proxyDir)) {
            $config->setProxyDir($proxyDir);
        }

        $config->setProxyNamespace('Proxies');
        $config->setAutoGenerateProxyClasses($autoGenerateProxyClasses);

        return $config;
    }

    /**
     * Register Gedmo Doctrine extensions for Timestampable behavior.
     *
     * @param EventManager $eventManager
     */
    private static function registerGedmoExtensions(EventManager $eventManager): void
    {
        DoctrineExtensions::registerAnnotations();
    }
}
