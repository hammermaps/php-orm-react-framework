<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Interfaces\ServiceInterfaces;


use Configula\ConfigValues;
use Managers\ModuleManager;

/**
 * Interface VendorExtensionServiceInterface
 * @package Interfaces\ServiceInterfaces
 */
interface VendorExtensionServiceInterface
{
    public function __construct(ModuleManager $moduleManager);

    public static function init(ModuleManager $moduleManager);
}