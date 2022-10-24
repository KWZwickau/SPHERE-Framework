<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiDivisionCourse extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadDivisionCourseContent');
        $Dispatcher->registerMethod('loadSubjectSelectBox');
        $Dispatcher->registerMethod('openCreateDivisionCourseModal');
        $Dispatcher->registerMethod('saveCreateDivisionCourseModal');
        $Dispatcher->registerMethod('openEditDivisionCourseModal');
        $Dispatcher->registerMethod('saveEditDivisionCourseModal');
        $Dispatcher->registerMethod('openDeleteDivisionCourseModal');
        $Dispatcher->registerMethod('saveDeleteDivisionCourseModal');

        $Dispatcher->registerMethod('openLinkDivisionCourseModal');
        $Dispatcher->registerMethod('loadDivisionCourseLinkContent');
        $Dispatcher->registerMethod('addDivisionCourse');
        $Dispatcher->registerMethod('removeDivisionCourse');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
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
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionCourseContent($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionCourseContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadDivisionCourseContent($Filter = null): string
    {
        return DivisionCourse::useFrontend()->loadDivisionCourseTable($Filter);
    }

    /**
     * @param $Error
     * @param $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadSubjectSelectBox($Error, $Data): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SubjectSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSubjectSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'Error' => $Error,
            'Data' => $Data
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Error
     * @param null $Data
     *
     * @return SelectBox|null
     */
    public function loadSubjectSelectBox($Error, $Data = null): ?SelectBox
    {
        return DivisionCourse::useFrontend()->loadSubjectSelectBox($Error, $Data);
    }

    /**
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateDivisionCourseModal($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function openCreateDivisionCourseModal($Filter = null): string
    {
        return $this->getDivisionCourseModal(DivisionCourse::useFrontend()->formDivisionCourse(null, $Filter, true));
    }

    /**
     * @param $form
     * @param string|null $DivisionCourseId
     *
     * @return string
     */
    private function getDivisionCourseModal($form, string $DivisionCourseId = null): string
    {
        if ($DivisionCourseId) {
            $title = new Title(new Edit() . ' Kurs bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Kurs hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineCreateDivisionCourseSave($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateDivisionCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateDivisionCourseModal($Filter = null, array $Data = null): string
    {
        if (($form = DivisionCourse::useService()->checkFormDivisionCourse($Filter, $Data))) {
            // display Errors on form
            return $this->getDivisionCourseModal($form);
        }

        if (DivisionCourse::useService()->createDivisionCourse($Data)) {
            return new Success('Kurs wurde erfolgreich gespeichert.')
                . self::pipelineLoadDivisionCourseContent($Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Kurs konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDivisionCourseModal($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function openEditDivisionCourseModal($DivisionCourseId, $Filter = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getDivisionCourseModal(DivisionCourse::useFrontend()->formDivisionCourse($DivisionCourseId, $Filter, true), $DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineEditDivisionCourseSave($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDivisionCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Filter
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditDivisionCourseModal($DivisionCourseId, $Filter, $Data)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = DivisionCourse::useService()->checkFormDivisionCourse($Filter, $Data, $tblDivisionCourse))) {
            // display Errors on form
            return $this->getDivisionCourseModal($form, $DivisionCourseId);
        }

        if (DivisionCourse::useService()->updateDivisionCourse($tblDivisionCourse, $Data)) {
            return new Success('Der Kurs wurde erfolgreich gespeichert.')
                . self::pipelineLoadDivisionCourseContent($Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Der Kurs konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDivisionCourseModal($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function openDeleteDivisionCourseModal($DivisionCourseId, $Filter = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        $countStudents = 0;
        $countDivisionTeachers = 0;
        $countCustodyList = 0;
        $countRepresentatives = 0;
        if (($students = $tblDivisionCourse->getStudents())) {
            $countStudents = count($students);
        }
        if (($divisionTeachers = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            $countDivisionTeachers = count($divisionTeachers);
        }
        if (($custodyList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY))) {
            $countCustodyList = count($custodyList);
        }
        if (($representatives = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))) {
            $countRepresentatives = count($representatives);
        }

        return new Title(new Remove() . ' Kurs löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Kurs wirklich löschen?',
                                array(
                                    'Schuljahr: ' . new Bold($tblDivisionCourse->getYearName()),
                                    'Typ: ' . $tblDivisionCourse->getTypeName(),
                                    'Name: ' . new Bold($tblDivisionCourse->getName()),
                                    'Schüler: ' . ($countStudents ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countStudents) : '0'),
                                    $tblDivisionCourse->getDivisionTeacherName()  .  ': '
                                        . ($countDivisionTeachers ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countDivisionTeachers) : '0'),
                                    'Elternvertreter: ' . ($countCustodyList ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countCustodyList) : '0'),
                                    'Schülersprecher: ' . ($countRepresentatives ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countRepresentatives) : '0'),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDivisionCourseSave($DivisionCourseId, $Filter))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDivisionCourseSave($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteDivisionCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function saveDeleteDivisionCourseModal($DivisionCourseId, $Filter = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->destroyDivisionCourse($tblDivisionCourse)) {
            return new Success('Der Kurs wurde erfolgreich gelöscht.')
                . self::pipelineLoadDivisionCourseContent($Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Der Kurs konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenLinkDivisionCourseModal($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openLinkDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function openLinkDivisionCourseModal($DivisionCourseId, $Filter = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Link() . ' Kurs besteht aus den folgenden Unter-Kursen')
            . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
            . self::receiverBlock($this->pipelineLoadDivisionCourseLinkContent($DivisionCourseId, $Filter), 'DivisionCourseLinkContent');
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionCourseLinkContent($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseLinkContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionCourseLinkContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function loadDivisionCourseLinkContent($DivisionCourseId, $Filter = null): string
    {
        return DivisionCourse::useFrontend()->loadDivisionCourseLinkContent($DivisionCourseId, $Filter);
    }

    /**
     * @param $DivisionCourseId
     * @param $DivisionCourseAddId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineAddDivisionCourse($DivisionCourseId, $DivisionCourseAddId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseLinkContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addDivisionCourse'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'DivisionCourseAddId' => $DivisionCourseAddId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $DivisionCourseAddId
     * @param $Filter
     *
     * @return string
     */
    public function addDivisionCourse($DivisionCourseId, $DivisionCourseAddId, $Filter)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblDivisionCourseAdd = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseAddId))) {
            return new Danger('Der Unter-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->addSubDivisionCourseToDivisionCourse($tblDivisionCourse, $tblDivisionCourseAdd)) {
            return new Success('Unter-Kurs wurde erfolgreich hinzugefügt.')
               . self::pipelineLoadDivisionCourseLinkContent($DivisionCourseId, $Filter)
               . self::pipelineLoadDivisionCourseContent($Filter);
        } else {
            return new Danger('Der Unter-Kurs konnte nicht hinzugefügt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $DivisionCourseRemoveId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineRemoveDivisionCourse($DivisionCourseId, $DivisionCourseRemoveId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseLinkContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removeDivisionCourse'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'DivisionCourseRemoveId' => $DivisionCourseRemoveId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $DivisionCourseRemoveId
     * @param $Filter
     *
     * @return string
     */
    public function removeDivisionCourse($DivisionCourseId, $DivisionCourseRemoveId, $Filter)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblDivisionCourseRemove = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseRemoveId))) {
            return new Danger('Der Unter-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->removeSubDivisionCourseFromDivisionCourse($tblDivisionCourse, $tblDivisionCourseRemove)) {
            return new Success('Unter-Kurs wurde erfolgreich entfernt.')
                . self::pipelineLoadDivisionCourseLinkContent($DivisionCourseId, $Filter)
                . self::pipelineLoadDivisionCourseContent($Filter);
        } else {
            return new Danger('Der Unter-Kurs konnte nicht entfernt werden.');
        }
    }
}