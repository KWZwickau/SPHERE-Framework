<?php

namespace SPHERE\Application\Billing\Accounting\Creditor;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Creditor
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendCreditor()
    {

        $Stage = new Stage('Übersicht', ' Gläubiger');
        $tableContent = array();
        if(($tblCreditorAll = Creditor::useService()->getCreditorAll())){
            array_walk($tblCreditorAll, function(TblCreditor $tblCreditor) use (&$tableContent){
                $Item['Owner'] = $tblCreditor->getOwner();
                $Item['Street'] = $tblCreditor->getStreet();
                $Item['Number'] = $tblCreditor->getNumber();
                $Item['Code'] = $tblCreditor->getCode();
                $Item['City'] = $tblCreditor->getCity();
                $Item['District'] = $tblCreditor->getCity();
                $Item['CreditorId'] = $tblCreditor->getCreditorId();
                $Item['BankName'] = $tblCreditor->getBankName();
                $Item['IBAN'] = $tblCreditor->getIBAN();
                $Item['BIC'] = $tblCreditor->getBIC();
                array_push($tableContent, $Item);
            });
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($tableContent, null, array(
                            'Owner' => 'Besitzer',
                            'Street' => 'Straße',
                            'Number' => 'Hausnummer',
                            'Code' => 'PLZ',
                            'City' => 'Stadt',
                            'CreditorId' => 'Gläubiger',
                            'BankName' => 'Name der Bank',
                            'IBAN' => 'IBAN',
                            'BIC' => 'BIC',
                        ))
                    )
                )
            )
        ));


        return $Stage;
    }
}