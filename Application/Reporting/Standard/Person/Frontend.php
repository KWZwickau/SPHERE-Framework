<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $View = new Stage();
        $View->setTitle('Auswertungen');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @param null $DivisionId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten');

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            //ToDo JohK Schuljahr

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                }
            }
        }

        $Stage->setContent(
            new Well(
                Person::useService()->getClass(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Division]', 'Klasse', array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }} - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Standard/Person/ClassList')
            )
            .
            ($DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'Salutation' => 'Anrede',
                        'FirstName' => 'Vorname',
                        'LastName' => 'Name',
                        'Denomination' => 'Konfession',
                        'Birthday' => 'Geburtsdatum',
                        'Birthplace' => 'Geburtsort',
                        'Address' => 'Adresse',
                    ),
                    null
                ) : '')
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendExtendedClassList($DivisionId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'erweiterte Klassenlisten');
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();
        $All = $Woman = $Man = '0';

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            //ToDo JohK Schuljahr

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ExtendedClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                }

                $studentList = Person::useService()->createExtendedClassList($tblDivision);
                $Count = count($studentList);

                if ($studentList) {
                    $Man = $studentList[$Count - 1]->Man;
                    $Woman = $studentList[$Count - 1]->Woman;
                    $All = $studentList[$Count - 1]->All;
                }
            }
        }

        $Stage->setContent(
            new Well(
                Person::useService()->getClass(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Division]', 'Klasse', array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }} - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Standard/Person/ExtendedClassList')
            )
            .
            ($DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'Number' => 'lfd. Nr.',
                        'Name' => 'Name, Vorname',
                        'Gender' => 'Geschlecht',
                        'Address' => 'Adresse',
                        'Birthday' => 'Geburtsdatum',
                        'Birthplace' => 'Geburtsort',
                        'StudentNumber' => 'Schülernummer',
                        'Guardian1' => 'Sorgeberechtigter 1',
                        'PhoneGuardian1' => 'Tel. Sorgeber. 1',
                        'Guardian2' => 'Sorgeberechtigter 2',
                        'PhoneGuardian2' => 'Tel. Sorgeber. 2',
                    ),
                    null
                ) .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Schüler' . new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Mädchen' . new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Jungen' . new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                        ))
                    )
                )
                : ''
            )
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendBirthdayClassList($DivisionId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Geburtstag');
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();
        $All = $Woman = $Man = '0';

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            //ToDo JohK Schuljahr

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/BirthdayClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                }

                $studentList = Person::useService()->createBirthdayClassList($tblDivision);
                $Count = count($studentList);

                if ($studentList) {
                    $Man = $studentList[$Count - 1]->Man;
                    $Woman = $studentList[$Count - 1]->Woman;
                    $All = $studentList[$Count - 1]->All;
                }
            }
        }

        $Stage->setContent(
            new Well(
                Person::useService()->getClass(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Division]', 'Klasse', array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }} - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Standard/Person/BirthdayClassList')
            )
            .
            ($DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'Number' => 'lfd. Nr.',
                        'Name' => 'Name, Vorname',
                        'Address' => 'Anschrift',
                        'Birthplace' => 'Geburtsort',
                        'Birthday' => 'Geburtsdatum',
                        'Age' => 'Alter',
                    ),
                    null
                ) .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Schüler' . new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Mädchen' . new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Jungen' . new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                        ))
                    )
                )
                : ''
            )
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendMedicalInsuranceClassList($DivisionId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Krankenkasse');
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();
        $All = $Woman = $Man = '0';

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            //ToDo JohK Schuljahr

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/MedicalInsuranceClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                }

                $studentList = Person::useService()->createMedicalInsuranceClassList($tblDivision);
                $Count = count($studentList);

                if ($studentList) {
                    $Man = $studentList[$Count - 1]->Man;
                    $Woman = $studentList[$Count - 1]->Woman;
                    $All = $studentList[$Count - 1]->All;
                }
            }
        }

        $Stage->setContent(
            new Well(
                Person::useService()->getClass(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Division]', 'Klasse', array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }} - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Standard/Person/MedicalInsuranceClassList')
            )
            .
            ($DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'Number' => 'lfd. Nr.',
                        'Name' => 'Name,<br/>Vorname',
                        'Address' => 'Anschrift',
                        'Birthday' => 'Geburtsdatum<br/>Geburtsort',
                        'MedicalInsurance' => 'Krankenkasse',
                        'Guardian' => '1. Sorgeberechtigter<br/>2. Sorgeberechtigter',
                        'PhoneNumber' => 'Telefon<br/>Schüler',
                        'PhoneGuardianNumber' => 'Telefon<br/>Sorgeberechtigte',
                    ),
                    null
                ) .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Schüler' . new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Mädchen' . new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Jungen' . new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                        ))
                    )
                )
                : ''
            )
        );

        return $Stage;
    }

    /**
     * @param null $GroupId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendGroupList($GroupId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'Personengruppenlisten');
        $tblGroupAll = Group::useService()->getGroupAll();
        $tblGroup = new TblGroup('');
        $groupList = array();
        $All = $Woman = $Man = '0';

        if ($GroupId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Group'] = $GroupId;
                $Global->savePost();
            }

            //ToDo JohK Schuljahr

            $tblGroup = Group::useService()->getGroupById($GroupId);
            if ($tblGroup) {
                $groupList = Person::useService()->createGroupList($tblGroup);
                if ($groupList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/GroupList/Download', new Download(),
                            array('GroupId' => $tblGroup->getId()))
                    );
                }

                $Count = count($groupList);

                if ($groupList) {
                    $Man = $groupList[$Count - 1]->Man;
                    $Woman = $groupList[$Count - 1]->Woman;
                    $All = $groupList[$Count - 1]->All;
                }
            }
        }

        $Stage->setContent(
            new Well(
                Person::useService()->getGroup(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Group]', 'Gruppe', array(
                                    '{{ Name }}' => $tblGroupAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Standard/Person/GroupList')
            )
            .
            ($GroupId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Gruppe:', $tblGroup->getName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                .
                new TableData($groupList, null,
                    array(
                        'Number' => 'lfd. Nr.',
                        'Salutation' => 'Anrede',
                        'FirstName' => 'Vorname',
                        'LastName' => 'Nachname',
                        'Birthday' => 'Geburtstag',
                        'Address' => 'Anschrift',
                        'PhoneNumber' => 'Telefon Festnetz',
                        'MobilPhoneNumber' => 'Telefon Mobil',
                        'Mail' => 'E-mail',
                    ),
                    null
                ) .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Personen' . new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Frauen' . new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                            new LayoutColumn(
                                new Panel('Männer' . new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                            ),
                        ))
                    )
                )
                : ''
            )
        );

        return $Stage;
    }
}
