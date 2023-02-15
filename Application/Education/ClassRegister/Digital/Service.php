<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreement;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContentLink;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param Stage $Stage
     * @param $view
     * @param $Route
     */
    public function setHeaderButtonList(Stage $Stage, $view, $Route)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization($Route . '/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization($Route . '/Headmaster');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        $Route . '/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        $Route . '/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Alle Klassenbücher')),
                        $Route . '/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Alle Klassenbücher',
                        $Route . '/Headmaster'));
                }
            }
        }
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $IsGroup
     * @param $YearId
     * @param $HasAllYears
     * @param $HasCurrentYears
     * @param $yearFilterList
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $IsGroup, $YearId, $HasAllYears, $HasCurrentYears,
        &$yearFilterList): array
    {
        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && !$IsGroup && $tblYearList && !$HasCurrentYears) {
            $tblYear = end($tblYearList);
        }
        $isCurrentYears = $HasCurrentYears && !$IsAllYears && !$IsGroup && !$YearId;

        $buttonList = array();
        if ($tblYearList) {
            if ($HasCurrentYears) {
                if ($isCurrentYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Aktuelles Schuljahr')),
                        $Route, new Edit()));
                } else {
                    $buttonList[] = (new Standard('Aktuelles Schuljahr', $Route, null));
                }
            }

            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                    $yearFilterList[$tblYearItem->getId()] = $tblYearItem;
                } else {
                    if ($isCurrentYears || $IsGroup) {
                        $yearFilterList [$tblYearItem->getId()] = $tblYearItem;
                    }

                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            if ($HasAllYears) {
                if ($IsAllYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                        $Route, new Edit(), array('IsAllYears' => true)));
                }  else {
                    $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                        array('IsAllYears' => true)));
                }
            }

            if ($IsGroup) {
                $buttonList[] = (new Standard(new Info(new Bold('Gruppen')),
                    $Route, new Edit(), array('IsGroup' => true)));
            }  else {
                $buttonList[] = (new Standard('Gruppen', $Route, null,
                    array('IsGroup' => true)));
            }

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblYear|null $tblYear
     * @param TblDivisionSubject|null $tblDivisionSubject
     *
     * @return LayoutRow
     */
    public function getHeadLayoutRow(TblDivision $tblDivision = null, TblGroup $tblGroup = null, TblYear &$tblYear = null,
        TblDivisionSubject $tblDivisionSubject = null): LayoutRow
    {
        if ($tblDivisionSubject
            && ($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
            && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
        ) {
            $tblYear = $tblDivision->getServiceTblYear();
            $title = ($tblSubjectGroup->isAdvancedCourse() ? 'Leistungskurs ' : 'Grundkurs ') . $tblSubjectGroup->getName();
            $content[] = ($tblGroup ? 'Gruppe: ' . $tblGroup->getName() : 'Klasse: ' . $tblDivision->getDisplayName())
                . ' Fach: ' . $tblSubject->getDisplayName();
            $content[] = 'Fachlehrer: ' . Division::useService()->getSubjectTeacherNameList($tblDivision, $tblSubject, $tblSubjectGroup);
        } elseif ($tblGroup) {
            $title = 'Stammgruppe';
            $content[] = $tblGroup->getName();
            if (($tudors = $tblGroup->getTudorsString())) {
                $content[] = $tudors;
            }
            $tblYear = $tblGroup->getCurrentYear();
        } elseif ($tblDivision) {
            $title = 'Klasse';
            $content[] = $tblDivision->getDisplayName();
            if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))) {
                $TeacherArray = array();
                foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                    if ($tblPerson = $tblDivisionTeacher->getServiceTblPerson()) {
                        $TeacherArray[] = $tblPerson->getFullName()
                            . (($description = $tblDivisionTeacher->getDescription())
                                ? ' ' . new Muted($description) : '');
                    }
                }
                if (!empty($TeacherArray)) {
                    $content[] = 'Klassenlehrer: ' . implode(', ', $TeacherArray);
                }
            }
            // Elternvertreter
            if (($tblCustodyList = Division::useService()->getCustodyAllByDivision($tblDivision))) {
                $custodyList = array();
                $count = 0;
                foreach ($tblCustodyList as $tblPerson) {
                    $Description = Division::useService()->getDivisionCustodyByDivisionAndPerson($tblDivision, $tblPerson)->getDescription();
                    $custodyList[$count++] = $tblPerson->getFullName() . ($Description ? ' (' . $Description . ')' : '');
                }
                $content[] = 'Elternvertreter: ' . implode(', ', $custodyList);
            }
            // Klassensprecher
            if (($tblDivisionRepresentativeList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))) {
                $representativeList = array();
                $count = 0;
                foreach($tblDivisionRepresentativeList as $tblDivisionRepresentative){
                    $tblPersonRepresentative = $tblDivisionRepresentative->getServiceTblPerson();
                    $Description = $tblDivisionRepresentative->getDescription();
                    $representativeList[$count++] = $tblPersonRepresentative->getFirstSecondName() . ' ' . $tblPersonRepresentative->getLastName()
                        . ($Description ? ' (' . $Description . ')' : '');
                }
                $content[] = 'Klassensprecher: ' . implode(', ', $representativeList);
            }
            $tblYear = $tblDivision->getServiceTblYear();
        } else {
            $title = '';
            $content = '';
            $tblYear = false;
        }

        return new LayoutRow(array(
            new LayoutColumn(new Panel($title, $content, Panel::PANEL_TYPE_INFO), 6),
            new LayoutColumn(new Panel('Schuljahr', $tblYear ? $tblYear->getDisplayName() : '', Panel::PANEL_TYPE_INFO), 6)
        ));
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param string $Route
     * @param string $BasicRoute
     *
     * @return LayoutRow
     */
    public function getHeadButtonListLayoutRow(TblDivision $tblDivision = null, TblGroup $tblGroup = null,
        string $Route = '/Education/ClassRegister/Digital/LessonContent', string $BasicRoute = ''): LayoutRow
    {
        $DivisionId = $tblDivision ? $tblDivision->getId() : null;
        $GroupId = $tblGroup ? $tblGroup->getId() : null;

        $isCourseSystem = ($tblDivision && Division::useService()->getIsDivisionCourseSystem($tblDivision))
            || ($tblGroup && $tblGroup->getIsGroupCourseSystem());

        if ($isCourseSystem) {
            $buttonList[] = $this->getButton('Kursheft auswählen', '/Education/ClassRegister/Digital/SelectCourse', new Book(),
                $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/SelectCourse');
        } else {
            $buttonList[] = $this->getButton('Klassentagebuch', '/Education/ClassRegister/Digital/LessonContent', new Book(),
                $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonContent');
        }

        // Klassentagebuch Kontrolle: nur für Klassenlehrer, Tudor oder Schulleitung
        if ((($tblPerson = Account::useService()->getPersonByLogin())
            && (($tblDivision && Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson))
                || ($tblGroup && ($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson))
            ))
            || Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')
        ) {
            // Klassentagebuch Kontrolle: nicht bei Kurssystemen
            if (!$isCourseSystem) {
                $buttonList[] = $this->getButton('Klassentagebuch Kontrolle', '/Education/ClassRegister/Digital/LessonWeek', new Ok(),
                    $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonWeek');
            }
        }

        $buttonList[] = $this->getButton('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');

        // Fehlzeiten (Kalenderansicht) nur bei Klassen anzeigen
        if ($tblDivision) {
            $buttonList[] = $this->getButton('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth',
                new Calendar(), $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');
        }

        // Belehrungen: nicht bei Kurssystemen → Belehrungen direkt im Kursheft
        if (!$isCourseSystem) {
            $buttonList[] = $this->getButton('Belehrungen', '/Education/ClassRegister/Digital/Instruction',
                new CommodityItem(), $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
        }
        $buttonList[] = $this->getButton('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship',
            new Listing(), $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButton('Ferien', '/Education/ClassRegister/Digital/Holiday',
            new Holiday(), $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        $buttonList[] = $this->getButton('Download', '/Education/ClassRegister/Digital/Download',
            new Download(), $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return new LayoutRow(new LayoutColumn($buttonList));
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionId
     * @param $GroupId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButton(string $name, string $route, $icon, $DivisionId, $GroupId, $BasicRoute, bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionId' => $DivisionId,
                'GroupId' =>  $GroupId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param TblDivisionSubject|null $tblDivisionSubject
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $Route
     * @param string $BasicRoute
     *
     * @return LayoutRow
     */
    public function getHeadButtonListLayoutRowForDivisionSubject(TblDivisionSubject $tblDivisionSubject = null, $DivisionId = null, $GroupId = null,
        string $Route = '/Education/ClassRegister/Digital/CourseContent', string $BasicRoute = ''): LayoutRow
    {
        $DivisionSubjectId = $tblDivisionSubject->getId();
        $buttonList[] = $this->getButtonForDivisionSubject('Kursheft', '/Education/ClassRegister/Digital/CourseContent', new Book(),
            $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseContent');

        // Kursheft Kontrolle: nur für Schulleitung
        if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')) {
            $buttonList[] = $this->getButtonForDivisionSubject('Kursheft Kontrolle', '/Education/ClassRegister/Digital/CourseControl', new Ok(),
                $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseControl');
        }

        $buttonList[] = $this->getButtonForDivisionSubject('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');

//        // nicht bei Gruppen
//        // Fehlzeiten (Kalenderansicht) funktioniert aktuell nur bei Klassen und nicht für Gruppe oder SekII-Kurse
//        if (!$GroupId) {
//            $buttonList[] = $this->getButtonForDivisionSubject('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth',
//                new Calendar(), $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');
//        }

        $buttonList[] = $this->getButtonForDivisionSubject('Belehrungen', '/Education/ClassRegister/Digital/Instruction',
            new CommodityItem(), $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
        $buttonList[] = $this->getButtonForDivisionSubject('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship',
            new Listing(), $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButtonForDivisionSubject('Ferien', '/Education/ClassRegister/Digital/Holiday',
            new Holiday(), $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        $buttonList[] = $this->getButtonForDivisionSubject('Download', '/Education/ClassRegister/Digital/Download',
            new Download(), $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return new LayoutRow(new LayoutColumn($buttonList));
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionSubjectId
     * @param $DivisionId
     * @param $GroupId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButtonForDivisionSubject(string $name, string $route, $icon, $DivisionSubjectId, $DivisionId, $GroupId, $BasicRoute,
        bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionSubjectId' => $DivisionSubjectId,
                'DivisionId' => $DivisionId,
                'GroupId' =>  $GroupId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsToolTip
     *
     * @return string
     */
    public function getTeacherString(TblPerson $tblPerson, bool $IsToolTip = true): string
    {
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
            && ($acronym = $tblTeacher->getAcronym())
        ) {
            $teacher = $acronym;
        } else {
            $teacher = $tblPerson->getLastName();
            if (strlen($teacher) > 5) {
                $teacher = substr($teacher, 0, 5) . '.';
            }
        }

        return $IsToolTip ? new ToolTip($teacher, $tblPerson->getFullName()) : $teacher;
    }

    /**
     * @param $Data
     * @param int $lesson
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblLessonContent
     */
    public function createLessonContent($Data, int $lesson, TblDivision $tblDivision = null, TblGroup $tblGroup = null): TblLessonContent
    {
        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
        } else {
            $tblYear = false;
        }

        $tblPerson = Account::useService()->getPersonByLogin();
//        $tblPerson = Person::useService()->getPersonById($Data['serviceTblPerson'])

        return (new Data($this->getBinding()))->createLessonContent(
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Room'],
            $tblDivision ?: null,
            $tblGroup ?: null,
            $tblYear ?: null,
            $tblPerson ?: null,
            ($tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject'])) ? $tblSubject : null,
            ($tblSubstituteSubject = Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])) ? $tblSubstituteSubject : null,
            isset($Data['IsCanceled'])
        );
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param $Data
     *
     * @return bool
     */
    public function updateLessonContent(TblLessonContent $tblLessonContent, $Data): bool
    {
        $tblPerson = Account::useService()->getPersonByLogin();
//        $tblPerson = Person::useService()->getPersonById($Data['serviceTblPerson'])

        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        return (new Data($this->getBinding()))->updateLessonContent(
            $tblLessonContent,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Room'],
            $tblPerson ?: null,
            ($tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject'])) ? $tblSubject : null,
            ($tblSubstituteSubject = Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])) ? $tblSubstituteSubject : null,
            isset($Data['IsCanceled'])
        );
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function destroyLessonContent(TblLessonContent $tblLessonContent): bool
    {
        if (($tblLessonContentLinkList = $tblLessonContent->getLinkedLessonContentAll())) {

            $tblLessonContentLinkList[] = $tblLessonContent;
            // Verknüpfungen löschen
            $this->destroyLessonContentLinkList($tblLessonContentLinkList);

            foreach ($tblLessonContentLinkList as $tblLessonContentItem) {
                (new Data($this->getBinding()))->destroyLessonContent($tblLessonContentItem);
            }
        } else {
            (new Data($this->getBinding()))->destroyLessonContent($tblLessonContent);
        }

        return true;
    }

    /**
     * @param $Id
     *
     * @return false|TblLessonContent
     */
    public function getLessonContentById($Id)
    {
        return (new Data($this->getBinding()))->getLessonContentById($Id);
    }

    /**
     * @param DateTime $date
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDate($date, $tblDivision, $tblGroup);
    }

    /**
     * @param DateTime $date
     * @param int|null $lesson
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDateAndLesson(DateTime $date, ?int $lesson, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDateAndLesson($date, $lesson, $tblDivision, $tblGroup);
    }

    /**
     * @param $Data
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblLessonContent|null $tblLessonContent
     *
     * @return bool|Form
     */
    public function checkFormLessonContent(
        $Data,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null,
        TblLessonContent $tblLessonContent = null
    ) {
        $error = false;

        $form = Digital::useFrontend()->formLessonContent(
            $tblDivision ?: null, $tblGroup ?: null, $tblLessonContent ? $tblLessonContent->getId() : null
        );
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if ($tblDivision) {
                $tblYear = $tblDivision->getServiceTblYear();
            } elseif ($tblGroup) {
                $tblYear = $tblGroup->getCurrentYear();
            } else {
                $tblYear = false;
            }
            if ($tblYear) {
                list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDateSchoolYear && $endDateSchoolYear) {
                    $date = new DateTime($Data['Date']);
                    if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                        $form->setError('Data[Date]', 'Das ausgewählte Datum: ' . $Data['Date'] . ' befindet sich außerhalb des Schuljahres.');
                        $error = true;
                    }
                } else {
                    $form->setError('Data[Date]', 'Das Schuljahr besitzt keinen Zeitraum');
                    $error = true;
                }
            } else {
                $form->setError('Data[Date]', 'Kein Schuljahr gefunden');
                $error = true;
            }
        }
        if (isset($Data['Lesson']) && $Data['Lesson'] == 0) {
            $form->setError('Data[Lesson]', 'Bitte geben Sie eine Unterrichtseinheit an');
            $error = true;
        }

        // nicht mehr verwenden da es als zusätzliches Fach benutzt werden soll
//        // bei einem gesetzten Vertretungsfach muss auch ein Fach ausgewählt werden
//        if (Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])
//            && empty($Data['serviceTblSubject'])
//        ) {
//            $form->setError('Data[serviceTblSubject]', 'Bitte geben Sie ein Fach an');
//            $error = true;
//        }

        return $error ? $form : false;
    }

    /**
     * @param $Id
     *
     * @return false|TblCourseContent
     */
    public function getCourseContentById($Id)
    {
        return (new Data($this->getBinding()))->getCourseContentById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivision $tblDivision, TblSubject $tblSubject,TblSubjectGroup $tblSubjectGroup)
    {
        return (new Data($this->getBinding()))->getCourseContentListBy($tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param $Data
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblCourseContent|null $tblCourseContent
     *
     * @return false|Form
     */
    public function checkFormCourseContent(
        $Data,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup,
        TblCourseContent $tblCourseContent = null
    ) {
        $error = false;

        $form = Digital::useFrontend()->formCourseContent(
            $tblDivision, $tblSubject, $tblSubjectGroup, $tblCourseContent ? $tblCourseContent->getId() : null
        );
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDateSchoolYear && $endDateSchoolYear) {
                    $date = new DateTime($Data['Date']);
                    if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                        $form->setError('Data[Date]', 'Das ausgewählte Datum: ' . $Data['Date'] . ' befindet sich außerhalb des Schuljahres.');
                        $error = true;
                    }
                } else {
                    $form->setError('Data[Date]', 'Das Schuljahr besitzt keinen Zeitraum');
                    $error = true;
                }
            } else {
                $form->setError('Data[Date]', 'Kein Schuljahr gefunden');
                $error = true;
            }
        }
        if (isset($Data['Lesson']) && $Data['Lesson'] == 0) {
            $form->setError('Data[Lesson]', 'Bitte geben Sie eine Unterrichtseinheit an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function createCourseContent($Data, TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup): bool
    {
        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        (new Data($this->getBinding()))->createCourseContent(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Remark'],
            $Data['Room'],
            isset($Data['IsDoubleLesson']),
            ($tblPerson = Account::useService()->getPersonByLogin()) ? $tblPerson : null
        );

        return  true;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     * @param $Data
     *
     * @return bool
     */
    public function updateCourseContent(TblCourseContent $tblCourseContent, $Data): bool
    {
        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        return (new Data($this->getBinding()))->updateCourseContent(
            $tblCourseContent,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Remark'],
            $Data['Room'],
            isset($Data['IsDoubleLesson']),
            ($tblPerson = Account::useService()->getPersonByLogin()) ? $tblPerson : null
        );
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     */
    public function updateBulkCourseContentHeadmaster(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup)
    {
        $updateList = array();
        if (($tblCourseContentList = $this->getCourseContentListBy($tblDivision, $tblSubject, $tblSubjectGroup))) {
            foreach ($tblCourseContentList as $tblCourseContent) {
                if (!$tblCourseContent->getDateHeadmaster() || !$tblCourseContent->getServiceTblPersonHeadmaster()) {
                    $updateList[] = $tblCourseContent;
                }
            }
        }

        if ($updateList && ($tblPerson = Account::useService()->getPersonByLogin())) {
            (new Data($this->getBinding()))->updateBulkCourseContent($updateList, (new DateTime('today'))->format('d.m.Y'), $tblPerson);
        }
    }

    /**
     * @param TblCourseContent $tblCourseContent
     *
     * @return bool
     */
    public function destroyCourseContent(TblCourseContent $tblCourseContent): bool
    {
        return (new Data($this->getBinding()))->destroyCourseContent($tblCourseContent);
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param string $BasicRoute
     * @param string $ReturnRoute
     * @param TblDivisionSubject|null $tblDivisionSubject
     *
     * @return string
     */
    public function getStudentTable(?TblDivision $tblDivision, ?TblGroup $tblGroup, string $BasicRoute, string $ReturnRoute,
        TblDivisionSubject $tblDivisionSubject = null): string
    {
        $tblPersonList = false;
        $hasColumnCourse = false;
        if ($tblDivisionSubject) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        } elseif ($tblDivision) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            if (($tblLevel = $tblDivision->getTblLevel())
                && ($tblSchoolType = $tblLevel->getServiceTblType())
            ) {
                $hasColumnCourse = $tblSchoolType->getShortName() == 'OS';
            }
        } elseif ($tblGroup) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
        }

        if ($tblPersonList) {
            $studentTable = array();
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $birthday = '';
                $Gender = '';
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    if ($tblCommon->getTblCommonBirthDates()) {
                        $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                        $tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender();
                        if ($tblGender) {
                            $Gender = $tblGender->getShortName();
                        }
                    }
                }
                $PersonPicture = '';
                if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                    $PersonPicture = new Center((new Link($tblPersonPicture->getPicture('50px', '10px'), $tblPerson->getId()))
                        ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
                }


                $displayDivision = '';
                $course = '';
                $medicalRecord = '';
                $agreement = '';
                $integration = '';
                $tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
                if ($tblGroup && $tblMainDivision) {
                    $displayDivision = $tblMainDivision->getDisplayName();
                    if ($hasColumnCourse) {
                        if (($tblLevel = $tblMainDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                        ) {
                            $hasColumnCourse = $tblSchoolType->getShortName() == 'OS';
                        }
                    }
                } else {
                    $tblMainDivision = $tblDivision;
                }
                if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                    if (($tblCourse = $tblStudent->getCourse())) {
                        $course = $tblCourse->getName();
                    }

                    if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                        && ($tblMedicalRecord->getDisease()
                            || $tblMedicalRecord->getMedication()
                            || $tblMedicalRecord->getAttendingDoctor())
                    ) {
                        $medicalRecord = (new Standard('', MedicalRecordReadOnly::getEndpoint(), new Hospital(), array(), 'Krankenakte'))
                            ->ajaxPipelineOnClick(MedicalRecordReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    }

                    if (Student::useService()->getStudentAgreementAllByStudent($tblStudent)) {
                        $agreement = (new Standard('', ApiAgreement::getEndpoint(), new Check(), array(), 'Einverständniserklärung'))
                            ->ajaxPipelineOnClick(ApiAgreement::pipelineOpenOverViewModal($tblPerson->getId()));
                    }
                }

                if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new Tag(), array(), 'Integration'))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                }

                // Kontakt-Daten
                $contacts = array();
                $contacts = Person::useService()->getContactDataFromPerson($tblPerson, $contacts);

                // Fehlzeiten
                $unExcusedLessons = 0;
                $excusedLessons = 0;
                $unExcusedDays = 0;
                $excusedDays = 0;
                if ($tblMainDivision) {
                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblMainDivision, null,
                        $excusedLessons);
                    $unExcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblMainDivision, null,
                        $unExcusedLessons);
                }
                $absenceDays = ($excusedDays + $unExcusedDays) . ' (' . new Success($excusedDays) . ', '
                    . new Danger($unExcusedDays) . ')';
                $absenceLessons = ($excusedLessons + $unExcusedLessons) . ' (' . new Success($excusedLessons) . ', '
                    . new Danger($unExcusedLessons) . ')';

                $studentTable[] = array(
                    'Number'        => ++$count,
                    'Name'          => $tblPerson->getLastFirstNameWithCallNameUnderline(),
                    'Picture'       => $PersonPicture,
                    'Division'      => $displayDivision,
//                    'Integration'   => $integration,
//                    'MedicalRecord' => $medicalRecord,
//                    'Agreement'     => $agreement,
                    'Info'          => $integration . $medicalRecord . $agreement,
                    'Gender'        => $Gender,
                    'Address'       => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiTwoRowString() : '',
                    'Phone'         => $contacts['PhoneFixed'] ?? '',
                    'Mail'          => $contacts['MailFrontendListFixed'] ?? '',
                    'Birthday'      => $birthday,
                    'Course'        => $course,
                    'AbsenceDays'   => $absenceDays,
                    'AbsenceLessons'=> $absenceLessons,
                    'Option'        => ($tblMainDivision
                            ? (new Standard(
                                '', '/Education/ClassRegister/Digital/AbsenceStudent', new Time(),
                                array(
                                    'DivisionId' => $tblMainDivision->getId(),
                                    'PersonId'   => $tblPerson->getId(),
                                    'BasicRoute' => $BasicRoute,
                                    'ReturnRoute'=> $ReturnRoute,
                                    'GroupId'    => $tblGroup ? $tblGroup->getId() : null,
                                    'DivisionSubjectId' => $tblDivisionSubject ? $tblDivisionSubject->getId() : null
                                ),
                                'Fehlzeiten des Schülers verwalten'
                            ))
                            . (new Standard(
                                '', '/Education/ClassRegister/Digital/Integration', new Commodity(),
                                array(
                                    'DivisionId' => $tblMainDivision->getId(),
                                    'PersonId'   => $tblPerson->getId(),
                                    'BasicRoute' => $BasicRoute,
                                    'ReturnRoute'=> $ReturnRoute,
                                    'GroupId'    => $tblGroup ? $tblGroup->getId() : null,
                                    'DivisionSubjectId' => $tblDivisionSubject ? $tblDivisionSubject->getId() : null
                                ),
                                'Integration des Schülers verwalten'
                            )) : '')
                );
            }

            $columns['Number'] = '#';
            $columns['Name'] = 'Name';
            $columns['Picture'] = 'Foto';
            if ($tblGroup) {
                $columns['Division'] = 'Klasse';
            }
            if ($hasColumnCourse) {
                $columns['Course'] = 'Bildungs&shy;gang';
            }
//            $columns['Integration'  ] = 'Inte&shy;gration';
//            $columns['MedicalRecord'] = 'Kranken&shy;akte';
//            $columns['Agreement'] = 'Einver&shy;ständnis';
            $columns['Info'] = 'Info';
            $columns['Gender'] = 'Ge&shy;schlecht';
            $columns['Birthday'] = 'Geburts&shy;datum';
            $columns['Address'] = 'Adresse';
            $columns['Phone'] = new ToolTip('Telefon '. new InfoIcon(),
                'p=Privat; g=Geschäftlich; n=Notfall; f=Fax; Bev.=Bevollmächtigt; Vorm.=Vormund; NK=Notfallkontakt');
            $columns['Mail'] = 'E-Mail';
            $columns['AbsenceDays'] = 'Zeugnis&shy;relevante Fehlzeiten Tage<br>(E, U)';
            $columns['AbsenceLessons'] = 'Zeugnis&shy;relevante Fehlzeiten UE<br>(E, U)';
            $columns['Option'] = '';

            return
                ApiSupportReadOnly::receiverOverViewModal()
                . MedicalRecordReadOnly::receiverOverViewModal()
                . ApiAgreement::receiverOverViewModal()
                . ApiPersonPicture::receiverModal()
                . ($tblDivision && ($inActivePanel = Person::useFrontend()
                    ->getInActiveStudentPanel($tblDivision))
                    ? $inActivePanel : '')
                . (new TableData($studentTable, null, $columns,
                    array(
                        'paging' => false,
                        'columnDefs' => array(
                            array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                            array('width' => '60px', 'targets' => $hasColumnCourse ? 4 : 3),
                            array('width' => '60px', 'targets' => -2),
                            array('width' => '60px', 'targets' => -3),
                            array('width' => '180px', 'targets' => -6),
                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ));
        }

        return '';
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    public function getSubjectsAndLectureshipByDivision(TblDivision $tblDivision): string
    {
        $dataList = array();
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                    $listing = array();
                    if (($list = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                        $tblDivision, $tblSubject
                    ))) {
                        foreach ($list as $item) {
                            if (($tblSubjectGroup = $item->getTblSubjectGroup())) {
                                $listing[] = new PullClear($tblSubjectGroup->getName()
                                    . new PullRight(Division::useService()->getSubjectTeacherNameList($tblDivision, $tblSubject, $tblSubjectGroup)));
                            }
                        }
                        sort($listing);
                    }

                    $dataList[] = array(
                        'Subject' => $tblDivisionSubject->getHasGrading()
                            ? $tblSubject->getDisplayName()
                            : new Muted($tblSubject->getDisplayName()  . ' (Keine Benotung)'),
                        'Teacher' => Division::useService()->getSubjectTeacherNameList($tblDivision, $tblSubject),
                        'SubjectGroup' => $list ? new \SPHERE\Common\Frontend\Layout\Repository\Listing($listing) : ''
                    );
                }
            }
        }

        $columns = array(
            'Subject' => 'Unterrichtsfach',
            'Teacher' => 'Lehrer',
            'SubjectGroup' => 'Fach-Gruppe' . new PullRight('Fach-Gruppen-Lehrer')
        );

        return (new TableData($dataList, new Title('Klasse ' . $tblDivision->getDisplayName()), $columns, null))
            ->setHash('Table_Division_' . $tblDivision->getId());
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function getSubjectsAndLectureshipByDivisionForDownload(TblDivision $tblDivision): array
    {
        $dataList = array();
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                    $teacherList = array();
                    if (($list = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                        $tblDivision, $tblSubject
                    ))) {
                        foreach ($list as $item) {
                            if (($tblSubjectGroup = $item->getTblSubjectGroup())
                                && ($subList = Division::useService()->getSubjectTeacherList($tblDivision, $tblSubject, $tblSubjectGroup))
                            ) {
                                foreach ($subList as $personId => $name) {
                                    if (!isset($teacherList[$personId])) {
                                        $teacherList[$personId] = $name;
                                    }
                                }
                            }
                        }
                    } else {
                        $teacherList = Division::useService()->getSubjectTeacherList($tblDivision, $tblSubject);
                    }

                    $dataList[$tblSubject->getAcronym()] = array(
                        'Subject' => $tblSubject->getDisplayName(),
//                        'Teacher' => empty($teacherList) ? '&nbsp;' : implode(', ', $teacherList),
                        'TeacherArray' => $teacherList
                    );
                }
            }

            ksort($dataList);
        }

        return $dataList;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByBetween(DateTime $fromDate, DateTime $toDate, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByBetween($fromDate, $toDate, $tblDivision, $tblGroup);
    }

    /**
     * @param DateTime $toDate
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    public function getLessonContentCanceledSubjectList(DateTime $toDate, TblDivision $tblDivision = null, TblGroup $tblGroup = null): array
    {
        $subjectCancelList = array();
        $subjectAdditionalList = array();
        if (($tblLessonContentList = (new Data($this->getBinding()))->getLessonContentCanceledAllByToDate($toDate, $tblDivision, $tblGroup))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                if (($tblSubject = $tblLessonContent->getServiceTblSubject())) {
                    if (isset($subjectCancelList[$tblSubject->getAcronym()])) {
                        $subjectCancelList[$tblSubject->getAcronym()]++;
                    } else {
                        $subjectCancelList[$tblSubject->getAcronym()] = 1;
                    }
                }
                if (($tblSubstituteSubjectSubject = $tblLessonContent->getServiceTblSubstituteSubject())) {
                    if (isset($subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()])) {
                        $subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()]++;
                    } else {
                        $subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()] = 1;
                    }
                }
            }
        }

        return array($subjectCancelList, $subjectAdditionalList);
    }

    /**
     * @param DateTime $dateTime
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param bool $hasEdit
     *
     * @return Panel|string
     */
    public function getCanceledSubjectOverview(DateTime $dateTime, ?TblDivision $tblDivision, ?TblGroup $tblGroup, bool $hasEdit = true)
    {
        list($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList) = $this->getCanceledSubjectList($dateTime, $tblDivision, $tblGroup);

        list($subjectTotalCanceledList, $subjectTotalAdditionalList) = $this->getLessonContentCanceledSubjectList($toDate, $tblDivision, $tblGroup);

        if ($subjectList) {
            $columns = array();
            $dataList = array();
            ksort($subjectList);
            $columns['Name'] = 'Fach';
            $dataList['Canceled']['Name'] = new ToolTip('Ausgefallene Stunden ' . new InfoIcon(), "Ausgefallene Stunden der KW{$dateTime->format('W')}");
            $dataList['Additional']['Name'] = new ToolTip('Zusätzlich erteilte Stunden ' . new InfoIcon(), "Zusätzlich erteilte Stunden der KW{$dateTime->format('W')}");
            $dataList['TotalCanceled']['Name'] = new ToolTip('Absoluter Ausfall ' . new InfoIcon(),
                "Aufsummierung der ausgefallenen Stunden bis einschließlich der KW{$dateTime->format('W')}");
            $dataList['TotalAdditional']['Name'] = new ToolTip('Abs. zus. erteilte Stunden ' . new InfoIcon(),
                "Aufsummierung der zusätzlich erteilten Stunden bis einschließlich der KW{$dateTime->format('W')}");
            foreach ($subjectList as $acronym => $subject) {
                $columns[$acronym] = $acronym;
                $dataList['Canceled'][$acronym] = $canceledSubjectList[$acronym] ?? 0;
                $dataList['Additional'][$acronym] = $additionalSubjectList[$acronym] ?? 0;
                $dataList['TotalCanceled'][$acronym] = $subjectTotalCanceledList[$acronym] ?? 0;
                $dataList['TotalAdditional'][$acronym] = $subjectTotalAdditionalList[$acronym] ?? 0;
            }

            $remark = '&nbsp;';
            $checking = new Container('&nbsp;');
            if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivision, $tblGroup, $fromDate))) {
                $remark = str_replace("\n", '<br>', $tblLessonWeek->getRemark());
                if ($tblLessonWeek->getDateDivisionTeacher()) {
                    $checking .= new Container(new Success(new Check() . ' am ' . $tblLessonWeek->getDateDivisionTeacher() . ' von '
                            . (($divisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher())
                                ? $divisionTeacher->getLastName() : '') . ' für die Vollständigkeit der Angaben (Klassenlehrer) geprüft'));
                }

                if ($tblLessonWeek->getDateHeadmaster()) {
                    $checking .= new Container(new Success(new Check() . ' am ' . $tblLessonWeek->getDateHeadmaster() . ' von '
                        . (($headmaster = $tblLessonWeek->getServiceTblPersonHeadmaster())
                            ? $headmaster->getLastName() : '') . ' zur Kenntnis genommen (Schulleitung)'));
                }
            }

            return new Panel(
                'Wochenübersicht',
                (new TableData($dataList, null, $columns, false))->setHash('Week')
                    . new Bold('Wochenbemerkung:')
                    . new Container($remark)
                    . ($hasEdit
                        ? new Container((new Primary(
                            new Edit() . ' Bearbeiten',
                            ApiDigital::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonWeekRemarkModal($tblDivision ? $tblDivision->getId() : null,
                            $tblGroup ? $tblGroup->getId() : null, $fromDate->format('d.m.Y'))))
                        . new Container($checking)
                        : ''),
                Panel::PANEL_TYPE_INFO
            );
        }

        return '';
    }

    /**
     * @param DateTime $dateTime
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    public function getCanceledSubjectList(DateTime $dateTime, ?TblDivision $tblDivision, ?TblGroup $tblGroup): array
    {
        $fromDate = Timetable::useService()->getStartDateOfWeek($dateTime);
        $toDate = new DateTime($fromDate->format('d.m.Y'));
        $toDate = $toDate->add(new DateInterval('P4D'));

        $canceledSubjectList = array();
        $additionalSubjectList = array();
        if (($tblLessonContentList = $this->getLessonContentAllByBetween($fromDate, $toDate, $tblDivision, $tblGroup))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                if ($tblLessonContent->getIsCanceled() && ($tblSubject = $tblLessonContent->getServiceTblSubject())) {
                    if (isset($canceledSubjectList[$tblSubject->getAcronym()])) {
                        $canceledSubjectList[$tblSubject->getAcronym()]++;
                    } else {
                        $canceledSubjectList[$tblSubject->getAcronym()] = 1;
                    }
                }
                if (($tblSubstituteSubject = $tblLessonContent->getServiceTblSubstituteSubject())) {
                    if (isset($additionalSubjectList[$tblSubstituteSubject->getAcronym()])) {
                        $additionalSubjectList[$tblSubstituteSubject->getAcronym()]++;
                    } else {
                        $additionalSubjectList[$tblSubstituteSubject->getAcronym()] = 1;
                    }
                }
            }
        }

        $subjectList = array();
        if ($tblDivision) {
            // Falls es bereits Einträge im Klassenbuch gibt, werden diese Fächer in der Wochenübersicht angezeigt
            if (($tempList = $this->getSubjectListFromLessonContent($tblDivision))) {
                $subjectList = $tempList;
            // ansonsten die Fächer der Klasse
            } else {
                $this->setSubjectListByDivision($tblDivision, $subjectList);
            }
        } elseif ($tblGroup) {
            // Falls es bereits Einträge im Klassenbuch gibt, werden diese Fächer in der Wochenübersicht angezeigt
            if (($tempList = $this->getSubjectListFromLessonContent(null, $tblGroup))) {
                $subjectList = $tempList;
            // ansonsten die Fächer der Stammgruppe
            } else {
                if (($tblDivisionList = $tblGroup->getCurrentDivisionList())) {
                    foreach ($tblDivisionList as $tblDivisionItem) {
                        $this->setSubjectListByDivision($tblDivisionItem, $subjectList);
                    }
                }
            }
        }
        return array($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList);
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListFromLessonContent(TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return (new Data($this->getBinding()))->getSubjectListFromLessonContent($tblDivision, $tblGroup);
    }

    /**
     * @param TblDivision $tblDivision
     * @param array $subjectList
     */
    private function setSubjectListByDivision(TblDivision $tblDivision, array &$subjectList)
    {
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                    $subjectList[$tblSubject->getAcronym()] = $tblSubject;
                }
            }
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param DateTime $dateTime
     *
     * @return false|TblLessonWeek
     */
    public function getLessonWeekByDate(?TblDivision $tblDivision, ?TblGroup $tblGroup, DateTime $dateTime)
    {
        return (new Data($this->getBinding()))->getLessonWeekAllByDate($tblDivision, $tblGroup, $dateTime);
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblYear $tblYear
     * @param $date
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return TblLessonWeek
     */
    public function createLessonWeek(?TblDivision $tblDivision, ?TblGroup $tblGroup, TblYear $tblYear, $date, $Remark, $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher, $DateHeadmaster, ?TblPerson $serviceTblPersonHeadmaster
    ): TblLessonWeek {
        return (new Data($this->getBinding()))->createLessonWeek($tblDivision, $tblGroup, $tblYear, $date, $Remark, $DateDivisionTeacher,
            $serviceTblPersonDivisionTeacher, $DateHeadmaster, $serviceTblPersonHeadmaster);
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return bool
     */
    public function updateLessonWeek(
        TblLessonWeek $tblLessonWeek,
        $Remark,
        $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher,
        $DateHeadmaster,
        ?TblPerson $serviceTblPersonHeadmaster
    ): bool {
        return (new Data($this->getBinding()))->updateLessonWeek($tblLessonWeek, $Remark, $DateDivisionTeacher, $serviceTblPersonDivisionTeacher,
            $DateHeadmaster, $serviceTblPersonHeadmaster);
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     *
     * @return bool
     */
    public function updateLessonWeekRemark(
        TblLessonWeek $tblLessonWeek,
        $Remark
    ): bool {
        return (new Data($this->getBinding()))->updateLessonWeekRemark($tblLessonWeek, $Remark);
    }

    /**
     * @return string
     */
    public function getDigitalClassRegisterPanelForTeacher(): string
    {
        $resultList = array();
        $divisionList = array();
        $divisionSubjectList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $baseRoute = (Digital::useFrontend())::BASE_ROUTE;
            $tblYearList = Term::useService()->getYearByNow();
            // Fachlehrer in Klassen
            if ($tblYearList && ($tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson))) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                        && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                        && ($tblSubjectItem = $tblDivisionSubject->getServiceTblSubject())
                        && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    ) {
                        // nur aktuelles Schuljahre
                        if (!isset($tblYearList[$tblYearItem->getId()])) {
                            continue;
                        }

                        // Kurshefte
                        if (Division::useService()->getIsDivisionCourseSystem($tblDivisionItem)) {
                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                            if (!isset($divisionSubjectList[$tblDivisionSubject->getId()])) {
                                if (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())) {
                                    $divisionSubjectList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                                    $resultList[] = array(
                                        'Type' => $tblDivisionItem->getTypeName(),
                                        'Name' => $tblDivisionItem->getDisplayName() . ' - ' . $tblSubjectGroup->getName(),
                                        'Option' => new Standard(
                                            '',
                                            $baseRoute . '/CourseContent',
                                            new Extern(),
                                            array(
                                                'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                                'BasicRoute' => $baseRoute . '/Teacher'
                                            ),
                                            'Zum Kursheft wechseln'
                                        )
                                    );
                                } else {
                                    // Lehrauftrag kann an der Fachgruppe als auch an der Fachklasse (ohne Gruppe) sein
                                    if (($tblDivisionSubjectWithGroupList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivisionItem, $tblSubjectItem
                                    ))) {
                                        foreach ($tblDivisionSubjectWithGroupList as $item) {
                                            if (!isset($divisionSubjectList[$item->getId()]) && ($tblSubjectGroupItem = $item->getTblSubjectGroup())) {
                                                $divisionSubjectList[$item->getId()] = $item;
                                                $resultList[] = array(
                                                    'Type' => $tblDivisionItem->getTypeName(),
                                                    'Name' => $tblDivisionItem->getDisplayName() . ' - ' . $tblSubjectGroupItem->getName(),
                                                    'Option' => new Standard(
                                                        '',
                                                        $baseRoute . '/CourseContent',
                                                        new Extern(),
                                                        array(
                                                            'DivisionSubjectId' => $item->getId(),
                                                            'BasicRoute' => $baseRoute . '/Teacher'
                                                        ),
                                                        'Zum Kursheft wechseln'
                                                    )
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                            // Klassentagebuch
                        } elseif (!isset($divisionList[$tblDivisionItem->getId()])) {
                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                            $resultList[] = array(
                                'Type' => $tblDivisionItem->getTypeName(),
                                'Name' => $tblDivisionItem->getDisplayName(),
                                'Option' => new Standard(
                                    '',
                                    $baseRoute . '/LessonContent',
                                    new Extern(),
                                    array(
                                        'DivisionId' => $tblDivisionItem->getId(),
                                        'BasicRoute' => $baseRoute . '/Teacher'
                                    ),
                                    'Zum Klassenbuch wechseln'
                                )
                            );
                        }
                    }
                }
            }

            // Fachlehrer in Gruppen
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                        foreach ($tblPersonList as $tblPersonStudent) {
                            if (($tblDivisionMain = Student::useService()->getCurrentMainDivisionByPerson($tblPersonStudent))
                                && isset($divisionList[$tblDivisionMain->getId()])
                            ) {
                                // Kursheft sollen nicht extra für die Stammgruppen angezeigt werden SSWHD-1870
                                // Kurshefte
                                if (Division::useService()->getIsDivisionCourseSystem($tblDivisionMain)) {
//                                    foreach ($divisionSubjectList as $tblDivisionSubjectTemp) {
//                                        if (($tblDivisionTemp = $tblDivisionSubjectTemp->getTblDivision())
//                                            && $tblDivisionMain->getId() == $tblDivisionTemp->getId()
//                                            && ($tblSubjectGroupTemp = $tblDivisionSubjectTemp->getTblSubjectGroup())
//                                        ) {
//                                            $resultList[] = array(
//                                                'Type' => $tblDivisionTemp->getTypeName(),
//                                                'Name' => $tblGroup->getName() . ' - ' . $tblSubjectGroupTemp->getName(),
//                                                'Option' => new Standard(
//                                                    '',
//                                                    $baseRoute . '/CourseContent',
//                                                    new Extern(),
//                                                    array(
//                                                        'DivisionSubjectId' => $tblDivisionSubjectTemp->getId(),
//                                                        'GroupId' => $tblGroup->getId(),
//                                                        'BasicRoute' => $baseRoute . '/Teacher'
//                                                    ),
//                                                    'Zum Kursheft wechseln'
//                                                )
//                                            );
//                                        }
//                                    }
                                    // Klassentagebuch
                                } else {
                                    $resultList[] = array(
                                        'Type' => $tblDivisionMain->getTypeName(),
                                        'Name' => $tblGroup->getName(),
                                        'Option' => new Standard(
                                            '',
                                            $baseRoute . '/LessonContent',
                                            new Extern(),
                                            array(
                                                'GroupId' => $tblGroup->getId(),
                                                'BasicRoute' => $baseRoute . '/Teacher'
                                            ),
                                            'Zum Klassenbuch wechseln'
                                        )
                                    );
                                }

                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($resultList) {
            return new Panel(
                'Digitales Klassenbuch (Fachlehrer)',
                new TableData(
                    $resultList,
                    null,
                    array(
                    'Type' => 'Schulart',
                    'Name' => 'Klasse / Gruppe / Kurs',
                    'Option' => ''
                ),
                array(
                    'order' => array(
                        array('0', 'asc'),
                        array('1', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                        array('orderable' => false, 'width' => '1%', 'targets' => -1)
                    ),
                    'pageLength' => -1,
                    'paging' => false,
                    'info' => false,
                    'searching' => false,
                    'responsive' => false
                )),
                Panel::PANEL_TYPE_PRIMARY
            );
        }

        return '';
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function getIsLessonContentEditAllowed(TblLessonContent $tblLessonContent): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'IsChangeLessonContentByOtherTeacherAllowed'))
            && $tblSetting->getValue()
        ) {
            return true;
        } else {
            $tblPerson = Account::useService()->getPersonByLogin();
            // Schulleitung darf immer
            if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')) {
                return true;
            // Klassenlehrer darf immer
            } elseif ($tblPerson && ($tblDivision = $tblLessonContent->getServiceTblDivision())
                && Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson)
            ) {
                return true;
            // Tudor darf immer
            } elseif ($tblPerson && ($tblGroup = $tblLessonContent->getServiceTblGroup())
                && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                && ($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
            ) {
                return true;
            // Letzter Bearbeiter darf immer
            } else if (($tblPersonLessonContent = $tblLessonContent->getServiceTblPerson())
                && $tblPersonLessonContent->getId() == $tblPerson->getId()
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param DateTime $dateTime
     * @param int $lesson
     *
     * @return false|TblLessonContent
     */
    public function getTimetableFromLastLessonContent(?TblDivision $tblDivision, ?TblGroup $tblGroup, DateTime $dateTime, int $lesson)
    {
        // kein importierter Stundenplan für den Tag vorhanden
        if ($tblDivision && Timetable::useService()->getTimeTableNodeBy($tblDivision, $dateTime, null)) {
            return false;
        }

        $lastDateTime = (new DateTime($dateTime->format('d.m.Y')))->sub(new DateInterval('P7D'));
        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
        } else {
            $tblYear = false;
        }

        if ($tblYear) {
            list($startDateSchoolYear,) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear) {
                while ($lastDateTime > $startDateSchoolYear) {
                    // letzter Wochen Tag mit eingetragen Unterrichtseinheiten
                    if ($this->getLessonContentAllByDateAndLesson($lastDateTime, null, $tblDivision ?: null, $tblGroup ?: null)) {
                        // Eintrag für die Stunde finden
                        if (($tblLessonContentList = $this->getLessonContentAllByDateAndLesson($lastDateTime, $lesson, $tblDivision ?: null, $tblGroup ?: null))) {
                            // es darf nur ein Eintrag gefunden werden
                            if (count($tblLessonContentList) == 1) {
                                /** @var TblLessonContent $tblLessonContent */
                                $tblLessonContent = reset($tblLessonContentList);
                                // das Fach darf nicht ausgefallen sein
                                if (!$tblLessonContent->getIsCanceled()) {
                                    return $tblLessonContent;
                                }
                            }
                        }

                        return false;
                    }

                    $lastDateTime->sub(new DateInterval('P7D'));
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function getLessonContentLinkPanel(?TblDivision $tblDivision, ?TblGroup $tblGroup, TblSubject $tblSubject)
    {
        $divisionList = array();
        $groupList = array();
        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
        } else {
            $tblYear = false;
        }

        if (($tblPerson = Account::useService()->getPersonByLogin())
            && $tblYear
            && ($tblSubjectTeacherList = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson))
        ) {
            // alle Lehraufträge des Lehrers für das Fach im Schuljahr
            foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                    && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                    && ($tblSubjectItem = $tblDivisionSubject->getServiceTblSubject()) && $tblSubjectItem->getId() == $tblSubject->getId()
                    && ($tblYearItem = $tblDivisionItem->getServiceTblYear()) && $tblYearItem->getId() == $tblYear->getId()
                    && !Division::useService()->getIsDivisionCourseSystem($tblDivisionItem)
                ) {
                    $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                }
            }

            // Fachlehrer in Gruppen nur bei Gruppe anzeigen
            if ($tblGroup && ($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroupItem) {
                    if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupItem))) {
                        foreach ($tblPersonList as $tblPersonStudent) {
                            if (($tblDivisionMain = Student::useService()->getCurrentMainDivisionByPerson($tblPersonStudent))
                                && isset($divisionList[$tblDivisionMain->getId()])
                            ) {
                                $groupList[$tblGroupItem->getId()] = $tblGroupItem;
                            }
                        }
                    }
                }
            }

            $dataList = array();
            if ($tblDivision && isset($divisionList[$tblDivision->getId()]) && count($divisionList) > 1) {
                unset($divisionList[$tblDivision->getId()]);
                foreach ($divisionList as $item) {
                    $dataList[] = new CheckBox('Data[Link][' . $item->getId() . ']', $item->getDisplayName(), 1);
                }
            } elseif ($tblGroup && isset($groupList[$tblGroup->getId()]) && count($groupList) > 1) {
                unset($groupList[$tblGroup->getId()]);
                foreach ($groupList as $item) {
                    $dataList[] = new CheckBox('Data[Link][' . $item->getId() . ']', $item->getName(), 1);
                }
            }

            if ($dataList) {
                return new Panel(
                    'Thema/Hausaufgaben verknüpfen',
                    $dataList,
                    Panel::PANEL_TYPE_PRIMARY
                );
            }
        }

        return '';
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param int $LinkId
     *
     * @return TblLessonContentLink
     */
    public function createLessonContentLink(TblLessonContent $tblLessonContent, int $LinkId): TblLessonContentLink
    {
        return (new Data($this->getBinding()))->createLessonContentLink($tblLessonContent, $LinkId);
    }

    /**
     * @return int
     */
    public function getNextLinkId(): int
    {
        return (new Data($this->getBinding()))->getNextLinkId();
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return false | TblLessonContent[]
     */
    public function getLessonContentLinkAllByLessonContent(TblLessonContent $tblLessonContent)
    {
        return (new Data($this->getBinding()))->getLessonContentLinkAllByLessonContent($tblLessonContent);
    }

    /**
     * @param TblLessonContent[] $tblLessonContentList
     *
     * @return bool
     */
    public function destroyLessonContentLinkList(
        array $tblLessonContentList
    ): bool {
        return (new Data($this->getBinding()))->destroyLessonContentLinkList($tblLessonContentList);
    }

    /**
     * @param $LessonContentId
     *
     * @return string
     */
    public function getLessonContentLinkedDisplayPanel($LessonContentId): string
    {
        if (($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))
            && ($tblLessonContentLinkedList = $tblLessonContent->getLinkedLessonContentAll())
        ) {
            $panelContent = array();

            if (($tblDivision = $tblLessonContent->getServiceTblDivision())) {
                $panelContent[] = 'Klasse ' . $tblDivision->getDisplayName();
            } elseif (($tblGroup = $tblLessonContent->getServiceTblGroup())) {
                $panelContent[] = 'Stammgruppe ' . $tblGroup->getName();
            }

            foreach ($tblLessonContentLinkedList as $tblLessonContentItem) {
                if (($tblDivision = $tblLessonContentItem->getServiceTblDivision())) {
                    $panelContent[] = 'Klasse ' . $tblDivision->getDisplayName();
                } elseif (($tblGroup = $tblLessonContentItem->getServiceTblGroup())) {
                    $panelContent[] = 'Stammgruppe ' . $tblGroup->getName();
                }
            }

            if (!empty($panelContent)) {
                sort($panelContent);
                return new Panel(
                    'Verknüpfte Thema/Hausaufgaben',
                    $panelContent,
                    Panel::PANEL_TYPE_INFO
                );
            }
        }

        return '';
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return bool
     */
    public function getHasSaturdayLessonsBySchoolType(TblType $tblSchoolType): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'SaturdayLessonsSchoolTypes'))
            && ($tblSetting->getValue())
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
            && isset($tblSchoolTypeAllowedList[$tblSchoolType->getId()])
        ) {
            return true;
        } else {
            return false;
        }
    }
}