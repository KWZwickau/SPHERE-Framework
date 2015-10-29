<?php
namespace SPHERE\Application\Document\Designer\Repository;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;

/**
 * Class Repository
 * @package SPHERE\Application\Document\Designer\Repository
 */
class Repository implements IModuleInterface
{
    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}
