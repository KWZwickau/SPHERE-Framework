<?php
namespace SPHERE\Application\Api\Billing\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use MOC\V\Core\FileSystem\FileSystem;

/**
 * Class BalanceDownload
 *
 * @package SPHERE\Application\Api\Billing\Balance
 */
class BalanceDownload implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Balance/Print/Download', __NAMESPACE__.'\BalanceDownload::downloadBalanceList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Balance/MonthOverView/Download', __NAMESPACE__.'\BalanceDownload::downloadMonthOverView'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Balance/YearOverView/Download', __NAMESPACE__.'\BalanceDownload::downloadYearOverView'
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
     * @param string $ItemId
     * @param string $Year
     * @param string $From
     * @param string $To
     * @param string $DivisionId
     *
     * @return bool|string
     */
    public function downloadBalanceList($ItemId = '', $Year = '', $From = '', $To = '', $DivisionId = '0')
    {

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            $PriceList = Balance::useService()->getPriceListByItemAndYear($tblItem, $Year, $From, $To, $DivisionId);
            if(!empty($PriceList)){
                $fileLocation = Balance::useService()->createBalanceListExcel($PriceList, $tblItem->getName());
                $MonthList = Invoice::useService()->getMonthList();
                $StartMonth = $MonthList[$From];
                $ToMonth = $MonthList[$To];
                $DivisionString = '';
                if(($tblDivision = Division::useService()->getDivisionById($DivisionId))){
                    $DivisionString = '_'.$tblDivision->getDisplayName();
                }

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    $tblItem->getName().$DivisionString.'_'.$Year.'-'.$StartMonth.'-'.$ToMonth.'.xlsx')->__toString();
            }
        }

        return false;
    }

    /**
     * @param string $Year
     * @param string $Month
     *
     * @return bool|string
     */
    public function downloadMonthOverView($Year = '', $Month = '')
    {

        if(($fileLocation = Balance::useService()->createMonthOverViewExcel($Year, $Month))){
            $MonthList = Invoice::useService()->getMonthList();
            $Month = $MonthList[$Month];
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Monatsübersicht-'.$Month.'.xlsx')->__toString();
        }
        return false;
    }

    /**
     * @param string $Year
     *
     * @return bool|string
     */
    public function downloadYearOverView($Year = '')
    {

        if(($fileLocation = Balance::useService()->createYearOverViewExcel($Year))){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Jahresübersicht-'.$Year.'.xlsx')->__toString();
        }
        return false;
    }

}
