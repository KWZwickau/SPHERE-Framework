<?php
namespace SPHERE\Application\Reporting\Custom\Gersdorf\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
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
 * @package SPHERE\Application\Reporting\Custom\Gersdorf\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendClassList(?int $DivisionCourseId = null, $All = null): Stage
    {

        $Stage = new Stage('EVOSG Auswertung', 'Klassenlisten');
        $Route = '/Reporting/Custom/Gersdorf/Person/ClassList';
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
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Gersdorf/Common/ClassList/Download', new Download(),
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
                            'Count'        => '#',
                            'LastName'     => 'Name',
                            'FirstName'    => 'Vorname',
                            'Birthday'     => 'Geb.-datum',
                            'Birthplace'   => 'Geburtsort',
                            'District'     => 'Ortsteil',
                            'StreetName'   => 'Straße',
                            'StreetNumber' => 'Nr.',
                            'Code'         => 'PLZ',
                            'City'         => 'Ort',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
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
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendSignList(?int $DivisionCourseId = null, $All = null): Stage
    {

        $Stage = new Stage('EVOSG Auswertung', 'Unterschriften Liste');
        $Route = '/Reporting/Custom/Gersdorf/Person/SignList';
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
        $TableContent = Person::useService()->createSignList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Gersdorf/Common/SignList/Download', new Download(),
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
                            'Count'        => '#',
                            'LastName'  => 'Name',
                            'FirstName' => 'Vorname',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                , 6))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendElectiveClassList(?int $DivisionCourseId = null, $All = null): Stage
    {

        $Stage = new Stage('Auswertung', 'Wahlfächer in Klassenlisten');
        $Route = '/Reporting/Custom/Gersdorf/Person/ElectiveList';
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
        $TableContent = Person::useService()->createElectiveClassList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Gersdorf/Common/ElectiveClassList/Download', new Download(),
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
                            'Count'            => '#',
                            'Name'             => 'Name',
                            'Education'        => 'Bildungsgang',
                            'ForeignLanguage1' => 'Fremdsprache 1',
                            'ForeignLanguage2' => 'Fremdsprache 2',
                            'ForeignLanguage3' => 'Fremdsprache 3',
                            'Orientation'      => (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName(),
                            'Religion'         => 'Religion',
                            'Elective'         => 'Wahlfächer',
                            'Elective1'         => 'Wahlfach 1',
                            'Elective2'         => 'Wahlfach 2',
                            'Elective3'         => 'Wahlfach 3',
                            'Elective4'         => 'Wahlfach 4',
                            'Elective5'         => 'Wahlfach 5',
                        ),
                        array(
                            "pageLength" => -1,
                            "responsive" => false,
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                            ),
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendClassPhoneList(?int $DivisionCourseId = null, $All = null): Stage
    {

        $Stage = new Stage('EVOSG Auswertung', 'Telefonlisten');
        $Route = '/Reporting/Custom/Gersdorf/Person/ClassPhoneList';
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
        $TableContent = Person::useService()->createClassPhoneList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Gersdorf/Common/ClassPhoneList/Download', new Download(),
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
                            'Number'        => '#',
                            'LastName'     => 'Name',
                            'FirstName'    => 'Vorname',
                            'PhoneNumbers' => 'Telefon-Nr.',
                            'S1PrivatePhoneNumbers'    => 'S1 Privat',
                            'S1Business'   => 'S1 Geschäftlich',
                            'S2Private'    => 'S2 Privat',
                            'S2Business'   => 'S2 Geschäftlich',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
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
    public function frontendTeacherList()
    {

        $Stage = new Stage('Auswertung', 'Mitarbeiter/Lehrerliste');
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $tblPersonList = $tblGroup->getPersonList();
        $TableContent = Person::useService()->createTeacherList();
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Download Mitarbeiter & Lehrerliste', '/Api/Reporting/Custom/Gersdorf/Common/TeacherList/Download', new Download()));
            $Stage->addButton(new Primary('Download Lehrerliste', '/Api/Reporting/Custom/Gersdorf/Common/TeacherList/Download', new Download()
                , array('isTeacher' => true)));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(new Layout(array(
            new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Count'     => '#',
                        'LastName'  => 'Name',
                        'FirstName' => 'Vorname',
                        'Gender'    => 'Geschlecht',
                        'Address'   => 'Anschrift',
                        'Phone'     => 'Telefon',
                        'Birthday'  => 'Geburtsdatum',
                        'Group'     => 'Personengruppe',
                    ),
                    array(
                        "columnDefs" => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                        ),
                    )
                ))))
            , PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
        )));

        return $Stage;
    }
}