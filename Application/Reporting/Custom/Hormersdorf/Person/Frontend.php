<?php

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Hormersdorf\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten');
        $Route = '/Reporting/Custom/Hormersdorf/Person/ClassList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())) {
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createClassList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Hormersdorf/Person/ClassList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number'       => '#',
                            'DisplayName'  => 'Name',
                            'Birthday'     => 'Geb.-Datum',
                            'Address'      => 'Adresse',
                            'PhoneNumbers' => 'Telefonnummer',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $Stage = new Stage('Auswertung', 'Liste der Mitarbeiter (Geburtstage)');
        $tblPersonList = array();
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF))) {
            $tblPersonList = $tblGroup->getPersonList();
        }
        if(($TableContent = Person::useService()->createStaffList($tblPersonList))) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Hormersdorf/Person/StaffList/Download', new Download()));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Name'     => 'Name',
                            'Birthday' => 'Geburtstag',
                        ),
                        array(
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            ),
                            'pageLength' => -1,
                            'paging' => false,
                            'info' => false,
                            'responsive' => false
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }
}
