<?php
namespace SPHERE\Application\Api\Test;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class Test
 *
 * @package SPHERE\Application\Api\Test
 */
class Test extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/ShowImage',
                __NAMESPACE__.'\Frontend::ShowImage'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/ShowThumbnail',
                __NAMESPACE__.'\Frontend::ShowThumbnail'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/PersonList',
                __CLASS__.'::PersonList'
            )
        );
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
     * @return string
     */
    public function PersonList()
    {

        return $this->getDataTable(
            Person::useService()->getPersonRepository()
        )
            ->setCallbackFunction(function (TblPerson $tblPerson) {

                $tblPerson->FullName = $tblPerson->getFullName();
//                $tblPerson->Option = new Standard('', '/People/Person', new Pencil(),
//                    array('Id' => $tblPerson->getId()), 'Bearbeiten');
                return $tblPerson;
            })
            ->getResult();
    }
}
