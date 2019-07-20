<?php
namespace SPHERE\Application\Api\Billing\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
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
     * @param string $ItemIdString
     * @param string $Year
     * @param string $From
     * @param string $To
     * @param string $DivisionId
     *
     * @return bool|string
     */
    public function downloadBalanceList($ItemIdString = '', $Year = '', $From = '', $To = '', $DivisionId = '0')
    {

        if($ItemIdString){
            $ItemIdList = explode(",", $ItemIdString);
            $tblItemList = array();
            foreach($ItemIdList as $ItemId){
                $tblItemList[] = Item::useService()->getItemById($ItemId);
            }
            $DivisionString = '';
            // ToDO später sollen andere Personenlisten auswählbar werden (Personengruppen / Einzelperson)
            if($DivisionId && ( $tblDivision = Division::useService()->getDivisionById($DivisionId))){
                // Datei erhält Name der Klasse
                $DivisionString = $tblDivision->getDisplayName().'_';
                // Pesronenliste aus der Klasse:
                $tblPersonList = Division::useService()->getPersonAllByDivisionList(array($tblDivision));
            } else {
                // Personenliste, wenn keine Klasse gewählt wurde:
                $tblPersonList = Balance::useService()->getPersonListByInvoiceTime($Year,
                    $From, $To);
            }
            $PriceList = array();
            if($tblPersonList){
                foreach($tblPersonList as $tblPerson){
                    /** @var TblItem $tblItem */
                    foreach($tblItemList as $tblItem){
                        // Rechnungen zusammengefasst (je Beitragsart)
                        $PriceList = Balance::useService()->getPriceListByItemAndPerson($tblItem, $Year,
                            $From, $To, $tblPerson, $PriceList);
                    }
                }
                // Summe der einzelnen Beiträge erstellen
                $PriceList = Balance::useService()->getSummaryByItemPrice($PriceList);
            }
            if(!empty($PriceList)){
                $fileLocation = Balance::useService()->createBalanceListExcel($PriceList, $tblItemList);
                $MonthList = Invoice::useService()->getMonthList();
                $StartMonth = $MonthList[$From];
                $ToMonth = $MonthList[$To];

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    $DivisionString.$Year.'-'.$StartMonth.'-'.$ToMonth.'.xlsx')->__toString();
            }
        }

        return false;
    }
}
