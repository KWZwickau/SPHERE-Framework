<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Repository\Debugger;

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
     * @param string $BasketName
     *
     * @return string
     */
    public function downloadSepa($Month = '', $Year = '', $BasketName = '')
    {
        Debugger::screenDump($_POST);
        exit;

        $directDebit = Balance::useService()->createSepaContent($Month, $Year, $BasketName);

        $monthList = Invoice::useService()->getMonthList($Month, $Month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        if($directDebit){
            // Retrieve the resulting XML
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="Abrechnung_'.$monthString.'_'.$Year.'.xml"');
            return $directDebit->asXML();
        } else {
            return new Warning('XML Datei enthält keine Sepa-Lastschrift');
        }

    }



}
