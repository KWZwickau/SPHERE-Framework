<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateInterval;
use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
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
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendStudentList(
        $DivisionId = null,
        $GroupId = null,
        $DivisionSubjectId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Schülerliste');
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            if ($GroupId) {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'GroupId' => $GroupId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            } else {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'DivisionId' => $DivisionId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            }
        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
            ));
        }

        if ($tblDivision || $tblGroup || $tblDivisionSubject) {
            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear, $tblDivisionSubject ?: null
                        ),
                        $tblDivisionSubject
                            ? Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                                '/Education/ClassRegister/Digital/Student', $BasicRoute)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                                '/Education/ClassRegister/Digital/Student', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        Digital::useService()->getStudentTable($tblDivision ?: null, $tblGroup ?: null, $BasicRoute,
                            '/Education/ClassRegister/Digital/Student', $tblDivisionSubject ?: null)
                    )), new Title(new PersonGroup() . ' Schülerliste'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendDownload(
        $DivisionId = null,
        $GroupId = null,
        $DivisionSubjectId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Download');

        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            if ($GroupId) {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'GroupId' => $GroupId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            } else {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'DivisionId' => $DivisionId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            }
        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
            ));
        }

        if ($tblDivision || $tblGroup || $tblDivisionSubject) {
            if ($tblDivisionSubject
                && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
            ) {
                $name = 'Kursliste';
                $printLink = (new Link((new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'), 'Kursheft'))->setPictureHeight(),
                    '/Api/Document/Standard/CourseContent/Create', null, array(
                        'DivisionId' => $DivisionId,
                        'SubjectId' => $tblSubject->getId(),
                        'SubjectGroupId' => $tblSubjectGroup->getId()
                    )))->setExternal();
            } else {
                if ($tblGroup) {
                    $name = 'Stammgruppenliste';
                } else {
                    $name = 'Klassenliste';
                }

                $isCourseSystem = ($tblDivision && Division::useService()->getIsDivisionCourseSystem($tblDivision))
                    || ($tblGroup && $tblGroup->getIsGroupCourseSystem());

                if ($isCourseSystem) {
                    $printLink = null;
                } else {
                    $printLink = (new Link((new Thumbnail(
                        FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'),
                        $tblDivision ? ' Klassen&shy;tagebuch' : 'Stammgruppen&shy;tagebuch'))->setPictureHeight(),
                        '/Api/Document/Standard/ClassRegister/Create', null, array(
                            'DivisionId' => $DivisionId,
                            'GroupId' => $GroupId,
                            'YearId' => $tblYear ? $tblYear->getId() : null
                        )))->setExternal();
                }
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear, $tblDivisionSubject ?: null
                        ),
                        $tblDivisionSubject
                            ? Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                                '/Education/ClassRegister/Digital/Download', $BasicRoute)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                                '/Education/ClassRegister/Digital/Download', $BasicRoute)
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
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId,
                                    'DivisionSubjectId' => $DivisionSubjectId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWMedical.png'), $name . ' Krankenakte'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId,
                                    'DivisionSubjectId' => $DivisionSubjectId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' Schülerliste'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassList/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId,
                                    'DivisionSubjectId' => $DivisionSubjectId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $name . ' zeugnis&shy;relevante Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/Absence/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId,
                                    'DivisionSubjectId' => $DivisionSubjectId
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $name . ' Monatliche Fehlzeiten'))->setPictureHeight(),
                                '/Api/Reporting/Standard/Person/ClassRegister/AbsenceMonthly/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId,
                                    'DivisionSubjectId' => $DivisionSubjectId
                                ))
                            , 2),
                        new LayoutColumn(
                            $printLink
                            , 2),
                    )), new Title(new Download() . ' Download')),
                    ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVOSG') && $tblDivision
                        ? new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionId' => $DivisionId,
                                    'Type'    => 'downloadClassList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionId' => $DivisionId,
                                    'Type'    => 'downloadSignList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste Fremdsprachen'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionId' => $DivisionId,
                                    'Type'    => 'downloadElectiveClassList'
                                ))
                            , 2),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Telefonliste'),
                                '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                    'DivisionId' => $DivisionId,
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
     * @param null $DivisionId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return Stage
     */
    public function frontendIntegration($DivisionId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = '',
        $GroupId = null, $DivisionSubjectId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Integration verwalten');

        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                    array(
                        'DivisionSubjectId' => $DivisionSubjectId,
                        'DivisionId' => $GroupId ? null : $DivisionId,
                        'GroupId'    => $GroupId,
                        'BasicRoute' => $BasicRoute,
                    ))
            );
        }

        $PersonPanel = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $PersonPanel = new Panel('Person', $tblPerson->getLastFirstNameWithCallNameUnderline(), Panel::PANEL_TYPE_INFO);
        }
        $DivisionPanel = '';
        if(($tblDivision = Division::useService()->getDivisionById($DivisionId))){
            $DivisionPanel = new Panel('Klasse, Schulart', $tblDivision->getDisplayName().', '.$tblDivision->getTypeName(), Panel::PANEL_TYPE_INFO);
        }


        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $Content = (new Well(Student::useFrontend()->frontendIntegration($tblPerson)));
        } else {
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
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLectureship(
        $DivisionId = null,
        $GroupId = null,
        $DivisionSubjectId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Unterrichtete Fächer / Lehrer');

        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            if ($GroupId) {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'GroupId' => $GroupId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            } else {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'DivisionId' => $DivisionId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            }
        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
            ));
        }

        if ($tblDivision || $tblGroup || $tblDivisionSubject) {
            if ($tblGroup) {
                $content = '';
                if (($tblDivisionList = $tblGroup->getCurrentDivisionList())) {
                    foreach ($tblDivisionList as $tblGroupDivision) {
                        $content .= Digital::useService()->getSubjectsAndLectureshipByDivision($tblGroupDivision);
                    }
                }
            } else {
                $content = Digital::useService()->getSubjectsAndLectureshipByDivision($tblDivision);
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear, $tblDivisionSubject ?: null
                        ),
                        $tblDivisionSubject
                            ? Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                                '/Education/ClassRegister/Digital/Lectureship', $BasicRoute)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                                '/Education/ClassRegister/Digital/Lectureship', $BasicRoute)
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
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLessonWeek(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Kontrolle');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
        ));
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        $hasDivisionTeacherRight = ($tblPerson = Account::useService()->getPersonByLogin())
            && (($tblDivision && Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson))
                || ($tblGroup && ($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson))
            );
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
        // Schulleitung soll auch die Klassenbücher für die Klassenlehrer abnehmen dürfen
        if ($hasHeadmasterRight)  {
            $hasDivisionTeacherRight = true;
        }

        if ($tblDivision || $tblGroup) {
            $content = '';
            $layoutRow = Digital::useService()->getHeadLayoutRow(
                $tblDivision ?: null, $tblGroup ?: null, $tblYear
            );
            if ($tblYear) {
                $content = ApiDigital::receiverBlock($this->loadLessonWeekTable($tblDivision ?: null, $tblGroup ?: null, $hasDivisionTeacherRight,
                    $hasHeadmasterRight), 'LessonWeekContent');
            }
            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        $layoutRow,
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/LessonWeek', $BasicRoute)
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
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param bool $hasDivisionTeacherRight
     * @param bool $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return string
     */
    public function loadLessonWeekTable(?TblDivision $tblDivision, ?TblGroup  $tblGroup, bool $hasDivisionTeacherRight, bool $hasHeadmasterRight,
        string $Date = null): string
    {
        $content = '';

        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblDivision->getServiceTblCompany()) {
                $tblCompanyList[] = $tblDivision->getServiceTblCompany();
            } else {
                $tblCompanyList = array();
            }
            $tblSchoolType = $tblDivision->getType();
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
            $tblCompanyList = $tblGroup->getCurrentCompanyList();
            $tblSchoolType = $tblGroup->getCurrentSchoolTypeSingle();
        } else {
            $tblYear = false;
            $tblCompanyList = array();
            $tblSchoolType = false;
        }

        $hasSaturdayLessons = $tblSchoolType && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

        if (!$tblYear) {
            return new Danger('Kein Schuljahr gefunden!', new Exclamation());
        }

        $DivisionId = $tblDivision ? $tblDivision->getId() : null;
        $GroupId = $tblGroup ? $tblGroup->getId() : null;
        $YearId = $tblYear->getId();

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
                $isHoliday = Term::useService()->getIsSchoolWeekHoliday($dateString, $tblYear, $tblCompanyList, $hasSaturdayLessons);

                // Rechte prüfen
                $newDivisionTeacher = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Unchecked() . ' noch nicht bestätigt')
                    . new PullRight(($hasDivisionTeacherRight
                            ? (new Link('Bestätigen', ApiDigital::getEndpoint(), new Check()))->ajaxPipelineOnClick(
                                ApiDigital::pipelineSaveLessonWeekCheck($DivisionId, $GroupId, $YearId, $dateString, 'DivisionTeacher', 'SET',
                                    $hasDivisionTeacherRight, $hasHeadmasterRight))
                            : '')
                        . '|');
                $newHeadmaster = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Unchecked() . ' noch nicht bestätigt')
                    . new PullRight($hasHeadmasterRight
                        ? (new Link('Bestätigen', ApiDigital::getEndpoint(), new Check()))->ajaxPipelineOnClick(
                            ApiDigital::pipelineSaveLessonWeekCheck($DivisionId, $GroupId, $YearId, $dateString, 'Headmaster', 'SET',
                                $hasDivisionTeacherRight, $hasHeadmasterRight))
                        : ''
                    );

                if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivision, $tblGroup, $startDate))) {
                    if ($tblLessonWeek->getDateDivisionTeacher()) {
                        $divisionTeacherText = new Success(
                                new Check() . ' am ' . $tblLessonWeek->getDateDivisionTeacher() . ' von '
                                . (($divisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher()) ? $divisionTeacher->getLastName() : '')
                                . ' bestätigt.'
                            )
                            . new PullRight(
                                ($hasDivisionTeacherRight
                                    ? (new Link('Rückgängig', ApiDigital::getEndpoint(), new Unchecked()))->ajaxPipelineOnClick(
                                        ApiDigital::pipelineSaveLessonWeekCheck($DivisionId, $GroupId, $YearId, $dateString, 'DivisionTeacher', 'UNSET',
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
                                    ApiDigital::pipelineSaveLessonWeekCheck($DivisionId, $GroupId, $YearId, $dateString, 'Headmaster', 'UNSET',
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
                        Digital::useFrontend()->getWeekViewContent($dateString, $tblDivision, $tblGroup, false, true)
                        . new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($displayWeek
                                . (new Link(' schließen', ApiDigital::getEndpoint()))
                                    ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonWeekContent($DivisionId, $GroupId, $hasDivisionTeacherRight,
                                        $hasHeadmasterRight))
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
                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonWeekContent($DivisionId, $GroupId, $hasDivisionTeacherRight,
                                    $hasHeadmasterRight, $dateString))
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
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param DateTime $Date
     *
     * @return Form
     */
    public function formLessonWeekRemark(?TblDivision $tblDivision, ?TblGroup $tblGroup, DateTime $Date): Form
    {
        if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivision, $tblGroup, $Date))) {
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
                            ->ajaxPipelineOnClick(ApiDigital::pipelineEditLessonWeekRemarkSave($tblDivision ? $tblDivision->getId() : null,
                                $tblGroup ? $tblGroup->getId() : null, $Date->format('d.m.Y')))
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendHoliday(
        $DivisionId = null,
        $GroupId = null,
        $DivisionSubjectId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Ferien / Unterrichtsfreie Tage');

        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            if ($GroupId) {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'GroupId' => $GroupId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            } else {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'DivisionId' => $DivisionId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            }
        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
            ));
        }

        if ($tblDivision || $tblGroup || $tblDivisionSubject) {
            $header = Digital::useService()->getHeadLayoutRow(
                $tblDivision ?: null, $tblGroup ?: null, $tblYear, $tblDivisionSubject ?: null
            );

            $tblCompany = false;
            if ($tblDivision) {
                $tblCompany = $tblDivision->getServiceTblCompany();
            } elseif ($tblGroup) {
                $tblCompany = $tblGroup->getCurrentCompanySingle();
            }

            if ($tblYear) {
                $list = array();
                $dataList = array();
                if ($tblCompany && ($tblYearHolidayAllByYearAndCompany = Term::useService()->getYearHolidayAllByYear($tblYear, $tblCompany))) {
                    $list = $tblYearHolidayAllByYearAndCompany;
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
            } else {
                $content = new Warning('Kein Schuljahr gefunden', new Exclamation());
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        $header,
                        $tblDivisionSubject
                            ? Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                                '/Education/ClassRegister/Digital/Holiday', $BasicRoute)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                                '/Education/ClassRegister/Digital/Holiday', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new Holiday() . ' Ferien / Unterrichtsfreie Tage'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }
}