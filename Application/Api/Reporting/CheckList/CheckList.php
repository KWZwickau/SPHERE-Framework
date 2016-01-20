<?php
namespace SPHERE\Application\Api\Reporting\CheckList;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class CheckList
 *
 * @package SPHERE\Application\Api\Reporting\CheckList
 */
class CheckList implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __NAMESPACE__.'\CheckList::downloadCheckList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param null $ListId
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOptionPersonId
     *
     * @return bool|string
     */
    public function downloadCheckList(
        $ListId = null,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOptionPersonId = null
    )
    {

        $tblList = \SPHERE\Application\Reporting\CheckList\CheckList::useService()->getListById($ListId);
        if ($tblList) {
            $fileLocation = \SPHERE\Application\Reporting\CheckList\CheckList::useService()
                ->createCheckListExcel($tblList, $YearPersonId, $LevelPersonId, $SchoolOptionPersonId);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Check-List ".$tblList->getName()." ".date("Y-m-d H:i:s").".xls")->__toString();
            }
        }

        return false;
    }
}
