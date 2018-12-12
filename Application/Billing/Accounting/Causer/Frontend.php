<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Api\Billing\Accounting\ApiBankReference;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as StandardForm;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
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


        if(($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            $Content[] = new Center('Auswahl für ' . $tblGroup->getName()
                . new Container(new Standard('', __NAMESPACE__ . '/View', new ListingIcon(),
                    array('GroupId' => $tblGroup->getId()))));
        }

        if(($tblGroupList = Group::useService()->getGroupAll())) {
            foreach ($tblGroupList as &$tblGroup) {
                if($tblGroup->getMetaTable() === 'STUDENT'
//                    || $tblGroup->getMetaTable() === 'PROSPECT'
//                    || $tblGroup->getMetaTable() === 'CUSTODY'
//                    || $tblGroup->getMetaTable() === 'TEACHER'
//                    || $tblGroup->getMetaTable() === 'CLUB'
                ) {
                    $tblGroup = false;
                }
            }
            $tblGroupList = array_filter($tblGroupList);
        }
        if(false === $tblGroupList
            || empty($tblGroupList)) {
            $tblGroupList = array();
        }

        $Content[] = new Center('Auswahl für Personen'
            . new Container(
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
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $GroupName = $tblGroup->getName();
        }
        $Stage = new Stage('Beitragsverursacher', 'Gruppe: ' . $GroupName);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $this->getCauserTable($GroupId)
                    )
                ))
            )
        ));

        return $Stage;
    }

    public function getCauserTable($GroupId)
    {

        $TableContent = array();
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $i = 0;
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblGroup, &$i) {
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    $Item['ContentRow'] = ''; // ToDO Anzeige der vorhandenen Zahlungszuweisungen
//                    $Item['Option'] = new Standard('', '', new Edit());
                    // Herraussuchen aller Beitragsarten die aktuell eingestellt werden müssen
                    $ContentSingleRow = array();
                    if(($tblItemGroupList = Item::useService()->getItemGroupByGroup($tblGroup))) {
                        $ContentSingleRow[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Beitragszahler', 4),
                            new LayoutColumn('Bankdaten', 1),
                            new LayoutColumn('Beitragsart', 3),
                            new LayoutColumn('Preis', 2),
                            new LayoutColumn('', 2),
                        ))));
                        foreach ($tblItemGroupList as $tblItemGroup) {
                            if(($tblItem = $tblItemGroup->getTblItem())) {
                                //ToDO clean up DIRTY Test
                                //ToDO Korrekte Variante mit Preis ziehen
                                $tblItemCalculation = false;
                                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
                                    $tblItemVariant = current($tblItemVariantList);
                                    if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))) {
                                        $tblItemCalculation = current($tblItemCalculationList);
                                    }
                                }
                                // ToDO Umbruchtest -> realen Debitor ziehen
                                $Debitor = 'Klara Kolumna';
                                if($tblPerson->getFirstName() == 'Charlotte') {
                                    $Debitor = 'Dr. VanWegenIckeHabNenLangenNamen, NaDirWerdIckeEsNochSoRichtigZeigenWa';
                                }


                                $ContentSingleRow[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn($Debitor, 4),
                                    new LayoutColumn(new SuccessText(new Check()), 1),
                                    new LayoutColumn($tblItem->getName(), 3),
                                    new LayoutColumn(($tblItemCalculation ? $tblItemCalculation->getPriceString() : 'Test'),
                                        2),
//                                    new LayoutColumn(new Standard('', '', new Edit()). new Standard('', '', new Remove()), 2)
                                ))));
                            }
                        }
                        $Item['ContentRow'] = new Listing($ContentSingleRow);
                    }

                    $Item['Option'] = new Standard('', __NAMESPACE__ . '/Edit', new Edit(), array(
                        'GroupId'  => $tblGroup->getId(),
                        'PersonId' => $tblPerson->getId()
                    ));
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
            'ContentRow' => 'Zahlungszuweisungen',
            'Option'     => '',
        ));
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
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__ . '/View', new ChevronLeft(),
            array('GroupId' => $GroupId)));

        $ColumnList = array();
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage . new Redirect('/Billing/Accounting/Causer/View', Redirect::TIMEOUT_ERROR,
                    array('GroupId' => $GroupId));
        }

        $ItemList = Item::useService()->getItemAllByPerson($tblPerson);
        // ToDO Implement Receiver
        $ColumnList[] = new LayoutColumn(new Panel('Mandatsreferenznummern',
            ApiBankReference::receiverPanelContent($this->getReferenceContent($PersonId)) .
            (new Link('Referenznummer hinzufügen', ApiBankReference::getEndpoint(), new Plus()))
                ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenAddReferenceModal('addBankReference', $PersonId)), Panel::PANEL_TYPE_INFO),
            3);

        if($ItemList) {
            foreach ($ItemList as $tblItem) {
                // Panel Color (unchoosen)
                // ToDO Receiver für den Content
                $ColumnList[] = new LayoutColumn(new Panel($tblItem->getName(),
                    $this->getItemContent($PersonId, $tblItem->getId()), Panel::PANEL_TYPE_INFO)
                , 3);
            }
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $ColumnList
         */
        foreach ($ColumnList as $Column) {
            if($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($Column);
            $LayoutRowCount++;
        }


        $Stage->setContent(
            ApiBankReference::receiverModal('Hinzufügen einer Referenznummer', 'addBankReference')
            . ApiBankReference::receiverModal('Bearbeiten der Referenznummer', 'editBankReference')
            . ApiBankReference::receiverModal('Entfernen der Referenznummer', 'deleteBankReference')
            . ApiDebtorSelection::receiverModal('Hinzufügen der Zahlungszuweisung', 'addDebtorSelection')
            . ApiDebtorSelection::receiverModal('Bearbeiten der Zahlungszuweisung', 'editDebtorSelection')
            . ApiDebtorSelection::receiverModal('Entfernen der Zahlungszuweisung', 'deleteDebtorSelection')
            . Debtor::useFrontend()->getPersonPanel($PersonId)
            . new Layout(
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
        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if(($tblReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPerson))) {
                $NumberList = array();
                foreach ($tblReferenceList as $tblReference) {
                    //ToDO bearbeiten/löschen deaktivieren, wenn sie bereits benutzt werden
                    $NumberList[] = new ToolTip($tblReference->getReferenceNumber(), 'Gültig ab: '.$tblReference->getReferenceDate()) . ' '
                        . (new Link('', ApiBankReference::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenEditReferenceModal('editBankReference',
                                $PersonId, $tblReference->getId()))
                        . ' | '
                        . (new Link(new DangerText(new Disable()), ApiBankReference::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiBankReference::pipelineOpenDeleteReferenceModal('deleteBankReference',
                                $PersonId, $tblReference->getId()));
                }

                if(!empty($NumberList)) {
                    $content = implode('<br/>', $NumberList);
                }
            }
        }
        return $content;
    }

    /**
     * @param string $PersonId
     * @param string $ItemId
     *
     * @return array
     */
    public function getItemContent($PersonId = '', $ItemId = '')
    {

        $PanelContent = array();
        if(($tblPerson = Person::useService()->getPersonById($PersonId))
        && ($tblItem = Item::useService()->getItemById($ItemId))) {
            if(($tblDebtorSelection = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                $ItemVariant = 'Varianten: ';
                $Reference = 'REF: ';
                $Debtor = 'Bezahler: ';
                if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())) {
                    $ItemVariant = $tblItemVariant->getName();
                }
                if(($tblBankReference = $tblDebtorSelection->getTblBankReference())){
                    $Reference = 'REF: '.$tblBankReference->getReferenceNumber();
                }
                if(($tblPersonDebtor = $tblDebtorSelection->getServiceTblPerson())){
                    $Debtor = 'Bezahler: '.$tblPersonDebtor->getLastFirstName();
                }
//            } else {
//                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
//                    $ItemVariantList = array();
//                    foreach ($tblItemVariantList as $tblItemVariant) {
//                        $tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant);
//                        $ItemPrice = 'kein aktiver Preis';
//                        if($tblItemCalculation) {
//                            $ItemPrice = $tblItemCalculation->getPriceString();
//                        }
//                        $ItemVariantList[] = $tblItemVariant->getName() . ' - ' . $ItemPrice;
//                    }
//                    $ItemVariant = implode('<br/>', $ItemVariantList);
//                }
                $PanelContent[] = $ItemVariant;
                $PanelContent[] = $Reference;
                $PanelContent[] = $Debtor;
            } else {
                $PanelContent[] = new Warning(
                    (new Link('Bezahlung festlegen', '', new PersonIcon()))
                    ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection', $PersonId, $ItemId))

                    .new ToolTip(new Info(), 'Beitragsarten werden ohne Bezahler nicht berücksichtigt')
                );
            }
        }
        return $PanelContent;
    }
}