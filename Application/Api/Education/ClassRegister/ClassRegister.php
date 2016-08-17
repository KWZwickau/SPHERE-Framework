<?php
namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\Response;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Post;
use SPHERE\System\Extension\Extension;

/**
 * Class ClassRegister
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ClassRegister extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Reorder', __CLASS__.'::reorderDivision'
        ));
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

    /**
     * @param null|array $Reorder
     * @param null|array $Additional
     *
     * @return Response
     */
    public function reorderDivision( $Reorder = null, $Additional = null )
    {
        // TODO: Update Order




        return (new Response())->addData($Reorder)->addData($Additional);
    }
}
