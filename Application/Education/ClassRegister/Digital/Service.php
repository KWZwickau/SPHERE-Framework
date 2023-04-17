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
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContentLink;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
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
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

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
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        return (new Data($this->getBinding()))->migrateYear($tblYear);
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
     * @param $YearId
     * @param $HasAllYears
     * @param $HasCurrentYears
     * @param $yearFilterList
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $YearId, $HasAllYears, $HasCurrentYears, &$yearFilterList): array
    {
        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList && !$HasCurrentYears) {
            $tblYear = end($tblYearList);
        }
        $isCurrentYears = $HasCurrentYears && !$IsAllYears && !$YearId;

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
                    if ($isCurrentYears) {
                        $yearFilterList[$tblYearItem->getId()] = $tblYearItem;
                    }

                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route, null, array('YearId' => $tblYearItem->getId())));
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

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return LayoutRow
     */
    public function getHeadLayoutRow(TblDivisionCourse $tblDivisionCourse): LayoutRow
    {
        $content[] = $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName();

        // SekII-Kurs
        if ($tblDivisionCourse->getType()->getIsCourseSystem()
            && ($tblSubject = $tblDivisionCourse->getServiceTblSubject())
        ) {
            $content[] = 'Fach: ' . $tblSubject->getDisplayName();
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse, $tblSubject))
            ) {
                $subjectTeacherList = array();
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson())) {
                        $subjectTeacherList[] = $tblPersonTeacher->getFullName();
                    }
                }
                if ($subjectTeacherList) {
                    $content[] = 'Fachlehrer: ' . implode(', ', $subjectTeacherList);
                }
            }
        }

        // Gruppenlehrer
        $divisionTeacherList = array();
        if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblDivisionTeacher */
            foreach ($tblCustodyMemberList as $tblDivisionTeacher) {
                if (($tblPersonDivisionTeacher = $tblDivisionTeacher->getServiceTblPerson())) {
                    $divisionTeacherList[] = $tblPersonDivisionTeacher->getFullName()
                        . ($tblDivisionTeacher->getDescription() ? ' ' . $tblDivisionTeacher->getDescription() : '');
                }
            }
        }
        if ($divisionTeacherList) {
            $content[] = $tblDivisionCourse->getDivisionTeacherName() . ': ' . implode(', ', $divisionTeacherList);
        }

        // Elternvertreter
        $custodyList = array();
        if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblCustody */
            foreach ($tblCustodyMemberList as $tblCustody) {
                if (($tblPersonCustody = $tblCustody->getServiceTblPerson())) {
                    $custodyList[] = $tblPersonCustody->getFullName()
                        . ($tblCustody->getDescription() ? ' ' . $tblCustody->getDescription() : '');
                }
            }
        }
        if ($custodyList) {
            $content[] = 'Elternvertreter: ' . implode(', ', $custodyList);
        }

        // Schülersprecher
        $representativeList = array();
        if (($tblRepresentativeMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblRepresentative */
            foreach ($tblRepresentativeMemberList as $tblRepresentative) {
                if (($tblPersonRepresentative = $tblRepresentative->getServiceTblPerson())) {
                    $representativeList[] = $tblPersonRepresentative->getFirstSecondName() . ' ' . $tblPersonRepresentative->getLastName()
                        . ($tblRepresentative->getDescription() ? ' ' . $tblRepresentative->getDescription() : '');
                }
            }
        }
        if ($representativeList) {
            $content[] = 'Schülersprecher: ' . implode(', ', $representativeList);
        }

        return new LayoutRow(array(
            new LayoutColumn(new Panel($tblDivisionCourse->getTypeName(), $content, Panel::PANEL_TYPE_INFO), 6),
            new LayoutColumn(new Panel('Schuljahr', ($tblYear = $tblDivisionCourse->getServiceTblYear()) ? $tblYear->getDisplayName() : '', Panel::PANEL_TYPE_INFO), 6)
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $Route
     * @param string $BasicRoute
     *
     * @return LayoutRow
     */
    public function getHeadButtonListLayoutRow(TblDivisionCourse $tblDivisionCourse,
        string $Route = '/Education/ClassRegister/Digital/LessonContent', string $BasicRoute = ''): LayoutRow
    {
        $isCourseSystem = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);
        $DivisionCourseId = $tblDivisionCourse->getId();

        if ($isCourseSystem) {
            $buttonList[] = $this->getButton('Kursheft auswählen', '/Education/ClassRegister/Digital/SelectCourse', new Book(),
                $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/SelectCourse');
        } else {
            $buttonList[] = $this->getButton('Klassentagebuch', '/Education/ClassRegister/Digital/LessonContent', new Book(),
                $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonContent');
        }

        // Klassentagebuch Kontrolle: nur für Klassenlehrer, Tudor oder Schulleitung
        if ((($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson))
            )
            || Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')
        ) {
            // Klassentagebuch Kontrolle: nicht bei Kurssystemen
            if (!$isCourseSystem) {
                $buttonList[] = $this->getButton('Klassentagebuch Kontrolle', '/Education/ClassRegister/Digital/LessonWeek', new Ok(),
                    $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonWeek');
            }
        }

        $buttonList[] = $this->getButton('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');

        // Fehlzeiten (Kalenderansicht) nur bei Klassen anzeigen
        $buttonList[] = $this->getButton('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth',
            new Calendar(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');

        // Belehrungen: nicht bei Kurssystemen → Belehrungen direkt im Kursheft
        if (!$isCourseSystem) {
            $buttonList[] = $this->getButton('Belehrungen', '/Education/ClassRegister/Digital/Instruction',
                new CommodityItem(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
        }
        $buttonList[] = $this->getButton('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship',
            new Listing(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButton('Ferien', '/Education/ClassRegister/Digital/Holiday',
            new Holiday(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        $buttonList[] = $this->getButton('Download', '/Education/ClassRegister/Digital/Download',
            new Download(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return new LayoutRow(new LayoutColumn($buttonList));
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionCourseId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButton(string $name, string $route, $icon, $DivisionCourseId, $BasicRoute, bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionCourseId
     * @param $BackDivisionCourseId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButtonCourseSystem(string $name, string $route, $icon, $DivisionCourseId, $BackDivisionCourseId,
        $BasicRoute, bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'BackDivisionCourseId' => $BackDivisionCourseId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $Route
     * @param string $BasicRoute
     * @param null $BackDivisionCourseId
     *
     * @return LayoutRow
     */
    public function getHeadButtonListLayoutRowForCourseSystem(TblDivisionCourse $tblDivisionCourse,
        string $Route = '/Education/ClassRegister/Digital/CourseContent', string $BasicRoute = '', $BackDivisionCourseId = null): LayoutRow
    {
        $DivisionCourseId = $tblDivisionCourse->getId();
        $buttonList[] = $this->getButtonCourseSystem('Kursheft', '/Education/ClassRegister/Digital/CourseContent', new Book(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseContent');

        // Kursheft Kontrolle: nur für Schulleitung
        if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')) {
            $buttonList[] = $this->getButtonCourseSystem('Kursheft Kontrolle', '/Education/ClassRegister/Digital/CourseControl', new Ok(),
                $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseControl');
        }

        $buttonList[] = $this->getButtonCourseSystem('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');
        $buttonList[] = $this->getButtonCourseSystem('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth', new Calendar(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');
        $buttonList[] = $this->getButtonCourseSystem('Belehrungen', '/Education/ClassRegister/Digital/Instruction', new CommodityItem(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
//        $buttonList[] = $this->getButtonCourseSystem('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship', new Listing(),
//            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButtonCourseSystem('Ferien', '/Education/ClassRegister/Digital/Holiday', new Holiday(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        $buttonList[] = $this->getButtonCourseSystem('Download', '/Education/ClassRegister/Digital/Download', new Download(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return new LayoutRow(new LayoutColumn($buttonList));
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblLessonContent
     */
    public function createLessonContent($Data, int $lesson, TblDivisionCourse $tblDivisionCourse): TblLessonContent
    {
        $tblPerson = Account::useService()->getPersonByLogin();
//        $tblPerson = Person::useService()->getPersonById($Data['serviceTblPerson'])

        return (new Data($this->getBinding()))->createLessonContent(
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Room'],
            $tblDivisionCourse,
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDate($date, $tblDivisionCourse);
    }

    /**
     * @param DateTime $date
     * @param int|null $lesson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDateAndLesson(DateTime $date, ?int $lesson, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDateAndLesson($date, $lesson, $tblDivisionCourse);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblLessonContent|null $tblLessonContent
     *
     * @return bool|Form
     */
    public function checkFormLessonContent($Data, TblDivisionCourse $tblDivisionCourse, TblLessonContent $tblLessonContent = null)
    {
        $error = false;
        $form = Digital::useFrontend()->formLessonContent($tblDivisionCourse, $tblLessonContent ? $tblLessonContent->getId() : null);

        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getCourseContentListBy($tblDivisionCourse);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblCourseContent|null $tblCourseContent
     *
     * @return false|Form
     */
    public function checkFormCourseContent(
        $Data,
        TblDivisionCourse $tblDivisionCourse,
        TblCourseContent $tblCourseContent = null
    ) {
        $error = false;

        $form = Digital::useFrontend()->formCourseContent(
            $tblDivisionCourse, $tblCourseContent ? $tblCourseContent->getId() : null
        );
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function createCourseContent($Data, TblDivisionCourse $tblDivisionCourse): bool
    {
        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        (new Data($this->getBinding()))->createCourseContent(
            $tblDivisionCourse,
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
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function updateBulkCourseContentHeadmaster(TblDivisionCourse $tblDivisionCourse)
    {
        $updateList = array();
        if (($tblCourseContentList = $this->getCourseContentListBy($tblDivisionCourse))) {
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $BasicRoute
     * @param string $ReturnRoute
     *
     * @return string
     */
    public function getStudentTable(TblDivisionCourse $tblDivisionCourse, string $BasicRoute, string $ReturnRoute): string
    {
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (list($fromDate, $tillDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear))
            && $fromDate
            && $tillDate
        ) {
            $studentTable = array();
            $count = 0;
            $hasColumnCourse = false;
            foreach ($tblPersonList as $tblPerson) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                } else {
                    $tblCompany = false;
                    $tblSchoolType = false;
                    $tblCourse = false;
                }

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

                if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                    $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                } else {
                    $courseName = $tblCourse ? $tblCourse->getName() : '';
                }
                if (!$hasColumnCourse && $courseName) {
                    $hasColumnCourse = true;
                }

                $medicalRecord = '';
                $agreement = '';
                $integration = '';

                if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
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
                $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $fromDate, $tillDate, $excusedLessons);
                $unExcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $fromDate, $tillDate, $unExcusedLessons);
                $absenceDays = ($excusedDays + $unExcusedDays) . ' (' . new Success($excusedDays) . ', '
                    . new Danger($unExcusedDays) . ')';
                $absenceLessons = ($excusedLessons + $unExcusedLessons) . ' (' . new Success($excusedLessons) . ', '
                    . new Danger($unExcusedLessons) . ')';

                $studentTable[] = array(
                    'Number'        => ++$count,
                    'Name'          => $tblPerson->getLastFirstNameWithCallNameUnderline(),
                    'Picture'       => $PersonPicture,
                    'Info'          => $integration . $medicalRecord . $agreement,
                    'Gender'        => $Gender,
                    'Address'       => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiTwoRowString() : '',
                    'Phone'         => $contacts['PhoneFixed'] ?? '',
                    'Mail'          => $contacts['MailFrontendListFixed'] ?? '',
                    'Birthday'      => $birthday,
                    'Course'        => $courseName,
                    'AbsenceDays'   => $absenceDays,
                    'AbsenceLessons'=> $absenceLessons,
                    'Option'        =>
                        (new Standard(
                            '', '/Education/ClassRegister/Digital/AbsenceStudent', new Time(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $BasicRoute,
                                'ReturnRoute'=> $ReturnRoute
                            ),
                            'Fehlzeiten des Schülers verwalten'
                        ))
                        . (new Standard(
                            '', '/Education/ClassRegister/Digital/Integration', new Commodity(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $BasicRoute,
                                'ReturnRoute'=> $ReturnRoute,
                            ),
                            'Integration des Schülers verwalten'
                        ))
                );
            }

            $columns['Number'] = '#';
            $columns['Name'] = 'Name';
            $columns['Picture'] = 'Foto';
            if ($hasColumnCourse) {
                $columns['Course'] = 'Bildungs&shy;gang';
            }
            $columns['Info'] = 'Info';
            $columns['Gender'] = 'Ge&shy;schlecht';
            $columns['Birthday'] = 'Geburts&shy;datum';
            $columns['Address'] = 'Adresse';
            $columns['Phone'] = new ToolTip('Telefon '. new InfoIcon(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax; Bev.=Bevollmächtigt; Vorm.=Vormund; NK=Notfallkontakt');
            $columns['Mail'] = 'E-Mail';
            $columns['AbsenceDays'] = 'Zeugnis&shy;relevante Fehlzeiten Tage<br>(E, U)';
            $columns['AbsenceLessons'] = 'Zeugnis&shy;relevante Fehlzeiten UE<br>(E, U)';
            $columns['Option'] = '';

            return
                ApiSupportReadOnly::receiverOverViewModal()
                . MedicalRecordReadOnly::receiverOverViewModal()
                . ApiAgreement::receiverOverViewModal()
                . ApiPersonPicture::receiverModal()
                . (($inActivePanel = Person::useFrontend()->getInActiveStudentPanel($tblDivisionCourse)) ? $inActivePanel : '')
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function getSubjectsAndLectureshipByDivisionCourse(TblDivisionCourse $tblDivisionCourse): string
    {
        $dataList = array();
        if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)) {
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                $tempList = array();
                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourse, 1))) {
                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                        if (($tblDivisionCourseSubject = $tblStudentSubject->getTblDivisionCourse())
                            && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                            && !isset($tempList[$tblSubject->getId()][$tblDivisionCourseSubject->getId()])
                        ) {
                            $tempList[$tblSubject->getId()][$tblDivisionCourseSubject->getId()] = 1;
                        }
                    }
                }

                foreach ($tempList as $subjectId => $courseIdList) {
                    if (($tblSubjectItem = Subject::useService()->getSubjectById($subjectId))) {
                        $listing = array();
                        foreach ($courseIdList as $courseId => $value) {
                            if (($tblDivisionCourseItem = DivisionCourse::useService()->getDivisionCourseById($courseId))) {
                                if (($teacherNameList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseItem, $tblSubjectItem, $tblYear))) {
                                    $listing[] = new PullClear($tblDivisionCourseItem->getDisplayName() . new PullRight(implode(', ', $teacherNameList)));
                                }
                            }
                        }

                        $dataList[] = array(
                            'Subject' => $tblSubjectItem->getDisplayName(),
                            'Teacher' => empty($listing) ? '' : new \SPHERE\Common\Frontend\Layout\Repository\Listing($listing)
                        );
                    }
                }
            }
        } else {
            if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))
                && ($tblDivisionCourseListStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                $tblDivisionCourseListStudents = $this->getSorter($tblDivisionCourseListStudents)->sortObjectBy('Name', new StringNaturalOrderSorter());
                foreach ($tblSubjectList as $tblSubject) {
                    $listing = array();
                    /** @var TblDivisionCourse $tblDivisionCourseStudent */
                    foreach ($tblDivisionCourseListStudents as $tblDivisionCourseStudent) {
                        if (($teacherNameList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseStudent, $tblSubject, $tblYear))) {
                            $listing[] = new PullClear($tblDivisionCourseStudent->getDisplayName() . new PullRight(implode(', ', $teacherNameList)));
                        }
                    }

                    $dataList[] = array(
                        'Subject' => $tblSubject->getDisplayName(),
                        'Teacher' => empty($listing) ? '' : new \SPHERE\Common\Frontend\Layout\Repository\Listing($listing)
                    );
                }
            }
        }

        $columns = array(
            'Subject' => 'Unterrichtsfach',
            'Teacher' => 'Kurs' . new PullRight('Lehrer')
        );

        return (new TableData($dataList, new Title($tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getDisplayName()), $columns, null))
            ->setHash('Table_Division_' . $tblDivisionCourse->getId());
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblYear $tblYear
     *
     * @return array
     */
    private function getSubjectTeacherNameListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, TblYear $tblYear): array
    {
        $teacherList = array();
        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP
            && $tblDivisionCourse->getServiceTblSubject()
            && $tblDivisionCourse->getServiceTblSubject()->getId() == $tblSubject->getId()
            && ($tblPersonTeacher = $tblDivisionCourse->getFirstDivisionTeacher())
        ) {
            $teacherList[] = $tblPersonTeacher->getFullName();
        } elseif (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse, $tblSubject))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson())) {
                    // Fach // Kurse -> Lehrer
                    $teacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName();
                }
            }
        }

        return $teacherList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getSubjectsAndLectureshipByDivisionForDownload(TblDivisionCourse $tblDivisionCourse): array
    {
        $dataList = array();

        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))
            && ($tblDivisionCourseListStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblDivisionCourseListStudents = $this->getSorter($tblDivisionCourseListStudents)->sortObjectBy('Name', new StringNaturalOrderSorter());
            foreach ($tblSubjectList as $tblSubject) {
                $teacherNameList = array();
                /** @var TblDivisionCourse $tblDivisionCourseStudent */
                foreach ($tblDivisionCourseListStudents as $tblDivisionCourseStudent) {
                    if (($tempList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseStudent, $tblSubject, $tblYear))) {
                        foreach ($tempList as $personId => $name) {
                            if (!isset($teacherNameList[$personId])) {
                                $teacherNameList[$personId] = $name;
                            }
                        }
                    }
                }

                $dataList[$tblSubject->getAcronym()] = array(
                    'Subject' => $tblSubject->getDisplayName(),
                    'TeacherArray' => $teacherNameList
                );
            }

            ksort($dataList);
        }

        return $dataList;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByBetween(DateTime $fromDate, DateTime $toDate, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByBetween($fromDate, $toDate, $tblDivisionCourse);
    }

    /**
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getLessonContentCanceledSubjectList(DateTime $toDate, TblDivisionCourse $tblDivisionCourse): array
    {
        $subjectCancelList = array();
        $subjectAdditionalList = array();
        if (($tblLessonContentList = (new Data($this->getBinding()))->getLessonContentCanceledAllByToDate($toDate, $tblDivisionCourse))) {
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $hasEdit
     *
     * @return Panel|string
     */
    public function getCanceledSubjectOverview(DateTime $dateTime, TblDivisionCourse $tblDivisionCourse, bool $hasEdit = true)
    {
        list($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList) = $this->getCanceledSubjectList($dateTime, $tblDivisionCourse);

        list($subjectTotalCanceledList, $subjectTotalAdditionalList) = $this->getLessonContentCanceledSubjectList($toDate, $tblDivisionCourse);

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
            if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, $fromDate))) {
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
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonWeekRemarkModal($tblDivisionCourse, $fromDate->format('d.m.Y'))))
                        . new Container($checking)
                        : ''),
                Panel::PANEL_TYPE_INFO
            );
        }

        return '';
    }

    /**
     * @param DateTime $dateTime
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getCanceledSubjectList(DateTime $dateTime, TblDivisionCourse $tblDivisionCourse): array
    {
        $fromDate = Timetable::useService()->getStartDateOfWeek($dateTime);
        $toDate = new DateTime($fromDate->format('d.m.Y'));
        $toDate = $toDate->add(new DateInterval('P4D'));

        $canceledSubjectList = array();
        $additionalSubjectList = array();
        if (($tblLessonContentList = $this->getLessonContentAllByBetween($fromDate, $toDate, $tblDivisionCourse))) {
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
        // Falls es bereits Einträge im Klassenbuch gibt, werden diese Fächer in der Wochenübersicht angezeigt
        if (($tempList = $this->getSubjectListFromLessonContent($tblDivisionCourse))) {
            $subjectList = $tempList;
        // ansonsten die Fächer der Klasse
        } else {
            $this->setSubjectListByDivision($tblDivisionCourse, $subjectList);
        }

        return array($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListFromLessonContent(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getSubjectListFromLessonContent($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $subjectList
     */
    private function setSubjectListByDivision(TblDivisionCourse $tblDivisionCourse, array &$subjectList)
    {
        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))) {
            foreach ($tblSubjectList as $tblSubject) {
                $subjectList[$tblSubject->getAcronym()] = $tblSubject;
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     *
     * @return false|TblLessonWeek
     */
    public function getLessonWeekByDate(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime)
    {
        return (new Data($this->getBinding()))->getLessonWeekAllByDate($tblDivisionCourse, $dateTime);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $date
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return TblLessonWeek
     */
    public function createLessonWeek(TblDivisionCourse $tblDivisionCourse, $date, $Remark, $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher, $DateHeadmaster, ?TblPerson $serviceTblPersonHeadmaster
    ): TblLessonWeek {
        return (new Data($this->getBinding()))->createLessonWeek($tblDivisionCourse, $date, $Remark, $DateDivisionTeacher,
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
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $baseRoute = (Digital::useFrontend())::BASE_ROUTE;

            $tblDivisionCourseList = array();
            $checkedDivisionCourseList = array();
            // Lehraufträge -> dann alle Schüler des Lehrauftrags -> alle Klassen, Stammgruppen und SekII-Kurse der Schüler
            if (($tblYearList = Term::useService()->getYearByNow())) {
                foreach ($tblYearList as $tblYear) {
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                                && !isset($tblDivisionCourseList[$tblDivisionCourse->getId()])
                                && !isset($checkedDivisionCourseList[$tblDivisionCourse->getId()])
                            ) {
                                // SekII-Kurse
                                if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                                    $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                    $checkedDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                } else {
                                    if (($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse(
                                        $tblDivisionCourse
                                    ))) {
                                        foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                                            if (isset($checkedDivisionCourseList[$tblDivisionCourseStudent->getId()])) {
                                                continue;
                                            }

                                            if (!isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])) {
                                                $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                            }

                                            $checkedDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                        }
                                    }
                                }


                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // Klassentagebuch
                if ($tblDivisionCourse->getIsDivisionOrCoreGroup()) {
                    $resultList[] = array(
                        'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                        'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                        'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                        'Option' => new Standard(
                            '',
                            $baseRoute . '/LessonContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Klassenbuch wechseln'
                        )
                    );
                // Kursheft (SekII-Kurs)
                } elseif ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                    $resultList[] = array(
                        'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                        'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                        'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                        'Option' => new Standard(
                            '',
                            $baseRoute . '/CourseContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Kursheft wechseln'
                        )
                    );
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
                        'DivisionCourse' => 'Kurs',
                        'DivisionCourseType' => 'Kurs-Typ',
                        'SchoolTypes' => 'Schularten',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                ),
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
            } elseif ($tblPerson
                && ($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson)
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     * @param int $lesson
     *
     * @return false|TblLessonContent
     */
    public function getTimetableFromLastLessonContent(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime, int $lesson)
    {
        // kein importierter Stundenplan für den Tag vorhanden
        if (Timetable::useService()->getTimeTableNodeBy($tblDivisionCourse, $dateTime, null)) {
            return false;
        }

        $lastDateTime = (new DateTime($dateTime->format('d.m.Y')))->sub(new DateInterval('P7D'));

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            list($startDateSchoolYear,) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear) {
                while ($lastDateTime > $startDateSchoolYear) {
                    // letzter Wochen Tag mit eingetragen Unterrichtseinheiten
                    if ($this->getLessonContentAllByDateAndLesson($lastDateTime, null, $tblDivisionCourse)) {
                        // Eintrag für die Stunde finden
                        if (($tblLessonContentList = $this->getLessonContentAllByDateAndLesson($lastDateTime, $lesson, $tblDivisionCourse))) {
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function getLessonContentLinkPanel(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $tblDivisionCourseList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
        ) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblDivisionCourseTeacher = $tblTeacherLectureship->getTblDivisionCourse())
                    && !isset($tblDivisionCourseList[$tblDivisionCourseTeacher->getId()])
                    && ($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourseTeacher))
                ) {
                    foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                        if ($tblDivisionCourseStudent->getIsDivisionOrCoreGroup()
                            && !isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])
                            && !DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourseStudent)
                        ) {
                            $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                        }
                    }
                }
            }

            $dataList = array();
            if (isset($tblDivisionCourseList[$tblDivisionCourse->getId()]) && count($tblDivisionCourseList) > 1) {
                unset($tblDivisionCourseList[$tblDivisionCourse->getId()]);
                $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName');
                /** @var TblDivisionCourse $item */
                foreach ($tblDivisionCourseList as $item) {
                    $dataList[] = new CheckBox('Data[Link][' . $item->getId() . ']', $item->getDisplayName(), 1);
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

            if (($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())) {
                $panelContent[] = $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getDisplayName();
            }

            foreach ($tblLessonContentLinkedList as $tblLessonContentItem) {
                if (($tblDivisionCourseItem = $tblLessonContentItem->getServiceTblDivisionCourse())) {
                    $panelContent[] = $tblDivisionCourseItem->getTypeName() . ' ' . $tblDivisionCourseItem->getDisplayName();
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

    /**
     * @param TblType[] $tblSchoolTypeList
     *
     * @return bool
     */
    public function getHasSaturdayLessonsBySchoolTypeList(array $tblSchoolTypeList): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'SaturdayLessonsSchoolTypes'))
            && ($tblSetting->getValue())
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if (isset($tblSchoolTypeAllowedList[$tblSchoolType->getId()])) {
                    return true;
                }
            }
        }

        return false;
    }
}