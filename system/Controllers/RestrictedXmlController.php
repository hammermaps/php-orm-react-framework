<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Interfaces\ControllerInterfaces\XmlControllerInterface;

/**
 * Class PublicController
 * @package Controllers
 */
class RestrictedXmlController extends RestrictedController implements XmlControllerInterface
{
    /**
     * @param string $action
     */
    public final function run(string $action)
    {
        $methodName = sprintf("%sAction", $action);

        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->render404();
        }

        $this->render();
    }

    /**
     *
     */
    public final function render(): void
    {
        header(self::HEADER_CONTENT_TYPE_JSON);
        echo json_encode($this->getContext());
        exit();
    }

    /**
     *
     */
    public function indexAction(): void
    {
        parent::indexAction(); // TODO: Change the autogenerated stub
    }
}
