<?php
namespace SPHERE\Application;

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

    public static function useFrontend();
}
