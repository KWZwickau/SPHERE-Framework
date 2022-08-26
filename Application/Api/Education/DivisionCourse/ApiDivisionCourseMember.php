<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiDivisionCourseMember extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadDivisionTeacherContent');
        $Dispatcher->registerMethod('addDivisionTeacher');
        $Dispatcher->registerMethod('removeDivisionTeacher');

        $Dispatcher->registerMethod('loadRepresentativeContent');
        $Dispatcher->registerMethod('addRepresentative');
        $Dispatcher->registerMethod('removeRepresentative');

        $Dispatcher->registerMethod('loadCustodyContent');
        $Dispatcher->registerMethod('addCustody');
        $Dispatcher->registerMethod('removeCustody');

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
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionTeacherContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionTeacherContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionTeacherContent',
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
    public function loadDivisionTeacherContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadDivisionTeacherContent($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddDivisionTeacher($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionTeacherContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addDivisionTeacher'
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
     * @param null $Data
     *
     * @return string
     */
    public function addDivisionTeacher($DivisionCourseId, $PersonId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger($tblDivisionCourse->getDivisionTeacherName(false) . ' wurde nicht gefunden', new Exclamation());
        }
        if (!($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            return new Danger('Typ: Klassenlehrer wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->addDivisionCourseMemberToDivisionCourse($tblDivisionCourse, $tblMemberType, $tblPerson, $Data['Description'])) {
            return new Success($tblDivisionCourse->getDivisionTeacherName(false) . ' wurde erfolgreich hinzugefügt.')
                . self::pipelineLoadDivisionTeacherContent($DivisionCourseId);
        } else {
            return new Danger($tblDivisionCourse->getDivisionTeacherName(false) . ' konnte nicht hinzugefügt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return Pipeline
     */
    public static function pipelineRemoveDivisionTeacher($DivisionCourseId, $MemberId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionTeacherContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removeDivisionTeacher'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberId' => $MemberId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return string
     */
    public function removeDivisionTeacher($DivisionCourseId, $MemberId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourseMember = DivisionCourse::useService()->getDivisionCourseMemberById($MemberId))) {
            return new Danger($tblDivisionCourse->getDivisionTeacherName(false) . ' wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember)) {
            return new Success($tblDivisionCourse->getDivisionTeacherName(false) . ' wurde erfolgreich entfernt.')
                . self::pipelineLoadDivisionTeacherContent($DivisionCourseId);
        } else {
            return new Danger($tblDivisionCourse->getDivisionTeacherName(false) . ' konnte nicht entfernt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRepresentativeContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RepresentativeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRepresentativeContent',
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
    public function loadRepresentativeContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadRepresentativeContent($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddRepresentative($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RepresentativeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addRepresentative'
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
     * @param null $Data
     *
     * @return string
     */
    public function addRepresentative($DivisionCourseId, $PersonId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schülersprecher wurde nicht gefunden', new Exclamation());
        }
        if (!($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))) {
            return new Danger('Typ: Schülersprecher wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->addDivisionCourseMemberToDivisionCourse($tblDivisionCourse, $tblMemberType, $tblPerson, $Data['Description'])) {
            return new Success('Schülersprecher wurde erfolgreich hinzugefügt.')
                . self::pipelineLoadRepresentativeContent($DivisionCourseId);
        } else {
            return new Danger('Schülersprecher konnte nicht hinzugefügt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return Pipeline
     */
    public static function pipelineRemoveRepresentative($DivisionCourseId, $MemberId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RepresentativeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removeRepresentative'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberId' => $MemberId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return string
     */
    public function removeRepresentative($DivisionCourseId, $MemberId)
    {
        if (!($tblDivisionCourseMember = DivisionCourse::useService()->getDivisionCourseMemberById($MemberId))) {
            return new Danger('Schülersprecher wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember)) {
            return new Success('Schülersprecher wurde erfolgreich entfernt.')
                . self::pipelineLoadRepresentativeContent($DivisionCourseId);
        } else {
            return new Danger('Schülersprecher konnte nicht entfernt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCustodyContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCustodyContent',
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
    public function loadCustodyContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadCustodyContent($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineAddCustody($DivisionCourseId, $PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'addCustody'
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
     * @param null $Data
     *
     * @return string
     */
    public function addCustody($DivisionCourseId, $PersonId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Elternvertreter wurde nicht gefunden', new Exclamation());
        }
        if (!($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_CUSTODY))) {
            return new Danger('Typ: Elternvertreter wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->addDivisionCourseMemberToDivisionCourse($tblDivisionCourse, $tblMemberType, $tblPerson, $Data['Description'])) {
            return new Success('Elternvertreter wurde erfolgreich hinzugefügt.')
                . self::pipelineLoadCustodyContent($DivisionCourseId);
        } else {
            return new Danger('Elternvertreter konnte nicht hinzugefügt werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return Pipeline
     */
    public static function pipelineRemoveCustody($DivisionCourseId, $MemberId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removeCustody'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberId' => $MemberId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $MemberId
     *
     * @return string
     */
    public function removeCustody($DivisionCourseId, $MemberId)
    {
        if (!($tblDivisionCourseMember = DivisionCourse::useService()->getDivisionCourseMemberById($MemberId))) {
            return new Danger('Elternvertreter wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember)) {
            return new Success('Elternvertreter wurde erfolgreich entfernt.')
                . self::pipelineLoadCustodyContent($DivisionCourseId);
        } else {
            return new Danger('Elternvertreter konnte nicht entfernt werden.');
        }
    }
}