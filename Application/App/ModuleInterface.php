<?php

namespace SPHERE\Application\App;


/**
 *
 */
interface ModuleInterface
{
    public static function registerModule();

    public static function useService();
}
