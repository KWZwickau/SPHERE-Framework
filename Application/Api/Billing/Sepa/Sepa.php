<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

//require_once( __DIR__.'/../../../../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
//AutoLoader::getNamespaceAutoLoader('Digitick\Sepa', __DIR__.'/../../../../Library/SepaXml/lib');
require_once( __DIR__.'/../../../../Library/SepaXml/vendor/autoload.php' );

/**
 * Class Sepa
 *
 * @package SPHERE\Application\Api\Billing\Sepa
 */
class Sepa implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download',
            __CLASS__.'::downloadSepa'
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
     * @param string $Month
     * @param string $Year
     *
     * @return string
     */
    public function downloadSepa($Month = '', $Year = '')
    {

        $directDebit = Balance::useService()->createSepaContent($Month, $Year);

        $monthList = Invoice::useService()->getMonthList($Month, $Month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        // Retrieve the resulting XML
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="Abrechnung_'.$monthString.'_'.$Year.'.xml"');
        return $directDebit->asXML();
    }



}
