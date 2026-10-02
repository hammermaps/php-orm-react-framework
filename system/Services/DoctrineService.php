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

namespace Services;


use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use Doctrine\DBAL\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use Exceptions\DoctrineException;
use Helpers\ArrayHelper;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Helpers\StringHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Services\Doctrine\EntityManagerFactory;
use Traits\ControllerTraits\AbstractBaseTrait;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DoctrineService
 * @package Services
 */
class DoctrineService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var ModuleManager
     */
    private ModuleManager $moduleManager;

    /**
     * @var string|null
     */
    private ?string $currentConnectionOption = "default";

    /**
     * @var self
     */
    private self $moduleDoctrineService;

    /**
     * @var self
     */
    private self $systemDoctrineService;

    /**
     * @var EntityManager|null
     */
    private ?EntityManager $entityManager = null;

    /**
     * @var array
     */
    private array $applicationOptions = [];

    /**
     * @var array
     */
    private array $connectionOptions = [];

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * DoctrineService constructor.
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param ModuleManager $moduleManager
     * @return DoctrineService|null
     * @throws DoctrineException
     */
    public static final function init(ModuleManager $moduleManager): ?DoctrineService
    {
        $instanceKey = spl_object_hash($moduleManager);
        if (is_null(self::$instance) || $instanceKey !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = $instanceKey;
        }

        self::$instance->setSystemDoctrineService();
        self::$instance->setModuleDoctrineService();
        return self::$instance;
    }

    /**
     * @throws DoctrineException
     */
    public final function setModuleDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.module");
        $this->moduleDoctrineService = new static($this->moduleManager);
        $this->moduleDoctrineService->setApplicationOptions($applicationOptions);
        $this->moduleDoctrineService->setConnectionOptions($connectionOptions);
        $this->moduleDoctrineService->errorMode();
    }

    /**
     * ORM for Entities of the module
     * @return DoctrineService
     * @see DoctrineService::setModuleDoctrineService()
     * @see AbstractBaseTrait::getModuleDbService()
     */
    public final function getModuleDoctrineService(): DoctrineService
    {
        return $this->moduleDoctrineService;
    }

    /**
     * @throws DoctrineException
     */
    public final function setSystemDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.system");
        $this->systemDoctrineService = new static($this->moduleManager);
        $this->systemDoctrineService->setApplicationOptions($applicationOptions);
        $this->systemDoctrineService->setConnectionOptions($connectionOptions);
        $this->systemDoctrineService->errorMode();
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public final function getOption(string $name, $default = null)
    {
        $keys = explode(".", $name);
        $value = $this->applicationOptions;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * @param $repositoryName
     * @param null $connectionOption
     * @return EntityRepository
     * @throws DoctrineException
     */
    public final function getRepository($repositoryName, $connectionOption = null): EntityRepository
    {
        $entity_namespace = $this->getOption("entity_namespace");
        return $this->getEntityManager($connectionOption)->getRepository(
            sprintf("%s\\%s", $entity_namespace, $repositoryName)
        );
    }

    /**
     * @param null $connectionOption
     * @return EntityManager
     * @throws DoctrineException
     */
    public final function getEntityManager($connectionOption = null): EntityManager
    {
        if (!is_null($connectionOption) && $this->currentConnectionOption !== $connectionOption) {
            $config = $this->moduleManager->getConfig();
            $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
            $this->currentConnectionOption = $connectionOption;
            $this->setConnectionOptions($connectionOptions);
        }

        if (is_null($this->entityManager)) {
            try {
                $this->entityManager = EntityManagerFactory::create(
                    $this->applicationOptions,
                    $this->connectionOptions
                );
            } catch (Exception $e) {
                throw new DoctrineException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $em = $this->entityManager;

        /**
         * @internal Hack for pdo_sqlite and Doctrine\DBAL\Exception\SyntaxErrorException while
         * executing 'SET NAMES utf8': SQLSTATE[HY000]: General error: 1 near "SET": syntax error
         * @internal SQLite connection is always UTF-8
         * @see http://www.alberton.info/dbms_charset_settings_explained.html
         * @deprecated
         */
        if ($em->getConnection()->getDriver() instanceof \Doctrine\DBAL\Driver\PDO\SQLite\Driver
            && $em->getEventManager()->hasListeners(Events::postConnect)) {
            $this->removeEventListener($em, Events::postConnect, MysqlSessionInit::class);
        }

        $checksum = DirHelper::init($this->getOption("entity_dir"))->getMd5CheckSum([".php"], [".", "..", ".checksum"]);
        $csFile = FileHelper::init(sprintf("%s/.checksum", $this->getOption("entity_dir")));

        /**
         * @internal Here is a checksum from the entities formed to learn changes or initialization.
         * For changes to the DB schema, an automatic adjustment is made here.
         * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/tools.html#database-schema-generation
         */
        if($csFile->getContents() !== $checksum)
        {
            try {
                $tool = new SchemaTool($em);
                $schemas = $this->getEntitySchemas($em);
                if (!empty($schemas)) {
                    $tool->updateSchema($schemas);
                }

                $csFile->putContents($checksum);

            } catch (Exception $e) {
                throw new DoctrineException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $em;
    }

    /**
     * ORM for Entities of the system
     * @return DoctrineService
     * @see DoctrineService::setSystemDoctrineService()
     * @see AbstractBaseTrait::getSystemDbService()
     */
    public final function getSystemDoctrineService(): DoctrineService
    {
        return $this->systemDoctrineService;
    }

    /**
     * @param $entity
     * @param null $connectionOption
     * @throws DoctrineException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public final function persistAndFlush($entity, $connectionOption = null)
    {
        $em = $this->getEntityManager($connectionOption);
        $em->persist($entity);
        $em->flush($entity);
    }

    /**
     * @param $options
     */
    protected final function setApplicationOptions($options)
    {
        $this->applicationOptions = is_array($options) ? $options : (array)$options;
        $this->entityManager = null;
    }

    /**
     * @param $options
     * @throws DoctrineException
     */
    protected final function setConnectionOptions($options): void
    {
        $this->connectionOptions = is_array($options) ? $options : (array)$options;
        $this->entityManager = null;

        $optionsHelper = ArrayHelper::init($this->connectionOptions);

        /**
         * @internal Both for the system and for modules, if the default driver "pdo_sqlite"
         * is selected, the database is automatically created if it does not exist.
         * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/tools.html#database-schema-generation
         */
        if (strcasecmp($optionsHelper->get("driver", false), "pdo_sqlite") == 0) {
            $sqLitePath = $optionsHelper->get("path", false);
            if ($sqLitePath && !FileHelper::init($sqLitePath)->isWritable()) {
                try {
                    $em = $this->getEntityManager($this->currentConnectionOption);
                    $tool = new SchemaTool($em);
                    $schemas = $this->getEntitySchemas($em);
                    if (empty($schemas)) {
                        return;
                    }

                    $tool->createSchema($schemas);

                } catch (Exception $e) {
                    throw new DoctrineException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
    }

    /**
     * Enable Doctrine exception/error mode.
     */
    public final function errorMode(): void
    {
        // No-op: Doctrine ORM throws exceptions by default.
    }

    /**
     * Prepare array with all necessary metadata of the entities to be able to automatically create the database if necessary.
     * @param EntityManager $em
     * @return array
     * @see DoctrineService::setConnectionOptions()
     */
    private function getEntitySchemas(EntityManager $em): array
    {
        try {
            $result = [];
            $entityDir = $this->getOption("entity_dir");
            $entityNamespace = $this->getOption("entity_namespace");
            $entities = DirHelper::init($entityDir)->getScan([".php"]);
            if (!empty($entities)) {
                foreach ($entities as $entity) {
                    $entityName = StringHelper::init($entity)->replace(".php", "")->getString();
                    $result[] = $em->getClassMetadata(sprintf("\\%s\\%s", $entityNamespace, $entityName));
                }

                return $result;
            }
        } catch (Exception $e) {
        }
        return $result;
    }

    /**
     * @param EntityManager $em
     * @param $event
     * @param string $instanceof
     */
    private function removeEventListener(EntityManager $em, $event, string $instanceof = self::class): void
    {
        foreach ($em->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof $instanceof) {
                    $em->getEventManager()->removeEventListener([$event], $listener);
                    break 2;
                }
            }
        }
    }
}
