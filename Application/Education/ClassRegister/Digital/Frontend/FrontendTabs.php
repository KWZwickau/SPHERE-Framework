<?php
namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateInterval;
use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

class FrontendTabs extends FrontendCourseContent
{
    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendStudentList(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Schülerliste');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton($this->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/Student',
                                $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/Student', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        Digital::useService()->getStudentTable($tblDivisionCourse, $BasicRoute, '/Education/ClassRegister/Digital/Student')
                    )), new Title(new PersonGroup() . ' Schülerliste'))
                ))
            );
        } else {
            return new Danger('Kurs nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $BackDivisionCourseId
     * @param $BasicRoute
     *
     * @return Standard
     */
    public function getBackButton(TblDivisionCourse $tblDivisionCourse, $BackDivisionCourseId, $BasicRoute): Standard
    {
        if ($tblDivisionCourse->getType()->getIsCourseSystem() && $BackDivisionCourseId) {
            return new Standard(
                'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                    'DivisionCourseId' => $BackDivisionCourseId,
                    'BasicRoute' => $BasicRoute
                )
            );
        } else {
            return new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            );
        }
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendDownload(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Download');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton($this->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

            if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                $name = 'Kursliste';
                $printLink = (new Link((new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'), 'Kursheft'))->setPictureHeight(),
                    '/Api/Document/Standard/CourseContent/Create', null, array(
                        'DivisionCourseId' => $DivisionCourseId
                    )))->setExternal();
            } else {
                if (($isCoreGroup = $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                    $name = 'Stammgruppenliste';
                } else {
                    $name = 'Klassenliste';
                }

                $isCourseSystem = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);

                if ($isCourseSystem) {
                    $printLink = null;
                } else {
                    $printLink = (new Link((new Thumbnail(
                        FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'),
                        $isCoreGroup ?  'Stammgruppen&shy;tagebuch' : ' Klassen&shy;tagebuch'))->setPictureHeight(),
                        '/Api/Document/Standard/ClassRegister/Create', null, array(
                            'DivisionCourseId' => $DivisionCourseId
                        )))->setExternal();
                }
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/Download',
                                $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/Download', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!',
                                new Exclamation())
                        ),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), $name . ' Einverständnis&shy;erklärung'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/AgreementClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWMedical.png'), $name . ' Krankenakte'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' Schülerliste'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassList/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $name . ' zeugnis&shy;relevante Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/Absence/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $name . ' Monatliche Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/AbsenceMonthly/Download', null, array(
                                    'DivisionCourseId' => $DivisionCourseId
                                ))
                            , 2),
                        new LayoutColumn(
                            $printLink
                            , 2),
                    )), new Title(new Download() . ' Download')),
                    ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVOSG')
                        ? new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionCourseId' => $DivisionCourseId,
                                    'Type'    => 'downloadClassList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionCourseId' => $DivisionCourseId,
                                    'Type'    => 'downloadSignList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste Fremdsprachen'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionCourseId' => $DivisionCourseId,
                                    'Type'    => 'downloadElectiveClassList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Telefonliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionCourseId' => $DivisionCourseId,
                                    'Type'    => 'downloadClassPhoneList'
                                ))
                            , 2),
                    )), new Title(new Download() . ' Individual Download'))
                        : null
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     *
     * @return Stage
     */
    public function frontendIntegration($DivisionCourseId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = ''): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Integration verwalten');

        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                    array(
                        'DivisionCourseId' => $DivisionCourseId,
                        'BasicRoute' => $BasicRoute,
                    ))
            );
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $PersonPanel = new Panel('Person', $tblPerson->getLastFirstNameWithCallNameUnderline(), Panel::PANEL_TYPE_INFO);
            $DivisionPanel = new Panel('Kurse', DivisionCourse::useService()->getCurrentMainCoursesByPersonAndYear($tblPerson, $tblYear), Panel::PANEL_TYPE_INFO);
            $Content = (new Well(Student::useFrontend()->frontendIntegration($tblPerson)));
        } else {
            $PersonPanel = '';
            $DivisionPanel = '';
            $Content = (new Warning('Person wurde nicht gefunden.'));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $PersonPanel
                            , 6),
                        new LayoutColumn(
                            $DivisionPanel
                            , 6),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            $Content
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLectureship(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Unterrichtete Fächer / Lehrer');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton($this->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

            $content = Digital::useService()->getSubjectsAndLectureshipByDivisionCourse($tblDivisionCourse);

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/Lectureship',
                                $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/Lectureship', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new Listing() . ' Unterrichtete Fächer / Lehrer'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLessonWeek(
        $DivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Kontrolle');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton($this->getBackButton($tblDivisionCourse, null, $BasicRoute));

            $hasDivisionTeacherRight = (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson))
            );
            $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
            // Schulleitung soll auch die Klassenbücher für die Klassenlehrer abnehmen dürfen
            if ($hasHeadmasterRight)  {
                $hasDivisionTeacherRight = true;
            }

            $content = ApiDigital::receiverBlock($this->loadLessonWeekTable($tblDivisionCourse, $hasDivisionTeacherRight, $hasHeadmasterRight), 'LessonWeekContent');

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? null
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/LessonWeek', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new Ok() . ' Klassentagebuch Kontrolle'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $hasDivisionTeacherRight
     * @param bool $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return string
     */
    public function loadLessonWeekTable(TblDivisionCourse $tblDivisionCourse, bool $hasDivisionTeacherRight, bool $hasHeadmasterRight, string $Date = null): string
    {
        $content = '';
        $tblCompanyList = DivisionCourse::useService()->getCompanyListByDivisionCourse($tblDivisionCourse);
        $tblSchoolTypeList = DivisionCourse::useService()->getSchoolTypeListByDivisionCourse($tblDivisionCourse);
        $hasSaturdayLessons = Digital::useService()->getHasSaturdayLessonsBySchoolTypeList($tblSchoolTypeList ?: array());

        if (!($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            return new Danger('Kein Schuljahr gefunden!', new Exclamation());
        }

        $DivisionCourseId = $tblDivisionCourse->getId();

        /** @var DateTime $startDate */
        /** @var DateTime $endDate */
        list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
        if ($startDate && $endDate) {
            $dayOfWeek = $startDate->format('w');
            if ($hasSaturdayLessons) {
                // wenn Schuljahresbeginn ein Sonntag dann beginne mit der nächsten Woche
                if ($dayOfWeek == 0) {
                    $startDate->add(new DateInterval('P7D'));
                }
            } else {
                // wenn Schuljahresbeginn ein Samstag oder Sonntag dann beginne mit der nächsten Woche
                if ($dayOfWeek == 6 || $dayOfWeek == 0) {
                    $startDate->add(new DateInterval('P7D'));
                }
            }
            $startDate = Timetable::useService()->getStartDateOfWeek($startDate);
            $dataList = array();
            while ($startDate <= $endDate) {
                $dateString = $startDate->format('d.m.Y');

                // Prüfung, ob die gesamte Woche Ferien sind
                $isHoliday = Term::useService()->getIsSchoolWeekHoliday($dateString, $tblYear, $tblCompanyList ?: array(), $hasSaturdayLessons);

                // Rechte prüfen
                $newDivisionTeacher = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Unchecked() . ' noch nicht bestätigt')
                    . new PullRight(($hasDivisionTeacherRight
                            ? (new Link('Bestätigen', ApiDigital::getEndpoint(), new Check()))->ajaxPipelineOnClick(
                                ApiDigital::pipelineSaveLessonWeekCheck($DivisionCourseId, $dateString, 'DivisionTeacher', 'SET',
                                    $hasDivisionTeacherRight, $hasHeadmasterRight))
                            : '')
                        . '|');
                $newHeadmaster = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Unchecked() . ' noch nicht bestätigt')
                    . new PullRight($hasHeadmasterRight
                        ? (new Link('Bestätigen', ApiDigital::getEndpoint(), new Check()))->ajaxPipelineOnClick(
                            ApiDigital::pipelineSaveLessonWeekCheck($DivisionCourseId, $dateString, 'Headmaster', 'SET',
                                $hasDivisionTeacherRight, $hasHeadmasterRight))
                        : ''
                    );

                if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, $startDate))) {
                    if ($tblLessonWeek->getDateDivisionTeacher()) {
                        $divisionTeacherText = new Success(
                                new Check() . ' am ' . $tblLessonWeek->getDateDivisionTeacher() . ' von '
                                . (($divisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher()) ? $divisionTeacher->getLastName() : '')
                                . ' bestätigt.'
                            )
                            . new PullRight(
                                ($hasDivisionTeacherRight
                                    ? (new Link('Rückgängig', ApiDigital::getEndpoint(), new Unchecked()))->ajaxPipelineOnClick(
                                        ApiDigital::pipelineSaveLessonWeekCheck($DivisionCourseId, $dateString, 'DivisionTeacher', 'UNSET',
                                            $hasDivisionTeacherRight, $hasHeadmasterRight))
                                    : '')
                                . '|');
                    } else {
                        $divisionTeacherText = $newDivisionTeacher;
                    }

                    if ($tblLessonWeek->getDateHeadmaster()) {
                        $headmasterText = new Success(new Check() . ' am ' . $tblLessonWeek->getDateHeadmaster() . ' von '
                            . (($headmaster = $tblLessonWeek->getServiceTblPersonHeadmaster()) ? $headmaster->getLastName() : '')
                            . ' bestätigt.'
                            . new PullRight($hasHeadmasterRight
                                ? (new Link('Rückgängig', ApiDigital::getEndpoint(), new Unchecked()))->ajaxPipelineOnClick(
                                    ApiDigital::pipelineSaveLessonWeekCheck($DivisionCourseId, $dateString, 'Headmaster', 'UNSET',
                                        $hasDivisionTeacherRight, $hasHeadmasterRight))
                                : ''
                            )
                        );
                    } else {
                        $headmasterText = $newHeadmaster;
                    }
                } else {
                    $divisionTeacherText = $newDivisionTeacher;
                    $headmasterText = $newHeadmaster;
                }

                $displayWeek = new Bold('KW' . $startDate->format('W')) . ' (' . $dateString . ')';
                if ($dateString == $Date) {
                    $item = new Well(
                        Digital::useFrontend()->getWeekViewContent($dateString, $tblDivisionCourse, false, true)
                        . new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($displayWeek
                                . (new Link(' schließen', ApiDigital::getEndpoint()))
                                    ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonWeekContent($DivisionCourseId, $hasDivisionTeacherRight, $hasHeadmasterRight))
                                . new PullRight('|'), 4),
                            new LayoutColumn($divisionTeacherText, 4),
                            new LayoutColumn($headmasterText, 4),
                        ))))
                    );
                } else {
                    $item = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $displayWeek
                            . (new Link(' anzeigen', ApiDigital::getEndpoint()))
                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonWeekContent($DivisionCourseId, $hasDivisionTeacherRight, $hasHeadmasterRight, $dateString))
                            . new PullRight('|')
                            , 4),
                        new LayoutColumn($divisionTeacherText, 4),
                        new LayoutColumn($headmasterText, 4),
                    ))));
                }

                if (!$isHoliday) {
                    $dataList[] = $item;
                }

                $startDate->add(new DateInterval('P7D'));
            }

            $content = new Panel(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('KW' . new PullRight('|'), 4),
                    new LayoutColumn('Für die Vollständigkeit der Angaben (Klassenlehrer)' . new PullRight('|'), 4),
                    new LayoutColumn('Zur Kenntnis genommen (Schulleitung)', 4),
                )))),
                $dataList,
                Panel::PANEL_TYPE_PRIMARY
            );
        }

        return $content;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $Date
     *
     * @return Form
     */
    public function formLessonWeekRemark(TblDivisionCourse $tblDivisionCourse, DateTime $Date): Form
    {
        if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, $Date))) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Remark'] = $tblLessonWeek->getRemark();
            $Global->savePost();
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Data[Remark]', 'Wochenbemerkung', 'Wochenbemerkung', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiDigital::pipelineEditLessonWeekRemarkSave($tblDivisionCourse->getId(), $Date->format('d.m.Y')))
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendHoliday(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Ferien / Unterrichtsfreie Tage');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $stage->addButton($this->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

            $list = array();
            $dataList = array();

            if (($tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents())) {
                foreach ($tblCompanyList as $tblCompany) {
                    if (($tblYearHolidayAllByYearAndCompany = Term::useService()->getYearHolidayAllByYear($tblYear, $tblCompany))) {
                        $list = array_merge($list, $tblYearHolidayAllByYearAndCompany);
                    }
                }
            }
            if (($tblYearHolidayAllByYear = Term::useService()->getYearHolidayAllByYear($tblYear))) {
                $list = array_merge($list, $tblYearHolidayAllByYear);
            }

            $tblHolidayList = array();
            foreach ($list as $tblYearHoliday) {
                if (($item = $tblYearHoliday->getTblHoliday())) {
                    $tblHolidayList[$item->getId()] = $item;
                }
            }
            foreach ($tblHolidayList as $tblHoliday) {
                $dataList[] = array(
                    'FromDate' => $tblHoliday->getFromDate(),
                    'ToDate' => $tblHoliday->getToDate(),
                    'Name' => $tblHoliday->getName(),
                    'Type' => $tblHoliday->getTblHolidayType()->getName()
                );
            }
            $content = new TableData($dataList, null, array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Name' => 'Name',
                'Type' => 'Typ'
            ),
                array(
                    'order' => array(
                        array(0, 'desc'),
                        array(1, 'desc')
                    ),
                    'columnDefs' => array(
                        array('type' => 'de_date', 'targets' => array(0,1)),
                    )
                )
            );

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/Holiday',
                                $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/Holiday', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new Holiday() . ' Ferien / Unterrichtsfreie Tage'))
                ))
            );
        } else {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }
}