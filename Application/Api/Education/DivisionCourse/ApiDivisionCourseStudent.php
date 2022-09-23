<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;

class ApiDivisionCourseStudent extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadDivisionCourseStudentContent');
        $Dispatcher->registerMethod('editDivisionCourseStudentContent');

        $Dispatcher->registerMethod('loadRemoveStudentContent');
        $Dispatcher->registerMethod('loadAddStudentContent');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('selectDivisionCourse');
        $Dispatcher->registerMethod('searchProspect');
        $Dispatcher->registerMethod('addStudent');
        $Dispatcher->registerMethod('removeStudent');

        $Dispatcher->registerMethod('openChangeDivisionCourseModal');
        $Dispatcher->registerMethod('saveChangeDivisionCourseModal');

//        $Dispatcher->registerMethod('openEditStudentEducationModal');
        $Dispatcher->registerMethod('saveEditStudentEducation');

        $Dispatcher->registerMethod('openCreateStudentEducationModal');
        $Dispatcher->registerMethod('saveCreateStudentEducationModal');

        return $Dispatcher->callMethod($Method);
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
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
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
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionCourseStudentContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionCourseStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDivisionCourseStudentContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadDivisionCourseStudentContent($DivisionCourseId);
    }

    /**
     * @param $StudentEducationId
     * @param $PersonId
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineEditDivisionCourseStudentContent($StudentEducationId, $PersonId, $DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editDivisionCourseStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'StudentEducationId' => $StudentEducationId,
            'PersonId' => $PersonId,
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $StudentEducationId
     * @param $PersonId
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function editDivisionCourseStudentContent($StudentEducationId, $PersonId, $DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->editDivisionCourseStudentContent($StudentEducationId, $PersonId, $DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRemoveStudentContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RemoveStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRemoveStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadRemoveStudentContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadRemoveStudentContent($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $AddStudentVariante
     * @param $SelectedDivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadAddStudentContent($DivisionCourseId, $AddStudentVariante, $SelectedDivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAddStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'AddStudentVariante' => $AddStudentVariante,
            'SelectedDivisionCourseId' => $SelectedDivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $AddStudentVariante
     * @param $SelectedDivisionCourseId
     *
     * @return string
     */
    public function loadAddStudentContent($DivisionCourseId, $AddStudentVariante, $SelectedDivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadAddStudentContent($DivisionCourseId, $AddStudentVariante, $SelectedDivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSearchPerson($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function searchPerson($DivisionCourseId = null, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadPersonSearch($DivisionCourseId, isset($Data['Search']) ? trim($Data['Search']) : '');
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSelectDivisionCourse($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'selectDivisionCourse',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function selectDivisionCourse($DivisionCourseId = null, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadSelectDivisionCourse($DivisionCourseId, $Data['DivisionCourseId'] ?? null);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSearchProspect($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchProspect',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function searchProspect($DivisionCourseId = null, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadProspectSearch($DivisionCourseId, isset($Data['Search']) ? trim($Data['Search']) : '');
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $AddStudentVariante
     * @param $SelectedDivisionCourseId
     * @return Pipeline
     */
    public static function pipelineAddStudent($DivisionCourseId, $PersonId, $AddStudentVariante, $SelectedDivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addStudent'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'AddStudentVariante' => $AddStudentVariante,
            'SelectedDivisionCourseId' => $SelectedDivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $AddStudentVariante
     * @param $SelectedDivisionCourseId
     *
     * @return string
     */
    public function addStudent($DivisionCourseId, $PersonId, $AddStudentVariante, $SelectedDivisionCourseId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->addStudentToDivisionCourse($tblDivisionCourse, $tblPerson)) {
            return new Success('Schüler wurde erfolgreich hinzugefügt.')
                . self::pipelineLoadAddStudentContent($DivisionCourseId, $AddStudentVariante, $SelectedDivisionCourseId)
                . self::pipelineLoadRemoveStudentContent($DivisionCourseId);
        } else {
            return new Danger('Schüler konnte nicht hinzugefügt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineRemoveStudent($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RemoveStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removeStudent'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return string
     */
    public function removeStudent($DivisionCourseId, $PersonId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->removeStudentFromDivisionCourse($tblDivisionCourse, $tblPerson)) {
            return new Success('Schüler wurde erfolgreich entfernt.')
                . self::pipelineLoadRemoveStudentContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Schüler konnte nicht entfernt werden.');
        }
    }

    /**
     * @param $form
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getChangeDivisionCourseModal($form, TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson): string
    {
        return new Title(new Transfer() . ' ' . $tblDivisionCourse->getTypeName() . 'nwechsel im Schuljahr')
            . new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new LayoutColumn(
                        new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Well($form)
                    )
                )
            )));
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenChangeDivisionCourseModal($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openChangeDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return string
     */
    public function openChangeDivisionCourseModal($DivisionCourseId, $PersonId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        if (!($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            return new Danger('Schuljahr wurde nicht gefunden', new Exclamation());
        }

        return $this->getChangeDivisionCourseModal(DivisionCourse::useFrontend()->formChangeDivisionCourse($tblDivisionCourse, $tblPerson, $tblYear, true),
            $tblDivisionCourse, $tblPerson);
    }

    /**
     * @param $DivisionCourseId
     * @param null $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineChangeDivisionCourseSave($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveChangeDivisionCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveChangeDivisionCourseModal($DivisionCourseId, $PersonId, $Data)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        if (($form = DivisionCourse::useService()->checkFormChangeDivisionCourse($Data, $tblDivisionCourse, $tblPerson))) {
            // display Errors on form
            return $this->getChangeDivisionCourseModal($form, $tblDivisionCourse, $tblPerson);
        }

        if (DivisionCourse::useService()->changeDivisionCourse($tblDivisionCourse, $tblPerson, $Data)) {
            return new Success('Der ' . $tblDivisionCourse->getTypeName() . 'nwechsel wurde erfolgreich gespeichert.')
                . self::pipelineLoadRemoveStudentContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Der ' . $tblDivisionCourse->getTypeName() . 'nwechsel konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

//    /**
//     * @param $form
//     * @param TblPerson $tblPerson
//     * @param TblDivisionCourse|null $tblDivisionCourse
//     * @param TblStudentEducation|null $tblStudentEducation
//     *
//     * @return string
//     */
//    private function getEditStudentEducationModal($form, TblPerson $tblPerson, ?TblDivisionCourse $tblDivisionCourse, ?TblStudentEducation $tblStudentEducation): string
//    {
//        $tblYear = false;
//        if ($tblDivisionCourse) {
//            $tblYear = $tblDivisionCourse->getServiceTblYear();
//        } elseif ($tblStudentEducation) {
//            $tblYear = $tblStudentEducation->getServiceTblYear();
//        }
//
//        return new Title(new Edit() . ' Schüler-Bildung bearbeiten')
//            . new Layout(new LayoutGroup(array(
//                new LayoutRow(array(
//                    new LayoutColumn(
//                        new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
//                        , 6),
//                    new LayoutColumn(
//                        new Panel('Schuljahr', $tblYear ? $tblYear->getDisplayName() : '', Panel::PANEL_TYPE_INFO)
//                        , 6)
//                )),
//                new LayoutRow(
//                    new LayoutColumn(
//                        new Well($form)
//                    )
//                )
//            )));
//    }

//    /**
//     * @param $PersonId
//     * @param $DivisionCourseId
//     * @param $StudentEducationId
//     *
//     * @return Pipeline
//     */
//    public static function pipelineOpenEditStudentEducationModal($PersonId, $DivisionCourseId, $StudentEducationId): Pipeline
//    {
//        $Pipeline = new Pipeline(false);
//        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
//        $ModalEmitter->setGetPayload(array(
//            self::API_TARGET => 'openEditStudentEducationModal',
//        ));
//        $ModalEmitter->setPostPayload(array(
//            'PersonId' => $PersonId,
//            'DivisionCourseId' => $DivisionCourseId,
//            'StudentEducationId' => $StudentEducationId
//        ));
//        $Pipeline->appendEmitter($ModalEmitter);
//
//        return $Pipeline;
//    }

//    /**
//     * @param $PersonId
//     * @param $DivisionCourseId
//     * @param $StudentEducationId
//     *
//     * @return string
//     */
//    public function openEditStudentEducationModal($PersonId, $DivisionCourseId, $StudentEducationId)
//    {
//        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
//            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
//        }
//
//        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
//        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationById($StudentEducationId);
//
//        return $this->getEditStudentEducationModal(DivisionCourse::useFrontend()->formEditStudentEducation(
//            $tblPerson, $tblDivisionCourse ?: null, $tblStudentEducation ?: null, true
//        ), $tblPerson, $tblDivisionCourse ?: null, $tblStudentEducation ?: null);
//    }

    /**
     * @param $PersonId
     * @param $DivisionCourseId
     * @param $StudentEducationId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentEducationSave($PersonId, $DivisionCourseId, $StudentEducationId): Pipeline
    {
        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseStudentContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentEducation'
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionCourseId' => $DivisionCourseId,
            'StudentEducationId' => $StudentEducationId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $PersonId
     * @param $DivisionCourseId
     * @param $StudentEducationId
     * @param $StudentEducationData
     *
     * @return Danger|string
     */
    public function saveEditStudentEducation($PersonId, $DivisionCourseId, $StudentEducationId, $StudentEducationData)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationById($StudentEducationId);

        if (($form = DivisionCourse::useService()->checkFormEditStudentEducation($StudentEducationData, $tblPerson, $tblDivisionCourse ?: null, $tblStudentEducation ?: null))) {
            // display Errors on form
//            return $this->getEditStudentEducationModal($form, $tblPerson, $tblDivisionCourse ?: null, $tblStudentEducation ?: null);
            return new Well($form);
        }

        if ($tblStudentEducation && DivisionCourse::useService()->updateStudentEducation($tblStudentEducation, $StudentEducationData)) {
            return new Success('Die Schüler-Bildung wurde erfolgreich gespeichert.')
                . ($DivisionCourseId ? self::pipelineLoadDivisionCourseStudentContent($DivisionCourseId) : ApiPersonReadOnly::pipelineLoadStudentProcessContent($PersonId))
                . self::pipelineClose();
        } else {
            return new Danger('Die Schüler-Bildung konnte nicht gespeichert werden.');
        }
    }

    /**
     * @param $form
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getCreateStudentEducationModal($form, TblPerson $tblPerson): string
    {
        $content = '';
        $hasNowStudentEducation = false;

        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                if (DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)) {
                    $hasNowStudentEducation = true;
                    break;
                }
            }
        }

        if (($tblFutureYearList = Term::useService()->getYearAllFutureYears(1))) {
            foreach ($tblFutureYearList as $tblFutureYear) {
                if (DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblFutureYear)) {
                    if ($hasNowStudentEducation) {
                        $content = new Warning('Es existiert bereits eine Schüler-Bildung für diesen Schüler für das zukünftige Schuljahr, 
                            bitte bearbeiten Sie diesen Eintrag.', new Exclamation());
                    }
                    break;
                }
            }
        } elseif ($hasNowStudentEducation) {
            $content = new Warning('Bitte legen Sie erst ein neues Schuljahr an, um für den Schüler eine neue Schüler-Bildung anzulegen.', new Exclamation());
        }

        return new Title(new Plus() . ' Schüler-Bildung hinzufügen')
            . new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
                    ),
                )),
                new LayoutRow(
                    new LayoutColumn(
                        $content ?: new Well($form)
                    )
                )
            )));
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateStudentEducationModal($PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateStudentEducationModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateStudentEducationModal($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        return $this->getCreateStudentEducationModal(DivisionCourse::useFrontend()->formCreateStudentEducation($tblPerson), $tblPerson);
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateStudentEducationSave($PersonId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateStudentEducationModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveCreateStudentEducationModal($PersonId, $Data)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        if (($form = DivisionCourse::useService()->checkFormCreateStudentEducation($Data, $tblPerson))) {
            // display Errors on form
            return $this->getCreateStudentEducationModal($form, $tblPerson);
        }

        if (DivisionCourse::useService()->createStudentEducation($Data, $tblPerson)) {
            return new Success('Die Schüler-Bildung wurde erfolgreich gespeichert.')
                . ApiPersonReadOnly::pipelineLoadStudentProcessContent($PersonId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Schüler-Bildung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}