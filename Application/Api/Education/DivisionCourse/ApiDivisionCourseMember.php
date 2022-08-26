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
}