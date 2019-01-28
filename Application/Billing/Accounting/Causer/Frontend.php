<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Api\Billing\Accounting\ApiBankReference;
use SPHERE\Application\Api\Billing\Accounting\ApiCauser;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as StandardForm;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Statistic;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Causer
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendCauser($GroupId = null)
    {

        $Stage = new Stage('Auswahl Gruppe der', 'Beitragsverursacher');

        $Content = array();


        if(($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))){
            $Content[] = new Center('Auswahl für '.$tblGroup->getName()
                .new Container(new Standard('', __NAMESPACE__.'/View', new ListingIcon(),
                    array('GroupId' => $tblGroup->getId()))));
        }

        if(($tblGroupList = Group::useService()->getGroupAll())){
            foreach($tblGroupList as &$tblGroup) {
                if($tblGroup->getMetaTable() === 'STUDENT'
//                    || $tblGroup->getMetaTable() === 'PROSPECT'
//                    || $tblGroup->getMetaTable() === 'CUSTODY'
//                    || $tblGroup->getMetaTable() === 'TEACHER'
//                    || $tblGroup->getMetaTable() === 'CLUB'
                ){
                    $tblGroup = false;
                }
            }
            $tblGroupList = array_filter($tblGroupList);
        }
        if(false === $tblGroupList
            || empty($tblGroupList)){
            $tblGroupList = array();
        }

        $Content[] = new Center('Auswahl für Personen'
            .new Container(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(Causer::useService()->directRoute(
                        new Form(new FormGroup(new FormRow(array(
                            new FormColumn(new SelectBox('GroupId', '', array('{{ Name }}' => $tblGroupList)), 11)
                        ,
                            new FormColumn(new StandardForm('', new ListingIcon()), 1)
                        )))), $GroupId)
                        , 8)
                ))))
            ));

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Panel('Kategorien:', new Listing($Content))
                        , 6)
                ))
            )
        ));


        return $Stage;
    }

    public function frontendCauserView($GroupId = null)
    {

        $GroupName = '';
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            $GroupName = $tblGroup->getName();
        }
        $Stage = new Stage('Beitragsverursacher', 'Gruppe: '.$GroupName);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(ApiCauser::receiverModal()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $this->getCauserTable($GroupId)
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    public function getCauserTable($GroupId)
    {

        $TableContent = array();
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))){
                $IsDebtorNumberNeed = false;
                if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)){
                    if($tblSetting->getValue() == 1){
                        $IsDebtorNumberNeed = true;
                    }
                }
                $i = 0;
                array_walk($tblPersonList,
                    function(TblPerson $tblPerson) use (&$TableContent, $tblGroup, &$i, $IsDebtorNumberNeed){
                        $Item['Name'] = $tblPerson->getLastFirstName();
                        $Item['ContentRow'] = '';
//                    $Item['Option'] = new Standard('', '', new Edit());
                        // Herraussuchen aller Beitragsarten die aktuell eingestellt werden müssen
                        $ContentSingleRow = array();
                        if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauser($tblPerson))){
                            $ContentSingleRow[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('Beitragszahler', 3),
                                new LayoutColumn('Zahlart', 2),
                                new LayoutColumn('Bank Info', 2),
                                new LayoutColumn('Beitragsart', 3),
                                new LayoutColumn('Preis', 2),
                            ))));
                            foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                                $Debtor = '';
                                $PaymentType = '';
                                $ItemName = '';

                                if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor())){
                                    $Debtor = $tblPersonDebtor->getLastFirstName();
                                    if($IsDebtorNumberNeed && !($tblDebtorNumber = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor))){
                                        $Debtor .= ' '.new DangerText(new ToolTip(new Info(),
                                                'Keine Debit.-Nr. hinterlegt'));
                                    }
                                }
                                if(($tblPaymentType = $tblDebtorSelection->getServiceTblPaymentType())){
                                    $PaymentType = str_replace('SEPA-', '', $tblPaymentType->getName());
                                }
                                if(($tblItem = $tblDebtorSelection->getServiceTblItem())){
                                    $ItemName = $tblItem->getName();
                                }

                                if($PaymentType == 'Lastschrift'){
                                    $BankStatus = new DangerText(new ToolTip(new Disable(),
                                        'Beitragszahler: Keine Bankdaten hinterlegt'));
                                    if(($tblBankAccount = $tblDebtorSelection->getTblBankAccount())){
                                        $BankStatus = new ToolTip(new DangerText(new Info()),
                                            'Beitragszahler: Referenznummer fehlt');
                                    }
                                    if(($tblBankReference = $tblDebtorSelection->getTblBankReference())){
                                        $BankStatus = new ToolTip(new SuccessText(new Info()), 'Beitragszahler OK');
                                    }
                                } else {
                                    $BankStatus = new WarningText(new ToolTip(new Minus(),
                                        'Beitragszahler: Benötigt keine Bankdaten'));
                                }

                                $ItemPriceString = 'Nicht verfügbar!';
                                if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                                    if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                                        $ItemPriceString = $tblItemCalculation->getPriceString();
                                    }
                                } elseif($tblDebtorSelection->getValuePriceString() != '0.00 €') {
                                    $ItemPriceString = $tblDebtorSelection->getValuePriceString();
                                }

                                $ContentSingleRow[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn($Debtor, 3),
                                    new LayoutColumn($PaymentType, 2),
                                    new LayoutColumn($BankStatus, 2),
                                    new LayoutColumn($ItemName, 3),
                                    new LayoutColumn($ItemPriceString, 2),
//                                    new LayoutColumn(new Standard('', '', new Edit()). new Standard('', '', new Remove()), 2)
                                ))));
                            }
                            $Item['ContentRow'] = new Listing($ContentSingleRow);
                        }

                        $Item['Option'] = new Standard('', __NAMESPACE__.'/Edit', new Edit(), array(
                            'GroupId'  => $tblGroup->getId(),
                            'PersonId' => $tblPerson->getId()
                        ));
                        $Item['Option'] .= (new Standard('', ApiCauser::getEndpoint(), new Statistic(), array(), 'Historie'))
                        ->ajaxPipelineOnClick(ApiCauser::pipelineOpenCauserModal($tblPerson->getId()));
                        $i++;
                        // Display Problem
//                    if($i <= 2000){
                        array_push($TableContent, $Item);
//                    }
                    });
            }
        }

        return new TableData($TableContent, null, array(
            'Name'       => 'Person',
            'ContentRow' => 'Zuordnung Beitragszahler',
            'Option'     => '',
        ), array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array("orderable" => false, "targets" => -1),
            ),
        ));
    }

    public function getHistoryByPerson($PersonId = '')
    {

        if(!$tblPerson = Person::useService()->getPersonById($PersonId)){
            return new Warning('Person nicht gefunden');
        }
        $TableContent = array();
        if(($tblInvoiceList = Invoice::useService()->getInvoiceAllByPersonCauser($tblPerson))){
            foreach($tblInvoiceList as $tblInvoice){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                        $item['Item'] = $tblInvoiceItemDebtor->getName();
                        $item['Price'] = $tblInvoiceItemDebtor->getPriceString();
                        $item['Quantity'] = $tblInvoiceItemDebtor->getQuantity();
                        $item['Summary'] = $tblInvoiceItemDebtor->getSummaryPrice();
                        $CheckBox = (new CheckBox('IsPaid', ' ', $tblInvoiceItemDebtor->getId()))->ajaxPipelineOnClick(
                            ApiInvoiceIsPaid::pipelineChangeIsPaid($tblInvoiceItemDebtor->getId()));
                        if(!$tblInvoiceItemDebtor->getIsPaid()){
                            $CheckBox->setChecked();
                        }
                        $item['IsPaid'] = ApiInvoiceIsPaid::receiverIsPaid($CheckBox, $tblInvoiceItemDebtor->getId());
                        array_push($TableContent, $item);
                    }
                }
            }
        }
        if(empty($TableContent)){
            $Table = new Warning('Zur Person '.$tblPerson->getFirstName().' '.$tblPerson->getLastName().'
            sind keine Rechnungen hinterlegt');

        } else {
            $Table = new TableData($TableContent, null, array(
                'InvoiceNumber' => 'Abr.-Nr.',
                'Item'          => 'Beitragsart',
                'Quantity'  => 'Menge',
                'Price'     => new ToolTip('EP', 'Einzelpreis'),
                'Summary'  => new ToolTip('GP', 'Gesamtpreis'),
                'Time'          => 'Abrechnungszeitraum',
                'IsPaid'    => 'Offene Posten'
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(0, 2, 3, 4)),
//                                    array('type' => 'de_date', 'targets' => array(2)),
                    array("orderable" => false, "targets" => -1),
                ),
                'order'      => array(
                    array(0, 'desc')
                ),
            ));
        }


        return ApiInvoiceIsPaid::receiverService()
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Title('Historie', new Bold($tblPerson->getFullName())))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            $Table
                        )
                    )
                ))
            );
    }

    /**
     * @param string $GroupId
     * @param string $PersonId
     *
     * @return Stage|string
     */
    public function frontendCauserEdit($GroupId = '', $PersonId = '')
    {

        $Stage = new Stage('Beitragsverursacher', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/View', new ChevronLeft(),
            array('GroupId' => $GroupId)));

        $ColumnList = array();
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Causer/View', Redirect::TIMEOUT_ERROR,
                    array('GroupId' => $GroupId));
        }

        $ItemList = Item::useService()->getItemAllByPerson($tblPerson);
        // ToDO Implement Receiver
        $ColumnList[] = new LayoutColumn(new Panel('Mandatsreferenznummern',
            ApiBankReference::receiverPanelContent($this->getReferenceContent($PersonId)).
            (new Link('Referenznummer hinzufügen', ApiBankReference::getEndpoint(), new Plus()))
                ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenAddReferenceModal('addBankReference', $PersonId)),
            Panel::PANEL_TYPE_INFO),
            3);

        if($ItemList){
            foreach($ItemList as $tblItem) {
                // Panel Color (unchoosen)
                // ToDO Receiver für den Content
                $ColumnList[] = new LayoutColumn(new Panel($tblItem->getName(),
                        ApiDebtorSelection::receiverPanelContent($this->getItemContent($PersonId, $tblItem->getId())
                            , $tblItem->getId())
                        , Panel::PANEL_TYPE_INFO)
                    , 3);
            }
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $ColumnList
         */
        foreach($ColumnList as $Column) {
            if($LayoutRowCount % 4 == 0){
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($Column);
            $LayoutRowCount++;
        }


        $Stage->setContent(
            ApiBankReference::receiverModal('Hinzufügen einer Referenznummer', 'addBankReference')
            .ApiBankReference::receiverModal('Bearbeiten der Referenznummer', 'editBankReference')
            .ApiBankReference::receiverModal('Entfernen der Referenznummer', 'deleteBankReference')
            .ApiDebtorSelection::receiverModal('Hinzufügen der Beitragszahler', 'addDebtorSelection')
            .ApiDebtorSelection::receiverModal('Bearbeiten der Beitragszahler', 'editDebtorSelection')
            .ApiDebtorSelection::receiverModal('Entfernen der Beitragszahler', 'deleteDebtorSelection')
            .Debtor::useFrontend()->getPersonPanel($PersonId)
            .new Layout(
                new LayoutGroup(
                    $LayoutRowList
                )
            ));

        return $Stage;
    }

    /**
     * @param string $PersonId
     *
     * @return string
     */
    public function getReferenceContent($PersonId = '')
    {

        $content = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            if(($tblReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPerson))){
                $NumberList = array();
                foreach($tblReferenceList as $tblReference) {
                    //ToDO bearbeiten/löschen deaktivieren, wenn sie bereits benutzt werden
                    $NumberList[] = $tblReference->getReferenceNumber().' '.new ToolTip(new Info().'&nbsp;&nbsp;'
                            , 'Gültig ab: '.$tblReference->getReferenceDate()).' '
                        .(new Link('', ApiBankReference::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenEditReferenceModal('editBankReference',
                                $PersonId, $tblReference->getId()))
                        .' | '
                        .(new Link(new DangerText(new Disable()), ApiBankReference::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenDeleteReferenceModal('deleteBankReference',
                                $PersonId, $tblReference->getId()));
                }

                if(!empty($NumberList)){
                    $content = implode('<br/>', $NumberList);
                }
            }
        }
        return $content;
    }

    /**
     * @param string $PersonId
     * @param string $ItemId
     * @param bool   $IsOpen
     *
     * @return string
     */
    public function getItemContent($PersonId = '', $ItemId = '', $IsOpen = false)
    {

        $PanelContent = array();
        $Accordion = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblItem = Item::useService()->getItemById($ItemId))){
            if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson,
                $tblItem))){
                $i = 0;
                foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                    $PaymentType = 'Zahlart: ';
                    $BankAccount = 'Bank: ';
                    $Reference = 'REF: ';
                    $Debtor = 'Bezahler: ';

                    $OptionButtons = new PullRight(
                        (new Link('', '', new Pencil()))
                            ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenEditDebtorSelectionModal(
                                'editDebtorSelection', $PersonId, $ItemId, $tblDebtorSelection->getId()))
                        .' | '
                        .(new Link(new DangerText(new Disable()), ''))
                            ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenDeleteDebtorSelectionModal(
                                'deleteDebtorSelection', $PersonId, $ItemId, $tblDebtorSelection->getId()))
                    );

                    if(($tblPaymentType = $tblDebtorSelection->getServiceTblPaymentType())){
                        $PaymentType .= new Bold($tblPaymentType->getName());
                    }
                    $PriceString = new WarningText(new WarningIcon().' kein aktueller Preis');
                    if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                        if($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant)){
                            $PriceString = $tblItemCalculation->getPriceString();
                        }
                        $ItemVariant = $tblItemVariant->getName().': '.new Bold($PriceString);
                        $ItemVariant .= $OptionButtons;
                    } else {
                        $ItemVariant = 'Individueller Preis:'.': '.new Bold($tblDebtorSelection->getValuePriceString());
                        $ItemVariant .= $OptionButtons;
                        $PriceString = $tblDebtorSelection->getValuePriceString();
                    }
                    if(($tblBankAccount = $tblDebtorSelection->getTblBankAccount())){
                        $BankAccount .= new Bold($tblBankAccount->getBankName());
                    }
                    if(($tblBankReference = $tblDebtorSelection->getTblBankReference())){
                        $Reference .= new Bold($tblBankReference->getReferenceNumber());
                    }
                    if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor())){
                        $Debtor .= new Bold($tblPersonDebtor->getLastFirstName());
                    }
                    $PanelContent[] = $PaymentType;
                    $PanelContent[] = $ItemVariant;
                    $PanelContent[] = $BankAccount;
                    $PanelContent[] = $Reference;
//                    $PanelContent[] = $Debtor;
                    /**@var Accordion[] $Accordion */
                    $Accordion[$i] = new Accordion();
                    $Accordion[$i]->addItem($Debtor.new PullRight($PriceString), implode('<br/>', $PanelContent),
                        $IsOpen);
                    $PanelContent = array();
                    $i++;
                }
            } else {
                $PanelContent[] = new Warning(
                    (new Link('Beitragszahler festlegen', '', new PersonIcon()))
                        ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection',
                            $PersonId, $ItemId))
                    .new ToolTip(new Info(), 'Beitragsarten werden ohne Beitragszahler nicht berücksichtigt')
                );
                return implode('<br/>', $PanelContent);
            }
            // Add Button at end of DebtorSelection List
            $Accordion[] = (new Link('Weitere Beitragszahler hinzufügen', '', new PersonIcon()))
                ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection',
                    $PersonId, $ItemId));
        }
        return implode('', $Accordion);
    }
}