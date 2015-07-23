<?php
namespace SPHERE\Application;

use SPHERE\Common\Frontend\IFrontendInterface;

/**
 * Interface IModuleInterface
 *
 * @package SPHERE\Application
 */
interface IModuleInterface
{

    public static function registerModule();

    /**
     * @return IServiceInterface
     */
    public static function useService();

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend();
}
