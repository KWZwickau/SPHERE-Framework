<?php
namespace SPHERE\Application\Billing\Accounting\Creditor;

use SPHERE\Application\Api\Billing\Accounting\ApiCreditor;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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

        $Stage->addButton((new Primary('Gläubiger hinzufügen', ApiCreditor::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiCreditor::pipelineOpenAddCreditorModal('addCreditor')));

        $Stage->setContent(
            ApiCreditor::receiverModal('Gläubiger hinzufügen', 'addCreditor')
            .ApiCreditor::receiverModal('Gläubiger bearbeiten', 'editCreditor')
            .ApiCreditor::receiverModal('Gläubiger entfernen', 'deleteCreditor')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiCreditor::receiverCreditorTable($this->getCreditorTable())
                        )
                    )
                )
            ));


        return $Stage;
    }

    /**
     * @return TableData
     */
    public function getCreditorTable()
    {

        $tableContent = array();
        if(($tblCreditorAll = Creditor::useService()->getCreditorAll())){
            array_walk($tblCreditorAll, function(TblCreditor $tblCreditor) use (&$tableContent){
                $Item['Owner'] = $tblCreditor->getOwner();
                $Item['Address'] = $tblCreditor->getStreet().' '.$tblCreditor->getNumber().', '.$tblCreditor->getCode()
                    .' '.$tblCreditor->getCity().' '.$tblCreditor->getDistrict();
                $Item['CreditorId'] = $tblCreditor->getCreditorId();
                $Item['BankName'] = $tblCreditor->getBankName();
                $Item['IBAN'] = $tblCreditor->getIBAN();
                $Item['BIC'] = $tblCreditor->getBIC();
                $Item['Option'] = (new Link('', ApiCreditor::getEndpoint(), new Pencil(), array(), 'Bearbeiten'))
                        ->ajaxPipelineOnClick(ApiCreditor::pipelineOpenEditCreditorModal('editCreditor',
                            $tblCreditor->getId()))
                    .'|'
                    .(new Link(new DangerText(new Disable()), ApiCreditor::getEndpoint(), null, array(), 'Entfernen'))
                        ->ajaxPipelineOnClick(ApiCreditor::pipelineOpenDeleteCreditorModal('editCreditor',
                            $tblCreditor->getId()));
                array_push($tableContent, $Item);
            });
        }

        return new TableData($tableContent, null, array(
            'Owner'      => 'Besitzer',
            'Address'    => 'Adresse',
            'CreditorId' => 'Gläubiger',
            'BankName'   => 'Bankname',
            'IBAN'       => 'IBAN',
            'BIC'        => 'BIC',
            'Option'     => '',
        ), array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array("orderable" => false, "targets" => -1),
            ),
        ));
    }
}