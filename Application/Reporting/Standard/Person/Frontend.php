<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
     * @return Stage
     */
    public function frontendClassList()
    {

        $Stage = new Stage('Auswertung', 'Klassenliste');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/ClassList/Download', new Download())
        );

        $studentList = Person::useService()->createClassList();
        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'FirstName'    => 'Vorname',
                    'LastName'     => 'Name',
                    'Denomination' => 'Konfession',
                    'Birthday'     => 'Geburtsdatum',
                    'Birthplace'   => 'Geburtsort',
                    'Address'      => 'Adresse',
                ),
                false
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendExtendedClassList()
    {

        $Stage = new Stage('Auswertung', 'erweiterte Klassenliste');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/ExtendedClassList/Download', new Download())
        );

        $studentList = Person::useService()->createExtendedClassList();
        $Count = count($studentList);

        $Man = $studentList[$Count - 1]->Man;
        $Woman = $studentList[$Count - 1]->Woman;
        $All = $studentList[$Count - 1]->All;

        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Number'        => 'lfd. Nr.',
                    'Name'          => 'Name, Vorname',
                    'Gender'        => 'Geschlecht',
                    'Address'       => 'Adresse',
                    'Birthday'      => 'Geburtsdatum',
                    'Birthplace'    => 'Geburtsort',
                    'StudentNumber' => 'Schülernummer',
                    'Mother'        => 'Sorgeberechtigte',
                    'PhoneMother'   => 'Tel. Sorgeber.',
                    'Father'        => 'Sorgeberechtigter',
                    'PhoneFather'   => 'Tel. Sorgeber.',
                ),
                false
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schüler'.new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Mädchen'.new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Jungen'.new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendBirthdayClassList()
    {

        $Stage = new Stage('Auswertung', 'Klassenliste Geburtstage');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/BirthdayClassList/Download', new Download())
        );

        $studentList = Person::useService()->createBirthdayClassList();
        $Count = count($studentList);

        $Man = $studentList[$Count - 1]->Man;
        $Woman = $studentList[$Count - 1]->Woman;
        $All = $studentList[$Count - 1]->All;

        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Number'     => 'lfd. Nr.',
                    'Name'       => 'Name, Vorname',
                    'Address'    => 'Anschrift',
                    'Birthplace' => 'Geburtsort',
                    'Birthday'   => 'Geburtsdatum',
                    'Age'        => 'Alter',
                ),
                false
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schüler'.new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Mädchen'.new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Jungen'.new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendMedicalInsuranceClassList()
    {

        $Stage = new Stage('Auswertung', 'Klassenliste Krankenkasse');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/MedicalInsuranceClassList/Download', new Download())
        );

        $studentList = Person::useService()->createMedicalInsuranceClassList();
        $Count = count($studentList);

        $Man = $studentList[$Count - 1]->Man;
        $Woman = $studentList[$Count - 1]->Woman;
        $All = $studentList[$Count - 1]->All;

        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Number'              => 'lfd. Nr.',
                    'Name'                => 'Name,<br/>Vorname',
                    'Address'             => 'Anschrift',
                    'Birthday'            => 'Geburtsdatum<br/>Geburtsort',
                    'MedicalInsurance'    => 'Krankenkasse',
                    'Guardian'            => '1. Sorgeberechtigter<br/>2. Sorgeberechtigter',
                    'PhoneNumber'         => 'Telefon<br/>Schüler',
                    'PhoneGuardianNumber' => 'Telefon<br/>Sorgeberechtigte',
                ),
                false
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schüler'.new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Mädchen'.new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Jungen'.new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }

    public function frontendEmployeeList()
    {

        $Stage = new Stage('Mitarbeiter');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/EmployeeList/Download', new Download())
        );

        $employeeList = Person::useService()->createEmployeeList();
        $Count = count($employeeList);

        $Man = $employeeList[$Count - 1]->Man;
        $Woman = $employeeList[$Count - 1]->Woman;
        $All = $employeeList[$Count - 1]->All;

        $Stage->setContent(
            new TableData($employeeList, null,
                array(
                    'Number'           => 'lfd. Nr.',
                    'Salutation'       => 'Anrede',
                    'FirstName'        => 'Vorname',
                    'LastName'         => 'Nachname',
                    'Birthday'         => 'Geburtstag',
                    'Address'          => 'Anschrift',
                    'PhoneNumber'      => 'Telefon Festnetz',
                    'MobilPhoneNumber' => 'Telefon Mobil',
                    'Mail'             => 'E-mail',
                ),
                false
            )

            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Gesamt'.new PullRight($All), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Frauen'.new PullRight($Woman), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                        new LayoutColumn(
                            new Panel('Männer'.new PullRight($Man), '', Panel::PANEL_TYPE_INFO), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }
}
