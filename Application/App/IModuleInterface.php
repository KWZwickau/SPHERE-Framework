<?php
namespace SPHERE\Application\App;

use SPHERE\Application\IServiceInterface;

/**
 * Interface IModuleInterface
 *
 * @package SPHERE\Application\App
 */
interface IModuleInterface
{

    public static function registerModule();

    /**
     * @return IServiceInterface
     */
    public static function useService();

}
