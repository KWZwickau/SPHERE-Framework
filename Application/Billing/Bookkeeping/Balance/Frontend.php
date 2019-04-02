<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Api\Billing\Inventory\ApiDocument;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Document\Document;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{
    const FILTER_CLASS = 1;
    const FILTER_GROUP = 2;
    const FILTER_PERSON = 3;

    public function frontendBalance()
    {

        $Stage = new Stage('Dashboard', 'Belegdruck');
        return $Stage;
    }

    public function frontendBalanceExcel($Balance = array())
    {

        $Stage = new Stage('Belegdruck', 'Serienbrief');

        // Vorauswahl für Schulgeld deaktiviert SSW-537
//        if(!isset($_POST['Balance']['Item']) && ($tblItem = Item::useService()->getItemByName('Schulgeld'))){
//            $_POST['Balance']['Item'] = $tblItem->getId();
//        }
        if(!isset($Balance['Year'])){
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['From'])){
            $_POST['Balance']['From'] = '1';
        }
        if(!isset($Balance['To'])){
            $_POST['Balance']['To'] = '12';
        }
        // Standard Download
        $Download = (new PrimaryLink('Herunterladen', '', new Download()))->setDisabled();
        $tableContent = array();
        if(!empty($Balance)){

            if(($tblItem = Item::useService()->getItemById($Balance['Item']))){
                $PriceList = Balance::useService()->getPriceListByItemAndYear($tblItem, $Balance['Year'],
                    $Balance['From'], $Balance['To'], $Balance['Division']);
                $tableContent = Balance::useService()->getTableContentByPriceList($PriceList);
                $Download = new PrimaryLink('Herunterladen', '/Api/Billing/Balance/Balance/Print/Download',
                    new Download(), array(
                        'ItemId'     => $tblItem->getId(),
                        'Year'       => $Balance['Year'],
                        'From'       => $Balance['From'],
                        'To'         => $Balance['To'],
                        'DivisionId' => $Balance['Division'],
                    ));
            }
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';
        if(empty($Balance)){
            $Table = new Info('Bitte benutzen Sie die Filterung');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }
        if(!empty($tableContent)){
            $Table = new TableData($tableContent, null, array(
                'Debtor' => 'Beitragszahler',
                'Causer' => 'Beitragsverursacher',
                'Value'  => 'Summe',
                'Info'  => 'Anmerkung',
            ), array(
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(2)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
                // First column should not be with Tabindex
                // solve the problem with responsive false
                "responsive" => false,
            ));
            $Space = '';
        } else {
            $Download->setDisabled();
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formBalanceFilter())
                ),
                new LayoutRow(
                    new LayoutColumn(new Container($Download).new Container('&nbsp;'))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    public function formBalanceFilter()
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1, 1);
        $MonthList = Invoice::useService()->getMonthList();
        $tblItemAll = Item::useService()->getItemAll();

        $tblYear = false;
        $tblDivisionList = array();
        if(($tblYearList = Term::useService()->getYearByNow())){
            $tblYear = current($tblYearList);
        }
        if($tblYear){
            if(!($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))){
                $tblDivisionList = array();
            }
        }
        return new Well(
            new Title('Filterung für Belegdruck', '').
            new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 3),
                        new FormColumn(new SelectBox('Balance[From]', 'Zeitraum Von', $MonthList, null, true, null), 3),
                        new FormColumn(new SelectBox('Balance[To]', 'Zeitraum Bis', $MonthList, null, true, null), 3),
                        new FormColumn(new SelectBox('Balance[Division]', 'Klasse '.new ToolTip(new InfoIcon(),
                                'Klassen aus dem aktuellem Schuljahr (Datum '.(new \DateTime())->format('d.m.Y').')')
                            , array( '{{ tblLevel.Name }} {{ Name }}' => $tblDivisionList), null, true, null), 3),
                    )),
                    new FormRow(array(
                        new FormColumn((new SelectBox('Balance[Item]', 'Beitragsart',
                            array('{{ Name }}' => $tblItemAll)))->setRequired(), 3),
                    )),
                    new FormRow(
                        new FormColumn(new Primary('Filtern', new Filter()))
                    )
                ))
            ));
    }

    public function frontendMonthOverview($Balance = array())
    {

        $Now = new \DateTime();
        if(!isset($Balance['Year'])){
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['Month'])){
            $_POST['Balance']['Month'] = (int)$Now->format('m');
        }
        $Stage = new Stage('Summierung der Beitragsarten','in einem Monat');

        // Standard Download
        $Download = (new PrimaryLink('Herunterladen', '', new Download()))->setDisabled();
        $DownloadYear = (new PrimaryLink('Jahr Herunterladen', '', new Download()))->setDisabled();
        $tableContent = array();
        if(!empty($Balance)){
            $resultList =  Balance::useService()->getPriceSummaryByMonth($Balance['Year'], $Balance['Month']);
            if(!empty($resultList)){
                foreach($resultList as $result){
                    $item['Name'] = $result['Name'];
                    $item['Time'] = $result['Year'].str_pad($result['Month'], 2, '0', STR_PAD_LEFT);
                    $item['Summary'] = Balance::useService()->getPriceString($result['Summary']);
                    array_push($tableContent, $item);
                }
            }
//            $tableContent = Balance::useService()->getTableContentByPriceList($PriceList);
            $Download = new PrimaryLink('Monatsansicht herunterladen', '/Api/Billing/Balance/Balance/MonthOverView/Download',
                new Download(), array(
                    'Year'   => $Balance['Year'],
                    'Month'   => $Balance['Month']
                ));
            $DownloadYear = new PrimaryLink('Jahr herunterladen', '/Api/Billing/Balance/Balance/YearOverView/Download',
                new Download(), array(
                    'Year'   => $Balance['Year']
                ));
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';
        if(empty($Balance)){
            $Table = new Info('Bitte benutzen Sie die Filterung');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }
        if(!empty($tableContent)){
            $Table = new TableData($tableContent, null, array(
                'Name' => 'Beitragsart',
                'Time' => 'Abrechnungszeitraum',
                'Summary' => 'Summe',
            ), array(
                'columnDefs' => array(
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(1)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
            ));
            $Space = '';
        } else {
            $Download->setDisabled();
            $DownloadYear->setDisabled();
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formMonthOverviewFilter())
                ),
                new LayoutRow(
                    new LayoutColumn(new Container(array($Download,$DownloadYear)).new Container('&nbsp;'))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    public function formMonthOverviewFilter()
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1, 1);
        $MonthList = Invoice::useService()->getMonthList();

        return new Well(
            new Title('Filterung', '').
            new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 4),
                        new FormColumn(new SelectBox('Balance[Month]', 'Monat', $MonthList, null, true, null), 4),
                    )),
                    new FormRow(
                        new FormColumn(new Primary('Filtern', new Filter()))
                    )
                ))
            ));
    }

    /**
     * @param array $Balance
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendBalancePdf($Balance = array())
    {

        $Stage = new Stage('Belegdruck', 'Pdf');

        if(!isset($_POST['Balance']['Item']) && ($tblItem = Item::useService()->getItemByName('Schulgeld'))){
            $_POST['Balance']['Item'] = $tblItem->getId();
        }
        if(!isset($Balance['Year'])){
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['From'])){
            $_POST['Balance']['From'] = '1';
        }
        if(!isset($Balance['To'])){
            $_POST['Balance']['To'] = '12';
        }

        if(!isset($Balance['Filter'])){
            $_POST['Balance']['Filter'] = self::FILTER_CLASS;
        }

        $filterForm = $this->getFilterForm($Balance);
        $filterBlock = ApiDocument::receiverBlock($filterForm, 'changeFilter');

        $tableContent = array();
        $tblItem = false;
        $error = false;
        if(!empty($Balance)){
            // todo single person
            $tblDivision = false;
            $tblGroup = false;
            if (isset($Balance['Division'])) {
                if (!($tblDivision = Division::useService()->getDivisionById($Balance['Division']))) {
                    $filterForm->setError('Balance[Division]', 'Bitte wählen Sie eine Klasse aus');
                    $error = true;
                }
            }
            if (isset($Balance['Group'])) {
                if (!($tblGroup = Group::useService()->getGroupById($Balance['Group']))) {
                    $filterForm->setError('Balance[Group]', 'Bitte wählen Sie eine Gruppe aus');
                    $error = true;
                }
            }
            if (($tblItem = Item::useService()->getItemById($Balance['Item']))) {
                if (!$error) {
                    $PriceList = Balance::useService()->getPriceListByItemAndYear(
                        $tblItem,
                        $Balance['Year'],
                        $Balance['From'],
                        $Balance['To'],
                        $tblDivision ? $tblDivision->getId() : '0',
                        $tblGroup ? $tblGroup->getId() : '0');
                    $tableContent = Balance::useService()->getTableContentByPriceList($PriceList);
                }
            } else {
                $filterForm->setError('Balance[Item]', 'Bitte wählen Sie eine Betragsart aus');
                $error = true;
            }
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';
        if (empty($Balance) || $error) {
            $Table = new Info('Bitte benutzen Sie die Filterung');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }
        if (!empty($tableContent)) {
            $Table = new TableData($tableContent, null, array(
                'Debtor' => 'Beitragszahler',
                'Causer' => 'Beitragsverursacher',
                'Value'  => 'Summe',
            ), array(
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(2)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
                // First column should not be with Tabindex
                // solve the problem with responsive false
                "responsive" => false,
            ));

            if ($tblItem) {
                if (($tblDocumentList = Document::useService()->getDocumentAllByItem($tblItem))
                ) {
                    $Table .= new Well($this->getPdfForm($tblDocumentList, $Balance));
                } else {
                    $Table .= new Warning('Für die Beitragsart: ' . $tblItem->getName() . ' ist kein Beleg eingestellt.', new Exclamation());
                }
            }
            $Space = '';
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(new Well(new Title('Filterung für Belegdruck', '') . $filterBlock))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param $Balance
     *
     * @return IFormInterface
     */
    public function getFilterForm($Balance)
    {

        $filterOptions = array(
            self::FILTER_CLASS => 'Klasse',
            self::FILTER_GROUP => 'Gruppe',
            self::FILTER_PERSON => 'Einzel-Person'
        );

        if (isset($Balance['Filter'])) {
            $Filter = $Balance['Filter'];
        } else {
            $Filter = self::FILTER_CLASS;
        }

        if ($Filter) {
            if ($Filter == self::FILTER_CLASS || $Filter == self::FILTER_GROUP) {
                $YearList = Invoice::useService()->getYearList(1, 1);
                $MonthList = Invoice::useService()->getMonthList();
                $tblItemAll = Item::useService()->getItemAll();

                $tblYear = false;
                $tblDivisionList = array();
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    $tblYear = current($tblYearList);
                }
                if ($tblYear) {
                    if (!($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                        $tblDivisionList = array();
                    }
                }

                if ($Filter == self::FILTER_CLASS) {
                    $selectBox = (new SelectBox('Balance[Division]', 'Klasse ' . new ToolTip(new InfoIcon(),
                            'Klassen aus dem aktuellem Schuljahr (Datum ' . (new \DateTime())->format('d.m.Y') . ')'),
                        array('{{ tblLevel.Name }} {{ Name }}' => $tblDivisionList), null, true,
                        null))->setRequired();
                } else {
                    $groups = array();
                    if (($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())) {
                        foreach ($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                            if (($tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson())) {
                                $groups[] = $tblGroup;
                            }
                        }
                    }

                    $selectBox = (new SelectBox('Balance[Group]', 'Gruppe ',
                        array('{{ Name }}' => $groups), null, true,
                        null))->setRequired();
                }

                return
                    new Form(
                        new FormGroup(array(
                            new FormRow(
                                new FormColumn(
                                    new Panel(
                                        'Filter für',
                                        (new SelectBox('Balance[Filter]', '',
                                            $filterOptions))->ajaxPipelineOnChange(ApiDocument::pipelineChangeFilter()),
                                        Panel::PANEL_TYPE_PRIMARY
                                    )
                                )
                            ),
                            new FormRow(array(
                                new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 3),
                                new FormColumn(new SelectBox('Balance[From]', 'Zeitraum Von', $MonthList, null, true,
                                    null), 3),
                                new FormColumn(new SelectBox('Balance[To]', 'Zeitraum Bis', $MonthList, null, true,
                                    null), 3),
                                new FormColumn($selectBox, 3),
                            )),
                            new FormRow(array(
                                new FormColumn((new SelectBox('Balance[Item]', 'Beitragsart',
                                    array('{{ Name }}' => $tblItemAll)))->setRequired(), 3),
                            )),
                            new FormRow(
                                new FormColumn(new Primary('Filtern', new Filter()))
                            )
                        ))
                    );
            }
                // todo Filter für Einzel-Person
//            } elseif ($Filter = self::FILTER_PERSON) {
//
//            }
        }

        return null;
    }

    public function getPdfForm($tblDocumentList, $Balance = null, $Data = null)
    {

        if ($Data === null) {
            $global = $this->getGlobal();

            $firstDocument = reset($tblDocumentList);
            $global->POST['Data']['Document'] = $firstDocument->getId();
            $global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

            if (($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll())) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = reset($tblResponsibilityAll);
                if (($tblCompany = $tblResponsibility->getServiceTblCompany())) {
                    $global->POST['Data']['CompanyName'] = $tblCompany->getName();
                    $global->POST['Data']['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    if (($tblAddress = $tblCompany->fetchMainAddress())
                        && ($tblCity = $tblAddress->getTblCity())
                    ) {
                        $global->POST['Data']['CompanyDistrict'] = $tblCity->getDistrict();
                        $global->POST['Data']['CompanyStreet'] = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
                        $global->POST['Data']['CompanyCity'] = $tblCity->getCode() . ' ' . $tblCity->getName();

                        $global->POST['Data']['Location'] = $tblCity->getName();
                    }
                }
            }

            // Filterdaten
            if ($Balance) {
                $global->POST['Data']['Item'] = $Balance['Item'];
                $global->POST['Data']['Year'] = $Balance['Year'];
                $global->POST['Data']['From'] = $Balance['From'];
                $global->POST['Data']['To'] = $Balance['To'];
                if (isset($Balance['Division'])) {
                    $global->POST['Data']['Division'] = $Balance['Division'];
                }
                if (isset($Balance['Group'])) {
                    $global->POST['Data']['Group'] = $Balance['Group'];
                }
            }

            $global->savePost();
        }

        return new Form(
            new FormGroup(array(
                // Filterdaten
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[Item]')
                    , 1),
                    new FormColumn(
                        new HiddenField('Data[Year]')
                    , 1),
                    new FormColumn(
                        new HiddenField('Data[From]')
                    , 1),
                    new FormColumn(
                        new HiddenField('Data[To]')
                    , 1),
                    new FormColumn(
                        new HiddenField('Data[Division]')
                    , 1),
                    new FormColumn(
                        new HiddenField('Data[Group]')
                    , 1),
                )),
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen des Belegs')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Beleg', array(
                            new SelectBox('Data[Document]', 'Beleg', array('{{ Name }}' => $tblDocumentList)),
                            new TextField('Data[Location]', '', 'Ort', new MapMarker()),
                            new DatePicker('Data[Date]', '', 'Datum', new Calendar())
                        ), Panel::PANEL_TYPE_INFO)
                        , 12),
                )),
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen des Schulträger')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Name des Schulträgers',array(
                            new TextField('Data[CompanyName]', '', 'Name'),
                            new TextField('Data[CompanyExtendedName]', '', 'Namenszusatz')
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Adressinformation des Schulträgers',array(
                            new TextField('Data[CompanyDistrict]', '', 'Ortsteil'),
                            new TextField('Data[CompanyStreet]', '', 'Straße'),
                            new TextField('Data[CompanyCity]', '', 'PLZ/Ort'),
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                )),
            )),
            new Primary('Herunterladen', new Download(), true), '/Api/Document/Standard/BillingDocument/Create'
        );
    }
}
