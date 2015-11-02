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
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClassList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Auswertung');
        $Stage->setDescription('Klassenliste');

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

    public function frontendFuxClassList()
    {

        $Stage = new Stage('Auswertung', 'erweiterte Klassenliste');

//        $Stage->addButton(
//            new Primary('Herunterladen',
//                '/Api/Reporting/Standard/Person/ClassList/Download', new Download())
//        );

        $studentList = Person::useService()->createClassListFux();
        $Count = count($studentList);

        $Man = $studentList[$Count - 1]->Man;
        $Woman = $studentList[$Count - 1]->Woman;
        $All = $studentList[$Count - 1]->All;

        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Name'          => 'Name, Vorname',
                    'Gender'        => 'Geschlecht',
                    'Birthday'      => 'Geburtsdatum',
                    'Birthplace'    => 'Geburtsort',
                    'StudentNumber' => 'Schülernummer',
                    'Father'        => 'Vater',
                    'Mother'        => 'Mutter',

                ),
                false
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Alle'.new PullRight($All), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                        new LayoutColumn(
                            new Panel('Mädchen'.new PullRight($Woman), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                        new LayoutColumn(
                            new Panel('Jungen'.new PullRight($Man), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }

    public function frontendBirthdayClassList()
    {

        $Stage = new Stage('Auswertung', 'Klassenliste Geburtstage');

//        $Stage->addButton(
//            new Primary('Herunterladen',
//                '/Api/Reporting/Standard/Person/ClassList/Download', new Download())
//        );

        $studentList = Person::useService()->createClassListFux();
        $Count = count($studentList);

        $Man = $studentList[$Count - 1]->Man;
        $Woman = $studentList[$Count - 1]->Woman;
        $All = $studentList[$Count - 1]->All;


        $Stage->setContent(
            new TableData($studentList, null,
                array(
                    'Name'       => 'Name, Vorname',
                    'Address'    => 'Anschrift',
                    'Birthplace' => 'Geburtsort',
                    'Birthday'   => 'Geburtsdatum',
                ),
                false
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Alle'.new PullRight($All), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                        new LayoutColumn(
                            new Panel('Mädchen'.new PullRight($Woman), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                        new LayoutColumn(
                            new Panel('Jungen'.new PullRight($Man), '', Panel::PANEL_TYPE_SUCCESS), 2
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }
}