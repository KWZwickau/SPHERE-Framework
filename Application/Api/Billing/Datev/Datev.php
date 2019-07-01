<?php
namespace SPHERE\Application\Api\Billing\Datev;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class Datev
 * @package SPHERE\Application\Api\Billing
 */
class Datev implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadDatev'
        ));
    }

    public static function useService()
    {
        // Implement useService() method.
    }

    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    public function downloadDatev($BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            if(($fileLocation = Balance::useService()->createDatevCsv($tblBasket))){
                $name = $tblBasket->getName();
                $month = $tblBasket->getMonth();
                $year = $tblBasket->getYear();
                $monthString = '';
                $monthList = Invoice::useService()->getMonthList($month, $month);
                if(!empty($monthList)){
                    $monthString = current($monthList);
                }
                return FileSystem::getDownload($fileLocation->getRealPath(),
//                    "EXTF_".date("Y-m-d H:i:s").".csv")->__toString();
                    'EXTF_'.$name.'_'.$monthString.'_'.$year.'.csv')->__toString();
            }
        }

        return false;
    }


}