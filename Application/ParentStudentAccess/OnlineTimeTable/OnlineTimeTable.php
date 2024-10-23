<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineTimeTable;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\System\Extension\Extension;

class OnlineTimeTable extends Extension implements IApplicationInterface, IModuleInterface
{
    /**
     * @return void
     */
    public static function registerApplication(): void
    {
        self::registerModule();
    }

    /**
     * @return void
     */
    public static function registerModule(): void
    {

    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service();
    }

    public static function useFrontend()
    {

    }
}