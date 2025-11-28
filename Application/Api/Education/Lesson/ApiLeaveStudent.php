<?php

namespace SPHERE\Application\Api\Education\Lesson;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\LeaveStudent\LeaveStudent;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiLeaveStudent extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadContent');
        $Dispatcher->registerMethod('openAddStudentModal');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('saveAddStudentModal');
        $Dispatcher->registerMethod('openEditModal');

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
        $Pipeline = new Pipeline(false);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $YearId
     * @param string $hasLoadingMessage
     *
     * @return Pipeline
     */
    public static function pipelineLoadContent($SchoolTypeId = null, $YearId = null, string $hasLoadingMessage = 'true'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
        ));
        if ($hasLoadingMessage === 'true') {
            $ModalEmitter->setLoadingMessage('Daten werden geladen');
        }
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $YearId
     * @param null $Data
     *
     * @return string
     */
    public function loadContent($SchoolTypeId = null, $YearId = null, $Data = null): string
    {
        if ($SchoolTypeId && $YearId) {
            $Data = [];
            $Data['SchoolType'] = $SchoolTypeId;
            $Data['Year'] = $YearId;
        }

        return LeaveStudent::useFrontend()->loadContent($Data);
    }


    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddStudentModal($SchoolTypeId, $YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openAddStudentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param null|array $Data
     *
     * @return string
     */
    public function openAddStudentModal($SchoolTypeId, $YearId, ?array $Data = null): string
    {
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data ?: null);
        }

        return LeaveStudent::useFrontend()->loadAddStudentContent($SchoolTypeId, $YearId);
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineSearchPerson($SchoolTypeId, $YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $YearId
     * @param null $Search
     *
     * @return string
     */
    public function searchPerson($SchoolTypeId = null, $YearId = null, $Search = null): string
    {
        return LeaveStudent::useFrontend()->loadPersonSearch($SchoolTypeId, $YearId, trim($Search));
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddStudentSave($SchoolTypeId, $YearId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveAddStudentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
            'PersonId' => $PersonId,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $PersonId
     *
     * @return string
     */
    public function saveAddStudentModal($SchoolTypeId = null, $YearId = null, $PersonId = null): string
    {
        $Data = [];
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            if (($tblLeaveStudent = LeaveStudent::useService()->getLeaveStudentBy($tblSchoolType, $tblYear))) {
                $Data = $tblLeaveStudent->getData();
            }
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                $Data[$tblPerson->getId()] = [
                    'Select' => 1,
                    'Added' => 1,
                ];

                LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data);

                // Suche leeren
                $_POST['Search'] = '';

                return new Success(@"{$tblPerson->getLastFirstNameWithCallNameUnderline()} wurde erfolgreich den Schulabgängern hinzugefügt", new SuccessIcon())
//                    . self::pipelineClose()
//                    . self::pipelineLoadContent($SchoolTypeId, $YearId);
                     . LeaveStudent::useFrontend()->loadAddStudentContent($SchoolTypeId, $YearId)
                    . self::pipelineLoadContent($SchoolTypeId, $YearId, 'false');
            }
        }

        return new Danger('Person wurde nicht gefunden!', new Exclamation());
    }
}