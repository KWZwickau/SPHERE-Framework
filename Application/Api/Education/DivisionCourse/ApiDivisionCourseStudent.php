<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
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

        $Dispatcher->registerMethod('loadRemoveStudentContent');
        $Dispatcher->registerMethod('loadAddStudentContent');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('selectDivisionCourse');
        $Dispatcher->registerMethod('searchProspect');
        $Dispatcher->registerMethod('addStudent');
        $Dispatcher->registerMethod('removeStudent');

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
                . self::pipelineLoadRemoveStudentContent($DivisionCourseId);
        } else {
            return new Danger('Schüler konnte nicht entfernt werden.');
        }
    }
}