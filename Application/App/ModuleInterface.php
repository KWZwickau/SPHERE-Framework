<?php

namespace SPHERE\Application\App;


use SPHERE\Application\App\Response\ResponseInterface;

/**
 *
 */
interface ModuleInterface
{
    public static function registerModule();

    public static function handleRequest(): ResponseInterface;
}
