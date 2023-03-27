<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Absence as AbsenceOld;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiAbsence
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiAbsence extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('openCreateAbsenceModal');
        $Dispatcher->registerMethod('saveCreateAbsenceModal');

        $Dispatcher->registerMethod('openEditAbsenceModal');
        $Dispatcher->registerMethod('saveEditAbsenceModal');

        $Dispatcher->registerMethod('openDeleteAbsenceModal');
        $Dispatcher->registerMethod('saveDeleteAbsenceModal');

        $Dispatcher->registerMethod('loadAbsenceContent');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('loadLesson');
        $Dispatcher->registerMethod('loadType');

        $Dispatcher->registerMethod('generateOrganizerWeekly');
        $Dispatcher->registerMethod('generateOrganizerMonthly');
        $Dispatcher->registerMethod('generateOrganizerForDivision');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionCourseId
     * @param null $Date
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAbsenceModal($PersonId = null, $DivisionCourseId = null, $Date = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionCourseId
     * @param null $Date
     *
     * @return string
     */
    public function openCreateAbsenceModal($PersonId = null, $DivisionCourseId = null, $Date = null): string
    {
        $hasSearch = $PersonId == null && $DivisionCourseId == null;
        return $this->getAbsenceModal(
            Absence::useFrontend()->formAbsence(null, $hasSearch, null, null, $PersonId, $DivisionCourseId, null, null, $Date),
            null,
            $PersonId,
            $hasSearch
        );
    }

    /**
     * @param $form
     * @param null $AbsenceId
     * @param null $PersonId
     * @param bool $hasSearch
     *
     * @return string
     */
    private function getAbsenceModal($form,  $AbsenceId = null, $PersonId = null, bool $hasSearch = false): string
    {
        $tblPerson = false;
        $date = 'now';
        $message = '';
        if ($AbsenceId) {
            if (($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
                $tblPerson = $tblAbsence->getServiceTblPerson();
                $date = $tblAbsence->getFromDate();
                $createDate = $tblAbsence->getEntityCreate();
                if (($creator = $tblAbsence->getDisplayPersonCreator(false))) {
                    $message = new Small(new Muted('erstellt von: ' . $creator . ' am: ' . $createDate->format('d.m.Y H:i:s')));
                }
            }
            $title = new Title(new Edit() . ' Fehlzeit bearbeiten' . new PullRight($message));
        } else {
            $title = new Title(new Plus() . ' Fehlzeit hinzufügen');
            if ($PersonId) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
            }
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        !$hasSearch && $tblPerson ? new LayoutRow(array(
                            new LayoutColumn(new Panel(
                                'Schüler',
                                $tblPerson->getFullName() . '&nbsp;&nbsp;'
                                    . (new Standard('', '/People/Person', new \SPHERE\Common\Frontend\Icon\Repository\Person(),
                                    array('Id' => $tblPerson->getId()), 'zur Person'))->setExternal(),
                                Panel::PANEL_TYPE_INFO
                            ), 6),
                            new LayoutColumn(new Panel(
                                'Kurse',
                                DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson, $date),
                                Panel::PANEL_TYPE_INFO
                            ), 6)
                        )) : null,
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    )))
            );
    }

    /**
     * @param null $PersonId
     * @param null $DivisionCourseId
     * @param null $hasSearch
     *
     * @return Pipeline
     */
    public static function pipelineCreateAbsenceSave($PersonId = null, $DivisionCourseId = null, $hasSearch = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionCourseId' => $DivisionCourseId,
            'hasSearch' => $hasSearch,
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     * @param $Search
     * @param null $PersonId
     * @param null $DivisionCourseId
     * @param null $hasSearch
     *
     * @return string
     */
    public function saveCreateAbsenceModal($Data, $Search, $PersonId = null, $DivisionCourseId = null, $hasSearch = null): string
    {
        $hasSearch = $hasSearch == 'true';
        if (($form = Absence::useService()->checkFormAbsence($Data, $Search, null, $PersonId, $DivisionCourseId, $hasSearch))) {
            // display Errors on form
            return $this->getAbsenceModal($form, null, $PersonId, $hasSearch);
        }

        $date = new DateTime($Data['FromDate'] ?? 'now');

        $tblPerson = null;
        if (!$hasSearch) {
            if ($PersonId) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
            }
            if (!$tblPerson) {
                $tblPerson = null;
            }
        }

        if (Absence::useService()->createAbsence($Data, $tblPerson)) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . $this->reloadPipelines($date, $DivisionCourseId, $tblPerson ? $tblPerson->getId() : null);
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param DateTime $date
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return string
     */
    private function reloadPipelines(DateTime $date, $DivisionCourseId, $PersonId): string
    {
        $reloadDigital = '';
        if ($DivisionCourseId
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        ) {
            // todo kursbuch und Klassentagebuch neu laden
//            $reloadDigital = ApiDigital::pipelineLoadLessonContentContent(
//                $digitalDivisionId,
//                $digitalGroupId,
//                $date->format('d.m.Y'),
//                ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day'
//            );
//            if ($digitalDivisionSubjectId
//                && ($tblDivisionSubject = Division::useService()->getDivisionSubjectById($digitalDivisionSubjectId))
//                && $tblDivisionSubject->getTblDivision()
//                && $tblDivisionSubject->getServiceTblSubject()
//                && $tblDivisionSubject->getTblSubjectGroup()
//            ) {
//                $reloadDigital .= ApiDigital::pipelineLoadCourseContentContent($tblDivisionSubject->getTblDivision(), $tblDivisionSubject->getServiceTblSubject(),
//                        $tblDivisionSubject->getTblSubjectGroup())
//                    . ApiDigital::pipelineLoadCourseMissingStudentContent($tblDivisionSubject->getId());
//            }
        }

        return self::pipelineChangeWeek($date->format('W'), $date->format('Y'))
            // Kalenderansicht der Klasse
            . (Consumer::useService()->getAccountSettingValue('AbsenceView') == 'Month'
                ? ($DivisionCourseId ? self::pipelineChangeMonth($DivisionCourseId, $date->format('m'), $date->format('Y')) : '')
                : ($DivisionCourseId ? self::pipelineChangeWeekForDivision($DivisionCourseId, $date->format('W'), $date->format('Y')) : '')
            )
            . self::pipelineLoadAbsenceContent($PersonId, $DivisionCourseId)
            // Klassenbuch neu laden
            . $reloadDigital
            . self::pipelineClose();
    }

    /**
     * @param $AbsenceId
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditAbsenceModal($AbsenceId, $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId,
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     * @param $DivisionCourseId
     *
     * @return Danger|string
     */
    public function openEditAbsenceModal($AbsenceId, $DivisionCourseId)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        $tblPerson = $tblAbsence->getServiceTblPerson();

        return $this->getAbsenceModal(
            Absence::useFrontend()->formAbsence($AbsenceId, false, '', null, $tblPerson ? $tblPerson->getId() : null, $DivisionCourseId),
            $AbsenceId
        );
    }

    /**
     * @param $AbsenceId
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineEditAbsenceSave($AbsenceId, $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId,
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditAbsenceModal($AbsenceId, $DivisionCourseId, $Data)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        if (($form = Absence::useService()->checkFormAbsence($Data, '', $tblAbsence))) {
            // display Errors on form
            return $this->getAbsenceModal($form, $AbsenceId);
        }

        $date = new DateTime($Data['FromDate'] ?? 'now');
        $tblPerson = $tblAbsence->getServiceTblPerson();

        if (Absence::useService()->updateAbsenceService($tblAbsence, $Data)) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . $this->reloadPipelines($date, $DivisionCourseId, $tblPerson ? $tblPerson->getId() : null);
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $AbsenceId
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteAbsenceModal($AbsenceId, $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId,
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function openDeleteAbsenceModal($AbsenceId, $DivisionCourseId): string
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        $tblPerson = $tblAbsence->getServiceTblPerson();

        return new Title(new Remove() . ' Fehlzeit löschen')
            . new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Panel(
                            'Schüler',
                            $tblPerson ? $tblPerson->getFullName() : '',
                            Panel::PANEL_TYPE_INFO
                        ), 6),
                        new LayoutColumn(new Panel(
                            'Kurse',
                            $tblPerson ? DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson, $tblAbsence->getFromDate()) : '',
                            Panel::PANEL_TYPE_INFO
                        ), 6)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Fehlzeit wirklich löschen?',
                                array(
                                    $tblAbsence->getDateSpan(),
                                    $tblAbsence->getStatusDisplayName()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteAbsenceSave($AbsenceId, $DivisionCourseId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                ))
            );
    }

    /**
     * @param $AbsenceId
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteAbsenceSave($AbsenceId, $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId,
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     * @param $DivisionCourseId
     *
     * @return Danger|string
     */
    public function saveDeleteAbsenceModal($AbsenceId, $DivisionCourseId)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        $date = new DateTime($tblAbsence->getFromDate());
        $tblPerson = $tblAbsence->getServiceTblPerson();

        if (Absence::useService()->destroyAbsence($tblAbsence)) {
            return new Success('Die Fehlzeit wurde erfolgreich gelöscht.')
                . $this->reloadPipelines($date, $DivisionCourseId, $tblPerson ? $tblPerson->getId() : null);
        } else {
            return new Danger('Die Fehlzeit konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param null $PersonId
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadAbsenceContent($PersonId = null, $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AbsenceContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAbsenceContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionCourseId
     *
     * @return string
     */
    public function loadAbsenceContent($PersonId = null, $DivisionCourseId = null)
    {
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if (!($tblDivisionCourse && $tblPerson)) {
            return new Danger('Kurs oder Person wurde nicht gefunden', new Exclamation());
        }

        return Absence::useFrontend()->loadAbsenceTable($tblPerson, $tblDivisionCourse);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSearchPerson(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Search
     *
     * @return string
     */
    public function searchPerson($Search = null): string
    {
        return Absence::useFrontend()->loadPersonSearch(trim($Search));
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadLesson(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'loadLesson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLesson',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadLesson(): string
    {
        return Absence::useFrontend()->loadLesson(isset($_POST['Data']['IsFullDay']));
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadType(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'loadType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadType',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadType(): string
    {
        return Absence::useFrontend()->loadType($_POST['Data']['PersonId'] ?? null);
    }

    /**
     * @param $WeekNumber
     * @param $Year
     *
     * @return Pipeline
     */
    public static function pipelineChangeWeek($WeekNumber, $Year){
        $Pipeline = new Pipeline(false);

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarWeekContent'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'generateOrganizerWeekly',
            'WeekNumber' => $WeekNumber,
            'Year' => $Year
        ));

        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public static function generateOrganizerWeekly(string $WeekNumber = '', string $Year = ''): string
    {
        return Absence::useFrontend()->LoadOrganizerWeekly($WeekNumber, $Year);
    }

    /**
     * @param $DivisionId
     * @param $Month
     * @param $Year
     *
     * @return Pipeline
     */
    public static function pipelineChangeMonth($DivisionId, $Month, $Year)
    {
        $Pipeline = new Pipeline(false);

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarContent'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'generateOrganizerForDivision',
            'DivisionId' => $DivisionId,
            'IsWeek' => 'false',
            'Month' => $Month,
            'Year' => $Year
        ));

        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param $DivisionId
     * @param $WeekNumber
     * @param $Year
     *
     * @return Pipeline
     */
    public static function pipelineChangeWeekForDivision($DivisionId, $WeekNumber, $Year){
        $Pipeline = new Pipeline(false);

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarContent'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'generateOrganizerForDivision',
            'DivisionId' => $DivisionId,
            'IsWeek' => 'true',
            'Year' => $Year,
            'WeekNumber' => $WeekNumber
        ));

        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param $DivisionId
     * @param string $IsWeek
     * @param string $Year
     * @param string $WeekNumber
     * @param string $Month
     *
     * @return string
     */
    public static function generateOrganizerForDivision($DivisionId, $IsWeek, $Year = '', $WeekNumber = '', $Month = '')
    {
        if ($IsWeek == 'true') {
            // View speichern
            Consumer::useService()->createAccountSetting('AbsenceView', 'Week');

            return self::generateOrganizerForDivisionWeekly($DivisionId, $WeekNumber, $Year);
        } else {
            // View speichern
            Consumer::useService()->createAccountSetting('AbsenceView', 'Month');

            return self::generateOrganizerMonthly($DivisionId, $Month, $Year);
        }
    }

    /**
     * @param string $DivisionId
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public static function generateOrganizerForDivisionWeekly($DivisionId, $WeekNumber = '', $Year = '')
    {
        // Definition
        $currentDate = new DateTime('now');

        if ($WeekNumber == '') {
            $WeekNumber = (int)(new DateTime('now'))->format('W');
        } else {
            $WeekNumber = (int) $WeekNumber;
        }

        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        } else {
            $Year = (int) $Year;
        }

        $headerList = array();
        $bodyList = array();

        $organizerBaseData = Absence::useFrontend()->convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthNameShort'];

        // Kalenderwoche ermitteln
        $WeekNext = $WeekNumber + 1;
        $WeekBefore = $WeekNumber - 1;
        $YearNext = $Year;
        $YearBefore = $Year;
        $lastWeek = date('W', strtotime("31.12." . $Year));
        $countWeek = ($lastWeek == 1) ? 52 : $lastWeek;
        if ($WeekNumber == $countWeek) {
            $WeekNext = 1;
            $YearNext = $Year + 1;
        }
        if ($WeekNumber == 1) {
            $WeekBefore = $countWeek;
            $YearBefore = $Year - 1;
        }

        // Start-/Endtag der Woche ermitteln
        $Week = $WeekNumber;
        if ($WeekNumber < 10) {
            $Week = '0' . $WeekNumber;
        }
        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
        $endDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}-7")));

//        $Month = $startDate->format('m');

        $dataList = array();
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblAbsenceList = AbsenceOld::useService()->getAbsenceAllBetweenByDivision($startDate, $endDate, $tblDivision))
        ) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPersonItem = $tblAbsence->getServiceTblPerson())
                    && ($tblDivisionItem = $tblAbsence->getServiceTblDivision())
                ) {
                    $fromDate = new DateTime($tblAbsence->getFromDate());
                    if ($tblAbsence->getToDate()) {
                        $toDate = new DateTime($tblAbsence->getToDate());
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $date->format('d.m.Y'), false);
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), false);
                        }
                    } else {
                        self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), false);
                    }
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '56px';
        $minHeightBody = '38px';
        $padding = '3px';

        $headerList['Person'] = (new TableColumn(new Center(new Bold(new PersonGroup() . 'Schüler'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Kalender-Inhalt erzeugen
        if ($tblDivision
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            if (!($tblCompany = $tblDivision->getServiceTblCompany())) {
                $tblCompany = null;
            }

            $hasSaturdayLessons = ($tblSchoolType = $tblDivision->getType()) && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $bodyList[$tblPerson->getId()]['Person'] = (new TableColumn(new Center(new Bold(
                    new ToolTip(
                        (new Link($tblPerson->getLastFirstName(), self::getEndpoint()))
                            ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(),
                                $tblDivision->getId()))
                        , 'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' hinzufügen.'
                    )
                ))))
                    ->setBackgroundColor($backgroundColor)
                    ->setVerticalAlign('middle')
                    ->setMinHeight($minHeightBody)
                    ->setPadding($padding);
                $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));

                if ($startDate && $endDate) {
                    while ($startDate <= $endDate) {
                        $DayAtWeek = $startDate->format('w');
                        $Day = (int)$startDate->format('d');
                        $Month = (int)$startDate->format('m');

                        if ($hasSaturdayLessons) {
                            $isWeekend = $DayAtWeek == 0;
                        } else {
                            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                        }
                        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, $tblCompany);

                        $fetchedDateString = $startDate->format('d.m.Y');

                        if (!isset($headerList['Day' . $Day])) {
                            $columnHeader = (new TableColumn(new Center(
                                $DayName[$DayAtWeek] . new Container($Day) . new Container($MonthName[$Month])
                            )))
                                ->setMinHeight($minHeightHeader)
                                ->setPadding($padding);

                            if ((int)$currentDate->format('d') == $Day && (int)$currentDate->format('m') == $Month && $currentDate->format('Y') == $Year) {
                                $columnHeader
                                    ->setColor('darkorange');
                            }
                            if ($isWeekend || $isHoliday) {
                                $columnHeader->setBackgroundColor('lightgray')
                                    ->setOpacity(0.5);
                            } else {
                                $columnHeader->setBackgroundColor($backgroundColor);
                            }

                            $headerList['Day' . $Day] = $columnHeader;
                        }

                        if ($isWeekend || $isHoliday) {
                            $columnBody = (new TableColumn(new Center($isWeekend ? new Muted(new Small('w')) : new Muted(new Small('f')))))
                                ->setBackgroundColor('lightgrey')
                                ->setVerticalAlign('middle')
                                ->setOpacity(0.5)
                                ->setPadding($padding);
                        } elseif (isset($dataList[$tblPerson->getId()][$fetchedDateString])) {
                            $columnBody = (new TableColumn(new Center(
                                $dataList[$tblPerson->getId()][$fetchedDateString]['Content']
                            )))
                                ->setBackgroundColor($dataList[$tblPerson->getId()][$fetchedDateString]['BackgroundColor'])
                                ->setPadding($padding);
                        } else {
                            $columnBody = (new TableColumn((new Link(
                                '<div style="height: 28px"><span style="visibility: hidden">'.new Plus().'</span></div>',
                                self::getEndpoint(),
                                null,
                                array(),
                                'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' für den '
                                . $fetchedDateString . ' hinzufügen.'))
                                ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivision->getId(), $fetchedDateString))))
                                ->setPadding('0');
                        }


                        $bodyList[$tblPerson->getId()]['Day' . $Day] = $columnBody
                            ->setMinHeight($minHeightBody)
                            ->setVerticalAlign('middle')
                            ->setPadding($padding);

                        $startDate->modify('+1 day');
                    }
                }
            }
        }

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

//        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));

        // Inhalt zusammenbasteln
        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), self::getEndpoint(), null, array(), 'KW' . $WeekBefore))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeekForDivision($DivisionId, $WeekBefore, $YearBefore))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new ToolTip(new Center(new Bold('KW' . $WeekNumber. ' ')), $Year)
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), self::getEndpoint(), null, array(), 'KW' . $WeekNext))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeekForDivision($DivisionId, $WeekNext, $YearNext))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    ''
                                    , 3)
                            )))
                        )
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $table
                    )
                )
            ))
        );

        return new Panel(
            new Calendar() . ' Kalender'
            . new PullRight(
                (new Link('Monatsansicht', self::getEndpoint(), null, array(), false, null, Link::TYPE_WHITE_LINK))
                    ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeMonth($DivisionId, '', ''))
            ),
            $Content,
            Panel::PANEL_TYPE_PRIMARY
        );
    }

    /**
     * @param $DivisionId
     * @param int $Month
     * @param int $Year
     *
     * @return string
     */
    public static function generateOrganizerMonthly($DivisionId, $Month, $Year)
    {
        // Definitionen
        $currentDate = new DateTime('now');

        if ($Month == '') {
            $Month = (int)$currentDate->format('m');
        } else {
            $Month = (int)$Month;
        }
        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        } else {
            $Year = (int)$Year;
        }

        $headerListStatic = array();
        $bodyListStatic = array();
        $headerList = array();
        $bodyList = array();

        $organizerBaseData = Absence::useFrontend()->convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthName'];

        $MonthNext = (int)$Month + 1;
        $MonthBefore = (int)$Month - 1;
        $YearNext = (int)$Year;
        $YearBefore = (int)$Year;
        // falls Dezember -> Jahreswechsel erzeugen für Folgemonat
        if ($Month == '12'){
            $MonthNext = '1';
            $YearNext = (int)$Year + 1;
        }
        // falls Januar -> Jahreswechsel erzeugen für vorherigen Monat
        if ($Month == '1'){
            $MonthBefore = '12';
            $YearBefore = (int)$Year - 1;
        }

        // Tagesanzahl im aktuellen Monat ermitteln
        $DayCounter = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);

        $startDateSchoolYear = new DateTime('01.' . $Month . '.' . $Year);
        $endDateSchoolYear = new DateTime($DayCounter . '.' . $Month . '.' . $Year);

        $dataList = array();
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblAbsenceList = AbsenceOld::useService()->getAbsenceAllBetweenByDivision($startDateSchoolYear, $endDateSchoolYear, $tblDivision))
        ) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPersonItem = $tblAbsence->getServiceTblPerson())
                    && ($tblDivisionItem = $tblAbsence->getServiceTblDivision())
                ) {
                    $fromDate = new DateTime($tblAbsence->getFromDate());
                    if ($tblAbsence->getToDate()) {
                        $toDate = new DateTime($tblAbsence->getToDate());
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $date->format('d.m.Y'));
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate());
                        }
                    } else {
                        self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate());
                    }
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '44px';
        $minHeightBody = '30px';
        $padding = '3px';

        $hasMonthBefore = true;
        $hasMonthNext = true;

        $headerListStatic['Person'] = (new TableColumn(new Center(new Bold(new PersonGroup() . 'Schüler'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Einträge für alle ausgewählten Personen anzeigen
        if ($tblDivision
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            if (!($tblCompany = $tblDivision->getServiceTblCompany())) {
                $tblCompany = null;
            }

            $hasSaturdayLessons = ($tblSchoolType = $tblDivision->getType()) && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

            // Begrenzung auf den Zeitraum des aktuellen Schuljahres
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            /** @var DateTime $startDateSchoolYear */
            if ($startDateSchoolYear && $endDateSchoolYear) {
                $startDateSchoolYear = new DateTime('01.' . $startDateSchoolYear->format('m') . '.' . $startDateSchoolYear->format('Y'));
                $startDateMonth = new DateTime('01.' . ($Month <= 9 ? '0'.$Month : $Month) . '.' . $Year);
                if ($startDateMonth <= $startDateSchoolYear) {
                    $hasMonthBefore = false;
                }

                $endDateSchoolYear = new DateTime('01.' . $endDateSchoolYear->format('m') . '.' . $endDateSchoolYear->format('Y'));
                if ($startDateMonth >= $endDateSchoolYear) {
                    $hasMonthNext = false;
                }
            }

            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson){
                $bodyListStatic[$tblPerson->getId()]['Person'] = (new TableColumn(new Center(new Bold(
                    new ToolTip(
                        (new Link($tblPerson->getLastFirstName(), self::getEndpoint()))
                            ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivision->getId()))
                                , 'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' hinzufügen.'
                    )
                ))))
                    ->setBackgroundColor($backgroundColor)
                    ->setVerticalAlign('middle')
                    ->setMinHeight($minHeightBody)
                    ->setPadding($padding);

                if ($DayCounter) {
                    $Day = 1;
                    while($Day <= $DayCounter){
                        $fetchedDate = new DateTime($Day . '.' . ($Month <= 9 ? '0'.$Month : $Month) . '.' . $Year);
                        $fetchedDateString = $fetchedDate->format('d.m.Y');
                        $DayAtWeek = (new DateTime(($Day < 10 ? '0'.$Day : $Day).'.'.$Month.'.'.$Year))->format('w');

                        if ($hasSaturdayLessons) {
                            $isWeekend = $DayAtWeek == 0;
                        } else {
                            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                        }
                        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $fetchedDate, $tblCompany);

                        $isCurrentDate = false;
                        if (!isset($headerList['Day' . $Day])) {
                            if (($isCurrentDate = ((int)$currentDate->format('d') == $Day
                                && (int)$currentDate->format('m') == $Month
                                && $currentDate->format('Y') == $Year))
                            ) {
                                // scrollen zum aktuellen Tag
                                $content = '<span id="OrganizerDay" style="color: darkorange;">'
                                    . new Center ($DayName[$DayAtWeek] . new Container($Day))
                                    . '</span>';
                            } else {
                                $content = new Center ($DayName[$DayAtWeek] . new Container($Day));
                            }

                            $columnHeader = (new TableColumn(new Center(
                                $content
                            )))
                                ->setMinHeight($minHeightHeader)
                                ->setPadding($padding);

                            if ($isCurrentDate) {
                                $columnHeader
                                    ->setColor('darkorange');
                            }
                            if ($isWeekend || $isHoliday) {
                                $columnHeader->setBackgroundColor('lightgray')
                                    ->setOpacity(0.5);
                            } else {
                                $columnHeader->setBackgroundColor($backgroundColor);
                            }

                            $headerList['Day' . $Day] = $columnHeader;
                        }

                        if ($isWeekend || $isHoliday) {
                            $columnBody = (new TableColumn(new Center($isWeekend ? new Muted(new Small('w')) : new Muted(new Small('f')))))
                                ->setBackgroundColor('lightgrey')
                                ->setVerticalAlign('middle')
                                ->setOpacity(0.5)
                                ->setPadding($padding);
                        } elseif (isset($dataList[$tblPerson->getId()][$fetchedDateString])) {
                            $columnBody = (new TableColumn(new Center(
                                $dataList[$tblPerson->getId()][$fetchedDateString]['Content']
                            )))
                                ->setBackgroundColor($dataList[$tblPerson->getId()][$fetchedDateString]['BackgroundColor'])
                                ->setPadding($padding);
                        } else {
                            $columnBody = (new TableColumn((new Link(
                                '<div style="height: 28px"><span style="visibility: hidden">'.new Plus().'</span></div>',
                                self::getEndpoint(),
                                null,
                                array(),
                                'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' für den '
                                    . $fetchedDateString . ' hinzufügen.'))
                                ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivision->getId(), $fetchedDateString))))
                            ->setPadding('0');
                        }

                        $bodyList[$tblPerson->getId()]['Day' . $Day] = $columnBody
                            ->setMinHeight($minHeightBody)
                            ->setVerticalAlign('middle');

                        $Day++;
                    }
                }
            }
        }

        // table Static
        $tableHeadStatic = new TableHead(new TableRow($headerListStatic));
        $rowsStatic = array();
        foreach ($bodyListStatic as $columnListStatic) {
            $rowsStatic[] = new TableRow($columnListStatic);
        }
        $tableBodyStatic = new TableBody($rowsStatic);
        $tableStatic = new Table($tableHeadStatic, $tableBodyStatic, null, false, null, 'TableCustom');

        // table float
        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('&nbsp;', 3),
                            new LayoutColumn(
                                $hasMonthBefore
                                    ? new Center(
                                        (new Link(new ChevronLeft(), self::getEndpoint(), null, array(), $MonthName[$MonthBefore] . ' ' . $YearBefore))
                                            ->ajaxPipelineOnClick(self::pipelineChangeMonth($DivisionId, $MonthBefore, $YearBefore))
                                        )
                                    : ''
                                , 1),
                            new LayoutColumn(
                                new Center(new Bold($MonthName[$Month] . ' ' . $Year))
                                , 4),
                            new LayoutColumn(
                                $hasMonthNext
                                    ? new Center(
                                            (new Link(new ChevronRight(), self::getEndpoint(), null, array(), $MonthName[$MonthNext].' '.$YearNext))
                                                ->ajaxPipelineOnClick(self::pipelineChangeMonth($DivisionId, $MonthNext, $YearNext))
                                        )
                                    : ''
                                , 1),
                            new LayoutColumn(
                                '&nbsp;'
//                                    new PullRight((new Link(' Download', self::getEndpoint(), new Download(), array(), 'Download der Daten vorbereiten'))
//                                        ->ajaxPipelineOnClick(self::pipelineOpenDownloadEdit($DivisionId))
//                                    )
                                , 3)
                        ))))
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        '<div style="float: left;">'
                        . $tableStatic
                        .'</div>'
                        . '<div id="OrganizerTable" style="overflow-x: auto;">'
                        . $table
                        . '</div>'
                        . (($Month == (int)$currentDate->format('m') && $Year == (int)$currentDate->format('Y'))
                            ? '<script>
                                tableSelector = "div#OrganizerTable";
                                $(tableSelector).scrollLeft( $("span#OrganizerDay").offset().left - ( $(tableSelector).offset().left + ( $(tableSelector).width() / 2 ) ) )
                            </script>'
                            : ''
                        )
                    )
                )
            ))
        );

        return new Panel(
            new Calendar() . ' Kalender'
            . new PullRight(
                (new Link('Wochenansicht', self::getEndpoint(), null, array(), false, null, Link::TYPE_WHITE_LINK))
                    ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeekForDivision($DivisionId, '', ''))
            ),
            $Content,
            Panel::PANEL_TYPE_PRIMARY
        );
    }

    /**
     * @param $dataList
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param $date
     * @param bool $hasToolTip
     */
    private static function setAbsenceMonthContent(
        &$dataList,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        $date,
        $hasToolTip = true
    ) {
        $lesson = $tblAbsence->getLessonStringByAbsence();
        $type = $tblAbsence->getTypeDisplayShortName();
        $remark = $tblAbsence->getRemark();

        $isWhiteLink = false;

        if ($tblAbsence->getIsOnlineAbsence()) {
            $backgroundColor = 'orange';
            $isWhiteLink = true;
        } elseif (($tblAbsenceType = $tblAbsence->getType())) {
            if ($tblAbsenceType == TblAbsence::VALUE_TYPE_THEORY) {
                $backgroundColor = '#E0F0FF';
            }
            if ($tblAbsenceType == TblAbsence::VALUE_TYPE_PRACTICE) {
                $backgroundColor = '#337ab7';
                $isWhiteLink = true;
            }
        } else {
            if ($tblAbsence->getIsCertificateRelevant()) {
                $backgroundColor = '#E0F0FF';
            } else {
                $backgroundColor = '#FFFFFF';
            }
        }

        if ($hasToolTip) {
            $toolTip = ($lesson ? $lesson . ' / ': '') . ($type ? $type . ' / ': '') . $tblAbsence->getStatusDisplayShortName()
                . (($tblPersonStaff = $tblAbsence->getDisplayStaffToolTip()) ? ' - ' . $tblPersonStaff : '')
                . ($remark ? ' - ' . $remark : '');
            $name = $tblAbsence->getStatusDisplayShortName();
        } else {
            $toolTip = $remark ? $remark : false;
            $name = $tblAbsence->getStatusDisplayShortName()
                .($lesson ? ' - ' . $lesson : '') . ($type ? ' - ' . $type : '')
                . (($tblPersonStaff = $tblAbsence->getDisplayStaff()) ? ' - ' . $tblPersonStaff : '');
        }

        $dataList[$tblPerson->getId()][$date]['Content'] = (new Link(
            $name,
            ApiAbsence::getEndpoint(),
            null,
            array(),
            $toolTip,
            null,
            $isWhiteLink
                ? AbstractLink::TYPE_WHITE_LINK
                : ($tblAbsence->getIsCertificateRelevant() ? AbstractLink::TYPE_LINK : AbstractLink::TYPE_MUTED_LINK)
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));

        $dataList[$tblPerson->getId()][$date]['BackgroundColor'] = $backgroundColor;
    }
}