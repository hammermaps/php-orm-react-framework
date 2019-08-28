<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Exceptions;

use Exception;
use Interfaces\ExceptionInterfaces\CustomExceptionInterface;
use Throwable;

class FileFactoryException extends Exception implements CustomExceptionInterface
{
    /**
     * FileFactoryException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public final function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = is_array($message) ? json_encode($message) : $message;

        parent::__construct($message, $code, $previous);
    }
}