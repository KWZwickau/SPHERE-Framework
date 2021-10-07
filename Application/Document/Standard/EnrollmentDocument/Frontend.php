<?php

namespace SPHERE\Application\Document\Standard\EnrollmentDocument;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Document\Standard\EnrollmentDocument
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param Stage $Stage
     */
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/EnrollmentDocument', new Person(), array(),
            'Schulbescheinigung eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/EnrollmentDocument/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Klasse')), '/Document/Standard/EnrollmentDocument/Division',
                new PersonGroup(), array(), 'Schulbescheinigungen einer Klasse'));
        } else {
            $Stage->addButton(new Standard('Klasse', '/Document/Standard/EnrollmentDocument/Division', new PersonGroup(),
                array(), 'Schulbescheinigungen einer Klasse'));
        }
    }

    /**
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public static function frontendSelectDivision(bool $IsAllYears = false, ?string $YearId = null): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Klasse auswählen');
        self::setButtonList($Stage);

        list($yearButtonList, $filterYearList)
            = Term::useFrontend()->getYearButtonsAndYearFilters('/Document/Standard/EnrollmentDocument/Division', $IsAllYears, $YearId);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        empty($yearButtonList) ? '' : $yearButtonList
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            self::loadDivisionTable($filterYearList)
                        )
                    )), new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @param array $filterYearList
     *
     * @return TableData
     */
    public static function loadDivisionTable(array $filterYearList): TableData
    {
        $TableContent = array();
        if (($tblDivisionAll = Division::useService()->getDivisionAll())) {
            foreach ($tblDivisionAll as $tblDivision) {
                // Schuljahre filtern
                if (!empty($filterYearList)
                    && ($tblYearDivision = $tblDivision->getServiceTblYear())
                    && !isset($filterYearList[$tblYearDivision->getId()]))
                {
                    continue;
                }

                $count = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Count'] = $count;

                if ($count > 0) {
                    $Item['Option'] = (new External(
                        '',
                        '/Api/Document/Standard/EnrollmentDocument/CreateMulti',
                        new Download(),
                        array(
                            'DivisionId' => $tblDivision->getId()
                        ),
                        'Schulbescheinigungen herunterladen'
                    ))->__toString();
                } else {
                    $Item['Option'] = '';
                }

                array_push($TableContent, $Item);
            }
        }

        return new TableData($TableContent, null,
            array(
                'Year' => 'Jahr',
                'Division' => 'Klasse',
                'Type' => 'Schulart',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(1,3)),
                    array('orderable' => false, 'targets'   => -1),
                ),
                'order' => array(
                    array(0, 'desc'),
                    array(2, 'asc'),
                    array(1, 'asc')
                ),
                'responsive' => false
            ));
    }

    /**
     * @return Stage
     */
    public static function frontendEnrollmentDocument(): Stage
    {

        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');
        self::setButtonList($Stage);

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('PersonId' => $tblPerson->getId()))
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse',
                                    'Option'   => ''
                                ),
                                array(
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                    ),
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param string|null $PersonId
     *
     * @return Stage
     */
    public function frontendFillEnrollmentDocument(?string $PersonId = null): Stage
    {

        $Stage = new Stage('Schulbescheinigung', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/EnrollmentDocument', new ChevronLeft()));
        $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Data = array();
        if ($tblPerson) {
            $Data = EnrollmentDocument::useService()->getEnrollmentDocumentData($tblPerson);
            $Global->POST['Data'] = $Data;
        }
        $Global->savePost();

        $form = $this->formStudentDocument(isset($Data['Gender']) ? $Data['Gender'] : false);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Schulbescheinigung herunterladen',
            'SPHERE\Application\Api\Document\Standard\EnrollmentDocument\Create',
            new Download(), array('Data' => array('empty')),
            'Schulbescheinigung herunterladen'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                            , 7)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                            , 7),
                        new LayoutColumn(
                            new Title('Vorlage des Standard-Dokuments "Schulbescheinigung"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/Schulbescheinigung.PNG')
                                , ''
                            )
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param $Gender
     *
     * @return Form
     */
    private function formStudentDocument($Gender): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School]', 'Schule',
                                                            'Schule')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolExtended]', 'Zusatz',
                                                            'Zusatz')
                                                        , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressDistrict]', 'Ortsteil',
                                                            'Ortsteil')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                        , 4)
                                                ))
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastName]', 'Vorname, Name',
                                                            'Vorname, Name '.
                                                            ($Gender == 'Männlich'
                                                                ? 'des Schülers'
                                                                : ($Gender == 'Weiblich'
                                                                    ? 'der Schülerin'
                                                                    : 'des Schülers/der Schülerin')
                                                            ))
                                                        , 12)
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geboren am',
                                                            'Geburtstag')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthplace]', 'Geboren in',
                                                            'Geburtsort')
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressDistrict]', 'Ortsteil',
                                                            'Ortsteil')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 2),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort',
                                                            'Ort')
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Division]', 'Besucht zur Zeit die Klasse',
                                                            'Besucht zur Zeit die Klasse')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[LeaveDate]', 'Voraussichtlich bis',
                                                            'Voraussichtlich bis')
                                                        , 6)
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Dokument')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Place]', 'Ort', 'Ort')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new DatePicker('Data[Date]', 'Datum', 'Datum')
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Standard\EnrollmentDocument\Create'
        );
    }
}