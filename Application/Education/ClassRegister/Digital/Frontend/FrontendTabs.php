<?php
namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateInterval;
use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;

class FrontendTabs extends FrontendSelectDivisionCourse
{
    const WELCOME_VIEW_TIMETABLE = 'Timetable';
    const WELCOME_VIEW_TEACHER_LECTURESHIP = 'TeacherLectureship';
    const WELCOME_VIEW_ALL_DIGITAL = 'AllDigital';

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     */
    public function frontendTeacherControl(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Ok();
        $name = 'Lehreransicht';
        $Route = '/Education/ClassRegister/Digital/TeacherControl';
        $content = '';
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $Filter = ['DivisionCourseId' => $DivisionCourseId];
            // save filter as json
            Consumer::useService()->createAccountSetting('DigitalTeacherViewFilter', json_encode($Filter));

            $global = $this->getGlobal();
            $global->POST['Filter']['DivisionCourseId'] = $DivisionCourseId;
            $global->savePost();

            $content = ApiDigital::receiverModal()
                . ApiAbsence::receiverModal()
                . new Panel(new Filter() . ' Filter', $this->formTeacherViewFilter($tblYear), Panel::PANEL_TYPE_INFO)
                . ApiDigital::receiverBlock($this->loadTeacherViewContent($tblYear->getId(), $Filter), 'TeacherViewContent');
        }

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    public function loadTeacherViewContent($YearId = null, $Filter = null): string
    {
        if ($YearId) {
            if (!($tblYear = Term::useService()->getYearById($YearId))) {
                return new Danger('Schuljahr nicht gefunden', new Exclamation());
            }
            $tblYearList = [$tblYear];
        } else {
            if (!($tblYearList = Term::useService()->getYearByNow())) {
                return new Danger('Schuljahr nicht gefunden', new Exclamation());
            }
        }

        if (!($tblPerson = Account::useService()->getPersonByLogin())) {
            return new Warning('Person zum eingeloggten Benutzerkonto nicht gefunden', new Exclamation());
        }

        $tblSubjectFilter = null;
        if (isset($Filter['SubjectId'])) {
            $tblSubjectFilter = Subject::useService()->getSubjectById($Filter['SubjectId']);
        }

        $tblDivisionCourseFilter = null;
        if (isset($Filter['DivisionCourseId'])) {
            $tblDivisionCourseFilter = DivisionCourse::useService()->getDivisionCourseById($Filter['DivisionCourseId']);
        }

        if ($tblDivisionCourseFilter) {
            $tblLessonContentList = Digital::useService()->getLessonContentAllByTeacherAndDivisionCourse(
                $tblPerson, $tblDivisionCourseFilter, $tblSubjectFilter ?: null);
        } else {
            $tblLessonContentList = [];
            foreach ($tblYearList as $tblYearTemp) {
                if (($tempList = Digital::useService()->getLessonContentAllByTeacherAndYear($tblPerson, $tblYearTemp, $tblSubjectFilter ?: null))) {
                    $tblLessonContentList = array_merge($tblLessonContentList, $tempList);
                }
            }
        }

        // setze Identifier für Ermittlung fehlender Einträge
        $tblLessonContentList = Digital::useService()->getLessonContentListWithIdentifier($tblLessonContentList);

        // ergänzt fehlende Einträge an Hand vom Stundenplan und Vertretungsplan
        Digital::useService()->addMissingLessonContentList($tblLessonContentList, $tblYearList,
            $tblPerson, $tblDivisionCourseFilter ?: null, $tblSubjectFilter ?: null);

        $dataList = [];
        if ($tblLessonContentList) {
            // fehlzeiten

//            $tblTestList = [];
            /** @var TblLessonContent $tblLessonContent */
            foreach ($tblLessonContentList as $tblLessonContent) {
                $isMissing = $tblLessonContent->getId() == 0;

                if (isset($Filter['OnlyMissing']) && !$isMissing) {
                    continue;
                }

                // tests
//                if (!isset($tblTestList[$tblLessonContent->getDate()])) {
//                    $tblTestList[$tblLessonContent->getDate()] = Grade::useService()->getTestListForDigitalByDate(new DateTime($tblLessonContent->getDate()));
//                }

                $tblSubject = $tblLessonContent->getServiceTblSubject();

                $dataList[] = [
                    'Check' => $this->getDisplayMissing($isMissing ? new Unchecked() : new Check(), $isMissing),
                    'Date' => $tblLessonContent->getDate(),
                    'DivisionCourse' => $this->getDisplayMissing(
                        ($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse()) ? $tblDivisionCourse->getName() : '', $isMissing),
                    'Lesson' => $this->getDisplayMissing($tblLessonContent->getLessonDisplay(true), $isMissing),
                    'Subject' => $this->getDisplayMissing($tblLessonContent->getDisplaySubject(true), $isMissing),
                    'Room' => $this->getDisplayMissing($tblLessonContent->getRoom(), $isMissing),
                    'Content' => $this->getDisplayMissing($tblLessonContent->getContent(), $isMissing),
                    'Homework' => $this->getDisplayMissing($tblLessonContent->getHomework(), $isMissing),
//                    $tblDivisionCourseListByStudentsInDivisionCourse = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse);
//                    'Test' => Digital::useFrontend()->getTestColumnContent(
//                        $tblTestList[$tblLessonContent->getDate()], $tblDivisionCourse->getId(), $tblSubject ? $tblSubject->getId() : null, []
//                    ),
//                    'Absence' => '',
                    'Option' => $isMissing
                        ? (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Plus(),
                            array(),
                            'Hinzufügen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal(
                            $tblDivisionCourse->getId(), $tblLessonContent->getDate(), $tblLessonContent->getLesson(), $tblSubject ? $tblSubject->getId() : null
                        ))
                        : (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonContentModal($tblLessonContent->getId()))
                        . (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteLessonContentModal($tblLessonContent->getId()))
                ];
            }
        }

        $columns = array(
            'Check' => ' ',
            'Date' => 'Datum',
            'DivisionCourse' => 'Kurs',
            'Lesson' => new ToolTip('UE', 'Unterrichtseinheit'),
            'Subject' => 'Fach',
            'Room' => 'Raum',
            'Content' => 'Thema',
            'Homework' => 'Hausaufgaben',
//            'Test' => new ToolTip('LÜ', 'Leistungsüberprüfung'),
//            'Absence' => 'Fehlzeiten',
            'Option' => ''
        );

        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'order' => array(
                    array(1, 'desc'),
                    array(2, 'asc'),
                    array(3, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 1),
                    array('type' => 'natural', 'targets' => 2),
                    array('width' => '10px', 'targets' => 0),
                    array('width' => '60px', 'targets' => 1),
                    array('width' => '60px', 'targets' => -1),
                    array('orderable' => false, 'searchable' => false, 'targets' => [0, -1]),
                ),
                'responsive' => false,
                'paging' => false,
//                'info' => false,
//                'searching' => false,
            )
        );
    }

    /**
     * @param TblYear|null $tblYear
     *
     * @return Form
     */
    public function formTeacherViewFilter(?TblYear $tblYear): Form
    {
        if ($tblYear)  {
            $tblYearList = [$tblYear];
            $YearId = $tblYear->getId();
        } else {
            $YearId = null;
            if (!($tblYearList = Term::useService()->getYearByNow())) {
                $tblYearList = [];
            }
        }

        $tblDivisionCourseList = Digital::useService()->getDivisionCourseListForDigital($tblYearList);

        $checkBox = (new CheckBox('Filter[OnlyMissing]', new Bold('Nur fehlende Einträge anzeigen'), 1))
            ->ajaxPipelineOnChange(ApiDigital::pipelineLoadTeacherViewContent($YearId));

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[DivisionCourseId]', 'Kurs', array('{{ Name }}' => $tblDivisionCourseList)))
                        ->ajaxPipelineOnChange(ApiDigital::pipelineLoadTeacherViewContent($YearId))
                    , 6),
                new FormColumn(
                    (new SelectBox('Filter[SubjectId]', 'Fach', array('{{ DisplayName }}' => Subject::useService()->getSubjectAll())))
                        ->ajaxPipelineOnChange(ApiDigital::pipelineLoadTeacherViewContent($YearId))
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        (new Container($checkBox))->setStyle(['margin-top: 7.5px;', 'margin-bottom: 7.5px'])
                    ))))
                ),
            )),
        )));
    }

    private function getDisplayMissing(string $content, bool $isMissing): string
    {
        return $isMissing ? new WarningText($content) : new Success($content);
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     */
    public function frontendStudentList(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new PersonGroup();
        $name = 'Schülerliste';
        $Route = '/Education/ClassRegister/Digital/Student';
        $content = Digital::useService()->getStudentTable($DivisionCourseId, $BasicRoute, $Route);

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
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
     * @return string
     */
    public function frontendDownload(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Download();
        $name = 'Download';
        $Route = '/Education/ClassRegister/Digital/Download';
        $content = '';

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                $text = 'Kursliste';
                $printLink = (new Link((new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png'), 'Kursheft'))->setPictureHeight(),
                    '/Api/Document/Standard/CourseContent/Create', null, array(
                        'DivisionCourseId' => $DivisionCourseId
                    )))->setExternal();
            } else {
                if (($isCoreGroup = $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                    $text = 'Stammgruppenliste';
                } else {
                    $text = 'Klassenliste';
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

            $content = new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!',
                            new Exclamation())
                    ),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), $text . ' Einverständnis&shy;erklärung'))->setPictureHeight(),
                            '/Api/Reporting/Standard/Person/AgreementClassList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWMedical.png'), $text . ' Krankenakte'))->setPictureHeight(),
                            '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $text . ' Schülerliste'))->setPictureHeight(),
                            '/Api/Reporting/Standard/Person/ClassList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $text . ' zeugnis&shy;relevante Fehlzeiten'))->setPictureHeight(),
                            '/Api/Reporting/Standard/Person/ClassRegister/Absence/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence.png'), $text . ' Monatliche Fehlzeiten'))->setPictureHeight(),
                            '/Api/Reporting/Standard/Person/ClassRegister/AbsenceMonthly/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        $printLink
                        , 2),
                ))),
                ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVOSG')
                    ? new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadClassList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadSignList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Klassenliste Fremdsprachen'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadElectiveClassList'
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), 'Individuelle Telefonliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/IndividualClassRegisterDownload', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'Type'    => 'downloadClassPhoneList'
                            ))
                        , 2),
                )), new Title($icon . ' Individual Download'))
                    : null,
                ConsumerGatekeeper::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'KG')
                    ? new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste'))->setPictureHeight(),
                            '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId
                            ))
                        , 2),
                    new LayoutColumn(
                        new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWAgreement.png'), 'Individuelle Unterschriftenliste Querformat'))->setPictureHeight(),
                            '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', null, array(
                                'DivisionCourseId' => $DivisionCourseId,
                                'isLandscape' => 1
                            ))
                        , 2),
                )), new Title($icon . ' Individual Download'))
                    : null,
            ));
        }

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
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
        $Stage = new Stage('Digitales Klassenbuch', 'Inklusion verwalten');

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
            $PersonPanel = new Panel('Person', $tblPerson->getLastFirstNameWithCallNameUnderline(true), Panel::PANEL_TYPE_INFO);
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
     * @return string
     */
    public function frontendLectureship(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Listing();
        $name = 'Unterrichtete Fächer / Lehrer';
        $Route = '/Education/ClassRegister/Digital/Lectureship';
        $content = Digital::useService()->getSubjectsAndLectureshipByDivisionCourse($DivisionCourseId);

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    /**
     * @param null $DivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     */
    public function frontendLessonWeek(
        $DivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Ok();
        $name = 'Klassentagebuch Kontrolle';
        $Route = '/Education/ClassRegister/Digital/LessonWeek';
        $content = '';
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $hasDivisionTeacherRight = (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson))
            );
            $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
            // Schulleitung soll auch die Klassenbücher für die Klassenlehrer abnehmen dürfen
            if ($hasHeadmasterRight) {
                $hasDivisionTeacherRight = true;
            }

            $content = ApiDigital::receiverBlock($this->loadLessonWeekTable($tblDivisionCourse, $hasDivisionTeacherRight, $hasHeadmasterRight), 'LessonWeekContent');
        }

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content);
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
                $newDivisionTeacher = new WarningText(new Unchecked() . ' noch nicht bestätigt')
                    . new PullRight(($hasDivisionTeacherRight
                            ? (new Link('Bestätigen', ApiDigital::getEndpoint(), new Check()))->ajaxPipelineOnClick(
                                ApiDigital::pipelineSaveLessonWeekCheck($DivisionCourseId, $dateString, 'DivisionTeacher', 'SET',
                                    $hasDivisionTeacherRight, $hasHeadmasterRight))
                            : '')
                        . '|');
                $newHeadmaster = new WarningText(new Unchecked() . ' noch nicht bestätigt')
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
     * @return string
     */
    public function frontendHoliday(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new Holiday();
        $name = 'Ferien / Unterrichtsfreie Tage';
        $Route = '/Education/ClassRegister/Digital/Holiday';
        $content = '';
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
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
                        array('type' => 'de_date', 'targets' => array(0, 1)),
                    )
                )
            );
        }

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    /**
     * @param $View
     * @param $Date
     *
     * @return string
     */
    public function loadWelcomeDigitalContent($View = null, $Date = null): string
    {
        // kein digitales Klassenbuch
        if (!Access::useService()->hasAuthorization('/Api/Education/ClassRegister/ApiDigital')) {
            return '';
        }

        $hasRightAllDigital = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Headmaster');

        if ($View == null) {
            $View = Consumer::useService()->getAccountSettingValue('WelcomeDigitalView');
            if (!$View) {
                $View = self::WELCOME_VIEW_TIMETABLE;
            }
        }

        $linkAllDigital = false;
        if ($hasRightAllDigital) {
            $linkAllDigital = (new Link('Alle Klassenbücher', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadWelcomeDigitalContent(self::WELCOME_VIEW_ALL_DIGITAL));
        }
        $linkTeacherLectureship = (new Link('Digitales Klassenbuch: Fachlehrer', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadWelcomeDigitalContent(self::WELCOME_VIEW_TEACHER_LECTURESHIP));
        $linkTimetable = (new Link('Stundenplan', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadWelcomeDigitalContent(self::WELCOME_VIEW_TIMETABLE));

        $spacer = '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';

        return match ($View) {
            self::WELCOME_VIEW_TEACHER_LECTURESHIP => Digital::useService()->getDigitalClassRegisterPanelForTeacher('Digitales Klassenbuch (Ansicht: Fachlehrer)'
                . new PullRight($linkTimetable . ($linkAllDigital ? $spacer . $linkAllDigital : ''))),
            self::WELCOME_VIEW_ALL_DIGITAL => Digital::useService()->getDigitalClassRegisterPanelForTeacher('Digitales Klassenbuch (Ansicht: Alle Klassenbücher)'
                . new PullRight($linkTimetable . $spacer . $linkTeacherLectureship), true),
            self::WELCOME_VIEW_TIMETABLE => Timetable::useService()->getTimetablePanelForTeacher($Date ?: 'today',
                new PullRight($linkTeacherLectureship . ($linkAllDigital ? $spacer . $linkAllDigital : ''))),
            default => '',
        };
    }
}