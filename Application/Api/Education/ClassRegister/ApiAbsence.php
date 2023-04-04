<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
            // Kursheft
            if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE
                || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE
            ) {
                $reloadDigital = ApiDigital::pipelineLoadCourseContentContent($tblDivisionCourse->getId(), ($tblSubject = $tblDivisionCourse->getServiceTblSubject()) ? $tblSubject->getId() : null)
                    . ApiDigital::pipelineLoadCourseMissingStudentContent($tblDivisionCourse->getId());
            // Klassentagebuch
            } else {
                $reloadDigital = ApiDigital::pipelineLoadLessonContentContent(
                    $tblDivisionCourse->getId(),
                    $date->format('d.m.Y'),
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day'
                );
            }
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
    public static function pipelineChangeWeek($WeekNumber, $Year): Pipeline
    {
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
    public static function pipelineChangeMonth($DivisionId, $Month, $Year): Pipeline
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
    public static function pipelineChangeWeekForDivision($DivisionId, $WeekNumber, $Year): Pipeline
    {
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
    public static function generateOrganizerForDivision($DivisionId, string $IsWeek, string $Year = '', string $WeekNumber = '', string $Month = ''): string
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
     * @param $DivisionId
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public static function generateOrganizerForDivisionWeekly($DivisionId, string $WeekNumber = '', string $Year = ''): string
    {
        return Absence::useFrontend()->generateOrganizerForDivisionWeekly($DivisionId, $WeekNumber, $Year);
    }

    /**
     * @param $DivisionId
     * @param $Month
     * @param $Year
     *
     * @return string
     */
    public static function generateOrganizerMonthly($DivisionId, $Month, $Year): string
    {
        return Absence::useFrontend()->generateOrganizerMonthly($DivisionId, $Month, $Year);
    }
}