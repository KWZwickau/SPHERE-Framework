<?php
namespace SPHERE\Application\Api\Billing\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
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
     *
     * @return bool|string
     */
    public function downloadBalanceList($ItemId = '', $Year = '', $From = '', $To = '')
    {

        if (($tblItem = Item::useService()->getItemById($ItemId))) {
            $PriceList = Balance::useService()->getPriceListByItemAndYear($tblItem, $Year, $From, $To);
            if (!empty($PriceList)) {
                $fileLocation = Balance::useService()->createBalanceListExcel($PriceList, $tblItem->getName(), $From, $To);
                $MonthList = Invoice::useService()->getMonthList();
                $StartMonth = $MonthList[$From];
                $ToMonth = $MonthList[$To];
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    $tblItem->getName().'-'.$Year.'-'.$StartMonth.'-'.$ToMonth.'.xlsx')->__toString();
            }
        }

        return false;
    }

}
