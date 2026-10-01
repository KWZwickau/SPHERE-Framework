<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Credit/Download',
            __CLASS__.'::downloadSepaCredit'
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
     * @param array $Invoice - BasketId, [CheckboxList], [Fee], [Legacy] Kompatibilitätsmodus
     *                       für Banken, die das aktuelle Format (SEPA-Formatversion 3.7 /
     *                       pain.008.001.08) noch nicht annehmen -> erzeugt stattdessen
     *                       das alte Format pain.008.001.02.
     *
     * @return string
     */
    public function downloadSepa($Invoice = array())
    {

        $CheckboxList = array();
        if(isset($Invoice['CheckboxList'])){
            $CheckboxList = $Invoice['CheckboxList'];
        }
        $FeeList = array();
        if(isset($Invoice['Fee'])){
            $FeeList = $Invoice['Fee'];
        }
        $IsLegacy = !empty($Invoice['Legacy']);
        $painFormat = $IsLegacy ? 'pain.008.001.02' : 'pain.008.001.08';
        $fileNameSuffix = $IsLegacy ? '_Kompatibilitaetsmodus' : '';

        $BasketId = $Invoice['BasketId'];
        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $directDebit = false;
        if($tblBasket){
            $directDebit = Balance::useService()->createSepaContent($tblBasket, $CheckboxList, $FeeList, $painFormat);
        }

        $name = $tblBasket->getName();
        $month = $tblBasket->getMonth();
        $year = $tblBasket->getYear();
        $monthString = '';
        $monthList = Invoice::useService()->getMonthList($month, $month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        if($directDebit){
            // Retrieve the resulting XML
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="Abrechnung_'.$name.'_'.$monthString.'_'.$year.$fileNameSuffix.'.xml"');
            return $directDebit->asXML();
        } else {
            return '<h1 style="color: red;">XML Datei enthält keine Sepa-Lastschrift</h1>'
                .'<h3>Mögliche Ursachen:</h3>'
                .'<div>- Die Abrechnung enthält keine SEPA-Lastschrift.</div>'
                .'<div>- Die enthaltenen SEPA-Lastschriften sind alle auf "offene Posten" gesetzt.</div>'
                .'<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lösung 1: Wählen Sie diese im Modal davor aus, hier sind die offenen Posten enthalten, werden aber bei
                    Verwendung auf <b>bezahlt</b> gesetzt. </div>'
                .'<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lösung 2: Gehen Sie zur Navigation "Fakturierung → Beitragsfakturierung → Offene Posten" und setzen Sie
                     die gewünschten Einträge auf bezahlt. </div>';
        }
    }

    /**
     * @param string $BasketId
     * @param string $Legacy   Kompatibilitätsmodus für Banken, die das aktuelle Format
     *                         (SEPA-Formatversion 3.7 / pain.001.001.09) noch nicht annehmen
     *                         -> erzeugt stattdessen das alte Format pain.001.002.03.
     *
     * @return string
     */
    public function downloadSepaCredit($BasketId = '', $Legacy = '')
    {

        $IsLegacy = !empty($Legacy);
        $painFormat = $IsLegacy ? 'pain.001.002.03' : 'pain.001.001.09';
        $fileNameSuffix = $IsLegacy ? '_Kompatibilitaetsmodus' : '';

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $customerCredit = false;
        if($tblBasket){
            $customerCredit = Balance::useService()->createSepaCreditContent($tblBasket, $painFormat);
        }

        $name = $tblBasket->getName();
        $month = $tblBasket->getMonth();
        $year = $tblBasket->getYear();
        $monthString = '';
        $monthList = Invoice::useService()->getMonthList($month, $month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        if($customerCredit){
            // Retrieve the resulting XML
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="Abrechnung_'.$name.'_'.$monthString.'_'.$year.$fileNameSuffix.'.xml"');
            return $customerCredit->asXML();
        } else {
            return new Warning('XML Datei enthält keine Sepa-Lastschrift');
        }
    }



}
