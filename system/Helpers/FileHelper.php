<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Helpers;


use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class FileHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $file = "";

    /**
     * @var string
     */
    private $fileType = "file";

    /**
     * @var string|null
     */
    private $exceptionClass = null;

    /**
     * FileHelper constructor.
     * @param string $file
     * @param string|null $exceptionClass
     */
    private function __construct(string $file, ?string $exceptionClass = null)
    {
        $this->file = $file;
        $this->fileType = is_dir($this->file) ? "directory" : "file";
        $this->exceptionClass = class_exists($exceptionClass) ? $exceptionClass : null;
    }

    /**
     * @param string $file
     * @param string|null $exceptionClass
     * @return FileHelper|null
     */
    public static function init(string $file, ?string $exceptionClass = null)
    {
        if (is_null(self::$instance) || serialize($file.$exceptionClass) !== self::$instanceKey) {
            self::$instance = new self($file, $exceptionClass);
            self::$instanceKey = serialize($file.$exceptionClass);
        }

        return self::$instance;
    }

    /**
     * @param bool $mkdir
     * @return bool
     */
    public function fileExists($mkdir = false)
    {
        if (!file_exists($this->file)) {
            if ($mkdir) {
                if (!@mkdir($this->file, 0777, true)) {
                    if (!is_null($this->exceptionClass)) {
                        throw new $this->exceptionClass(sprintf("The required %s '%s' can not be created, please check the directory permissions or create it manually.", $this->fileType, $this->file), E_ERROR);
                    }
                    return false;
                }
            } elseif (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be found", $this->fileType, $this->file), E_ERROR);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        if (!$this->fileExists()) {
            return false;
        } elseif (!is_readable($this->file)) {
            if (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be loaded, please check the file and directory permissions", $this->fileType, $this->file), E_ERROR);
            }

            return false;
        }

        return true;
    }

    /**
     * @param bool $mkdirAndSetChmod
     * @return bool
     */
    public function isWritable($mkdirAndSetChmod = false)
    {
        if (!$this->fileExists($mkdirAndSetChmod)) {
            return false;
        } elseif (!is_writable($this->file)) {
            if ($mkdirAndSetChmod) {
                if (!@chmod($this->file, 0777)) {
                    if (!is_null($this->exceptionClass)) {
                        throw new $this->exceptionClass(sprintf("The required %s '%s' can not be written, please check the directory permissions.", $this->fileType, $this->file), E_ERROR);
                    }
                    return false;
                }
            } elseif (!is_null($this->exceptionClass)) {
                throw new $this->exceptionClass(sprintf("The %s '%s' could not be loaded, please check the file and directory permissions", $this->fileType, $this->file), E_ERROR);
            }

            return false;
        }

        return true;
    }
}