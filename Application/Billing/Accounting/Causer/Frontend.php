<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Api\Billing\Accounting\ApiBankReference;
use SPHERE\Application\Api\Billing\Accounting\ApiCauser;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Statistic;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\CustomPanel;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
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
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
     * @return Stage
     */
    public function frontendCauser()
    {

        $Stage = new Stage('Auswahl Gruppe der', 'Beitragsverursacher');

        $Stage->setContent(
            $this->layoutPersonGroupList()
        );


        return $Stage;
    }

    /**
     * @return Layout
     */
    public static function layoutPersonGroupList()
    {

        $tblGroupList = array();
        if(($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())){
            foreach($tblSettingGroupPersonList as $tblSettingGroupPerson){
                $tblGroupList[] = $tblSettingGroupPerson->getServiceTblGroupPerson();
            }
        }
        $tblGroupList = array_filter($tblGroupList);
        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if (!empty($tblGroupList)) {
            /** @var TblGroup $tblGroup */
            foreach ($tblGroupList as $Index => $tblGroup) {

                $countContent = new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Mitglieder'));
                $content =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblGroup->getName()
                                . new Muted(new Small('<br/>' . $tblGroup->getDescription()))
                                , 5),
                            new LayoutColumn(
                                $countContent
                                , 6),
                            new LayoutColumn(
                                new PullRight(
                                    new Standard('', __NAMESPACE__.'/View',
                                        new GroupIcon(),
                                        array('GroupId' => $tblGroup->getId()))
                                ), 1)
                        )
                    )));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Personen in festen Gruppen', $tblGroupLockedList), 6
            ),
            !empty($tblGroupCustomList) ?
                new LayoutColumn(
                    new Panel('Personen in individuellen Gruppen', $tblGroupCustomList), 6) : null
        ))));
    }

    public function frontendCauserView($GroupId = null)
    {
        ini_set('memory_limit', '512M');

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
                if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR)){
                    if($tblSetting->getValue() == 1){
                        $IsDebtorNumberNeed = true;
                    }
                }
                $i = 0;
                if(($tblYear = Term::useService()->getYearByNow())){
                    $tblYear = current($tblYear);
                } else {
                    $tblYear = false;
                }
                array_walk($tblPersonList,
                    function(TblPerson $tblPerson) use (&$TableContent, $tblGroup, &$i, $IsDebtorNumberNeed, $tblYear){
                        $Item = array();
                        $Item['Name'] = $tblPerson->getLastFirstName();
                        //ToDO der Block ist neu aber nicht effizient
                        $tblDivisionCourseList = array();
                        if($tblYear && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                            if($tblStudentEducation->getTblDivision()){
                                $tblDivisionCourseList[] = $tblStudentEducation->getTblDivision()->getDisplayName();
                            }
                            if($tblStudentEducation->getTblCoreGroup()){
                                $tblDivisionCourseList[] = $tblStudentEducation->getTblCoreGroup()->getDisplayName();
                            }
                        }
                        $Item['DivisionCourse'] = implode(', ', $tblDivisionCourseList);
                        $Item['ContentRow'] = '';
//                    $Item['Option'] = new Standard('', '', new Edit());
                        // Herraussuchen aller Beitragsarten die aktuell eingestellt werden müssen
                        $ContentSingleRow = array();
                        if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauser($tblPerson))){
                            $ContentSingleRow[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('Beitragszahler', 3),
                                new LayoutColumn('Zahlungsart', 2),
                                new LayoutColumn('Bank Info', 2),
                                new LayoutColumn('Beitragsart', 3),
                                new LayoutColumn('Preis', 2),
                            ))));
                            foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                                $Debtor = '';
                                $PaymentType = '';
                                $ItemName = '';

                                if(($tblItem = $tblDebtorSelection->getServiceTblItem())){
                                    $tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem);
                                } else {
                                    $tblItemGroupList = array();
                                }
                                $tblPersonGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                                $IsEntryValide = false;
                                if(!empty($tblItemGroupList) && !empty($tblPersonGroupList)){
                                    foreach($tblItemGroupList as $tblItemGroup){
                                        if(($tblItemTempGroup = $tblItemGroup->getServiceTblGroup())){
                                            foreach($tblPersonGroupList as $tblGroupTemp){
                                                if($tblGroupTemp->getId() == $tblItemTempGroup->getId()){
                                                    $IsEntryValide = true;
                                                }
                                            }
                                        }
                                    }
                                }
                                // Einträge die aufgrund nicht zutreffender Gruppen übersprungen werden
                                if(!$IsEntryValide){
                                    continue;
                                }

                                if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor())){
                                    $Debtor = $tblPersonDebtor->getLastFirstName();
                                    if($IsDebtorNumberNeed && !($tblDebtorNumber = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor))){
                                        $Debtor .= ' '.new DangerText(new ToolTip(new Info(),
                                                'Keine Debitoren-Nr. hinterlegt'));
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
                                            'Beitragszahler: Mandatsreferenznummer fehlt');
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
                            //Für komplett Übersprungene Zahlungszuweisungen soll kein Header erzeugt werden
                            if(count($ContentSingleRow) > 1){
                                $Item['ContentRow'] = new Listing($ContentSingleRow);
                            }
                        }

                        $Item['Option'] = new Standard('', __NAMESPACE__.'/Edit', new Edit(), array(
                            'GroupId'  => $tblGroup->getId(),
                            'PersonId' => $tblPerson->getId()
                        ), 'Bearbeiten');
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
            'Name'           => 'Person',
            'DivisionCourse' => 'Klasse / Stammgruppe',
            'ContentRow'     => 'Zuordnung Beitragszahler',
            'Option'         => '',
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
            foreach($tblInvoiceList as $tblInvoice) {
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                $item['BasketType'] = '';
                if(($tblBasket = $tblInvoice->getServiceTblBasket())
                && ($tblBasketType = $tblBasket->getTblBasketType())){
                    $item['BasketType'] = $tblBasketType->getName();
                }
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        $item['Item'] = $tblInvoiceItemDebtor->getName();
                        $item['Price'] = $tblInvoiceItemDebtor->getPriceString('€', true);
                        $item['Quantity'] = $tblInvoiceItemDebtor->getQuantity();
                        $item['Summary'] = $tblInvoiceItemDebtor->getSummaryPriceFrontend();
                        $ColumnContent = Invoice::useService()->getIsPaidColumnContent($tblInvoiceItemDebtor, false);
                        $item['IsPaid'] = ApiInvoiceIsPaid::receiverIsPaid($ColumnContent, $tblInvoiceItemDebtor->getId());
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
                'InvoiceNumber' => 'Rechnung Nr.',
                'Time'          => 'Abr. '.new ToolTip(new Info(), 'Abrechnungszeitraum'),
                'Item'          => 'Beitragsart',
                'BasketType'    => 'Typ',
                'Quantity'      => 'Menge',
                'Price'         => new ToolTip('EP', 'Einzelpreis'),
                'Summary'       => new ToolTip('GP', 'Gesamtpreis'),
                'IsPaid'        => 'Offene Posten'
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(0, 2, 3, 4)),
//                                    array('type' => 'de_date', 'targets' => array(2)),
                    array("orderable" => false, "targets" => -1),
                ),
                'order'      => array(
                    array(0, 'desc')
                ),
                'lengthMenu' => [[10, 12, 25, 50, 100, -1], [10, 12, 25, 50, 100, "All"]],
                'pageLength' => 12
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
        if($GroupId){
            $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/View', new ChevronLeft(), array('GroupId' => $GroupId)));
        } else {
            $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
        }

        $ColumnList = array();
        $ColumnHideList = array();
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $Stage->setContent(new Warning('Person nicht gefunden'));
            if($GroupId){
                return $Stage.new Redirect('/Billing/Accounting/Causer/View', Redirect::TIMEOUT_ERROR, array('GroupId' => $GroupId));
            } else {
                return $Stage.new Redirect('/Billing/Accounting/Causer', Redirect::TIMEOUT_ERROR);
            }
        }

        $tblItemList = Item::useService()->getItemAllByPerson($tblPerson);
        $ColumnList[] = new LayoutColumn(new Panel('Mandatsreferenznummer',
            ApiBankReference::receiverPanelContent($this->getReferenceContent($PersonId)).
            (new Link('Mandatsreferenznummer hinzufügen', ApiBankReference::getEndpoint(), new Plus()))
                ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenAddReferenceModal('addBankReference', $PersonId)),
            Panel::PANEL_TYPE_INFO),
            3);

        if($tblItemList){
            $tblItemList = $this->getSorter($tblItemList)->sortObjectBy('Name');
            /** @var TblItem $tblItem */
            foreach($tblItemList as $tblItem) {
                $panelColor = Panel::PANEL_TYPE_INFO;
                $notActiveInfo = '';
                if(!$tblItem->getIsActive()){
                    $panelColor = Panel::PANEL_TYPE_WARNING;
                    $notActiveInfo = new Muted(new Small(' (deaktiviert)'));
                }

                if((Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                    $ColumnList[] = new LayoutColumn(new Panel($tblItem->getName().$notActiveInfo,
                        ApiDebtorSelection::receiverPanelContent($this->getItemContent($PersonId, $tblItem->getId())
                            , $tblItem->getId())
                        , $panelColor)
                    , 3);
                }
            }
            foreach($tblItemList as $tblItem) {
                if(!$tblItem->getIsActive()){
                    // deaktivierte nicht mehr auswählbar
                    continue;
                }
                if(!(Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                    $ColumnHideList[] = new LayoutColumn(new Panel($tblItem->getName(),
                        ApiDebtorSelection::receiverPanelContent($this->getItemContent($PersonId, $tblItem->getId())
                            , $tblItem->getId())
                        , Panel::PANEL_TYPE_DEFAULT)
                    .'<div style="height: 5px"></div>'
                    , 3);
                }
            }
        }
        $LayoutRowList = $this->getFrontendPanelList($ColumnList);
        $LayoutRowHideList = $this->getFrontendPanelList($ColumnHideList);

        $SiblingPanel = '';
        $tblType = Relationship::useService()->getTypeByName('Geschwisterkind');
        $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType);
        $tblPersonSiblingList = array();
        if($tblRelationshipList){
            foreach($tblRelationshipList as $tblRelationship){
                $tblPersonFrom = $tblRelationship->getServiceTblPersonFrom();
                $tblPersonTo = $tblRelationship->getServiceTblPersonTo();
                if($tblPersonFrom && $tblPersonTo){
                    if($tblPersonFrom->getId() != $tblPerson->getId()){
                        $tblPersonSiblingList[] = $tblPersonFrom;
                    } elseif ($tblPersonTo->getId() != $tblPerson->getId()){
                        $tblPersonSiblingList[] = $tblPersonTo;
                    }
                }
            }
        }
        if(!empty($tblPersonSiblingList)){
            $ColumnSiblingList = array();
            $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $tblGroupProspect = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
            foreach($tblPersonSiblingList as $tblPersonSibling){
                $ContentString = '';
                $isLink = false;
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonSibling);
                if($tblGroupStudent && Group::useService()->existsGroupPerson($tblGroupStudent, $tblPersonSibling)){
                    $ContentString .= 'Schüler';
                    $isLink = true;
                } elseif ($tblGroupProspect && Group::useService()->existsGroupPerson($tblGroupProspect, $tblPersonSibling)) {
                    $ContentString .= 'Interessent';
                    $isLink = true;
                }
                if($ContentString && $tblStudentEducation && ($tblDivision = $tblStudentEducation->getTblDivision())){
                    $TypeName = '';
                    if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                        $TypeName = $tblSchoolType->getName();
                    }
                    $ContentString .= ' '.$tblDivision->getDisplayName().' - '.$TypeName;
                }

                $CustomPanel = (new CustomPanel(new PullClear($tblPersonSibling->getFullName()), $ContentString));
                if($isLink){
                    $CustomPanel->setLink((new Link('', '/Billing/Accounting/Causer/Edit', new \SPHERE\Common\Frontend\Icon\Repository\Link(),
                        array('PersonId' => $tblPersonSibling->getId())))->setExternal());
                }

                $ColumnSiblingList[] = new LayoutColumn($CustomPanel, 4);
            }
            $RowSiblingList = $this->getFrontendPanelList($ColumnSiblingList, 3);
            $SiblingPanel = (new CustomPanel(new InfoText('Geschwisterkinder '.new Bold(count($ColumnSiblingList))),
                new Layout(new LayoutGroup($RowSiblingList))))->setAccordeon();
        }

        //
        $UnusedItemPanel = (new CustomPanel(new InfoText('Weitere mögliche '.new Bold('Beitragsarten')), new Layout(new LayoutGroup($LayoutRowHideList))))
            ->setAccordeon((count($ColumnList) > 1? false : true));

        $Stage->setContent(
            ApiBankReference::receiverModal('Hinzufügen einer Mandatsreferenznummer', 'addBankReference')
            .ApiBankReference::receiverModal('Bearbeiten der Mandatsreferenznummer', 'editBankReference')
            .ApiBankReference::receiverModal('Entfernen der Mandatsreferenznummer', 'deleteBankReference')
            .ApiDebtorSelection::receiverModal('Hinzufügen der Beitragszahler', 'addDebtorSelection')
            .ApiDebtorSelection::receiverModal('Bearbeiten der Beitragszahler', 'editDebtorSelection')
            .ApiDebtorSelection::receiverModal('Entfernen der Beitragszahler', 'deleteDebtorSelection')
            .($SiblingPanel
                ? new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(Debtor::useFrontend()->getPersonPanel($PersonId), 3),
                    new LayoutColumn($SiblingPanel, 9)
                ))))
                : Debtor::useFrontend()->getPersonPanel($PersonId))
            .new Layout(new LayoutGroup($LayoutRowList))
            .$UnusedItemPanel);

        return $Stage;
    }

    /**
     * @param LayoutColumn[] $ColumnList
     * @param int            $ColumnCount
     *
     * @return LayoutRow[]
     */
    private function getFrontendPanelList(array $ColumnList,int $ColumnCount = 4)
    {
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $ColumnList
         */
        foreach($ColumnList as $Column) {
            if($LayoutRowCount % $ColumnCount == 0){
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($Column);
            $LayoutRowCount++;
        }
        return $LayoutRowList;
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
                    $Description = '';
                    if($tblReference->getDescription()){
                        $Description = '<br/>'.$tblReference->getDescription();
                    }
                    $NumberList[] = $tblReference->getReferenceNumber().' '.(new ToolTip(new Info().'&nbsp;&nbsp;'
                            , htmlspecialchars('Gültig ab: '.$tblReference->getReferenceDate()).' '.$Description))->enableHtml()
                        .(new Link('', ApiBankReference::getEndpoint(), new Pencil(), array(), 'Mandatsreferenznummer bearbeiten'))
                            ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenEditReferenceModal('editBankReference',
                                $PersonId, $tblReference->getId()))
                        .' | '
                        .(new Link(new DangerText(new Disable()), ApiBankReference::getEndpoint(), null , array(), 'Mandatsreferenznummer entfernen'))
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
     * @param bool   $IsOpen  // Accordion
     *
     * @return string
     */
    public function getItemContent($PersonId = '', $ItemId = '', $IsOpen = false)
    {

        $PanelContent = array();
//        $Accordion = array();
        $CustomPanel = array();
        if(($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblItem = Item::useService()->getItemById($ItemId))){
            if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson,
                $tblItem))){
                $i = 0;
                foreach($tblDebtorSelectionList as $tblDebtorSelection) {
                    $PaymentType = 'Zahlungsart: ';
                    $BankAccount = 'Bank: ';
                    $Reference = 'Mandatsreferenznummer: ';
                    $Debtor = new InfoText('Bezahler: ');
                    $PeriodPayType = 'Zahlungszeitraum: ';
                    $FromDate = 'Beitragspflicht ab: ';
                    $ToDate = 'Beitragspflicht bis: ';

                    if(($tblDebtorPeriodType = $tblDebtorSelection->getTblDebtorPeriodType())){
                        $PeriodPayType .= new Bold($tblDebtorPeriodType->getName());
                    }
                    if($tblDebtorSelection->getFromDate()){
                        $FromDate .= new Bold($tblDebtorSelection->getFromDate());
                    } else {
                        $FromDate .= new Bold('---');
                    }
                    if($tblDebtorSelection->getToDate()){
                        $ToDate .= new Bold($tblDebtorSelection->getToDate());
                    } else {
                        $ToDate .= new Bold('kein Enddatum');
                    }
                    if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor())){
                        $Debtor .= new InfoText(new Bold($tblPersonDebtor->getSalutation().' '.substr($tblPersonDebtor->getFirstName(), 0, 1) .'. '
                            .$tblPersonDebtor->getLastName())); // .new Link(new PersonIcon(), '') Wäre möglich aber Gruppenproblematik
                    }

                    $OptionButtons = new PullRight(
                        (new Link('', '', new Pencil(), array(), 'Beitragszahler bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenEditDebtorSelectionModal(
                                'editDebtorSelection', $PersonId, $ItemId, $tblDebtorSelection->getId()))
                        .' | '
                        .(new Link(new DangerText(new Disable()), 'Beitragszahler entfernen'))
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
//                    if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor())){
//                        $Debtor .= new Bold($tblPersonDebtor->getLastFirstName());
//                    }
                    $PanelContent[] = $PaymentType;
                    $PanelContent[] = $ItemVariant;
                    $PanelContent[] = $BankAccount;
                    $PanelContent[] = $Reference;
                    $PanelContent[] = $PeriodPayType;
                    $PanelContent[] = $FromDate;
                    $PanelContent[] = $ToDate;
//                    $PanelContent[] = $Debtor;
//                    /**@var Accordion[] $Accordion */
//                    $Accordion[$i] = new Accordion();
//                    $Accordion[$i]->addItem(new PullClear($Debtor.new PullRight($PriceString)), implode('<br/>', $PanelContent),
//                        $IsOpen);
                    $CustomPanel[$i] = (new CustomPanel(new PullClear($Debtor.new PullRight($PriceString)), implode('<br/>', $PanelContent)))->setAccordeon($IsOpen);
                    $PanelContent = array();
                    $i++;
                }
            } else {
                $PanelContent[] = // new Warning(
                    (new Link('Beitragszahler festlegen', '', new PersonIcon()))
                        ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection',
                            $PersonId, $ItemId))
                    .new ToolTip(new Info(), 'Beitragsarten werden ohne Beitragszahler nicht berücksichtigt');
                // , null, false, 3, 3);
                return implode('<br/>', $PanelContent);
            }
            // Add Button at end of DebtorSelection List
//            $Accordion[] = (new Link('Weitere Beitragszahler hinzufügen', '', new PersonIcon()))
            $CustomPanel[] = (new Link('Weitere Beitragszahler hinzufügen', '', new PersonIcon()))
                ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection',
                    $PersonId, $ItemId));
        }
        return implode('', $CustomPanel);
    }
}