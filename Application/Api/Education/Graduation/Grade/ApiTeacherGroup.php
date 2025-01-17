<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiTeacherGroup  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadViewTeacherGroups');
        $Dispatcher->registerMethod('loadViewTeacherGroupEdit');
        $Dispatcher->registerMethod('loadTeacherGroupStudentSelect');
        $Dispatcher->registerMethod('saveTeacherGroupEdit');
        $Dispatcher->registerMethod('loadViewTeacherGroupDelete');
        $Dispatcher->registerMethod('saveTeacherGroupDelete');

        $Dispatcher->registerMethod('loadViewTeacherGroupSort');

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
     * @return Pipeline
     */
    public static function pipelineLoadViewTeacherGroups(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTeacherGroups',
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadViewTeacherGroups(): string
    {
        return Grade::useFrontend()->loadViewTeacherGroups();
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTeacherGroupEdit($DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTeacherGroupEdit',
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
    public function loadViewTeacherGroupEdit($DivisionCourseId): string
    {
        return Grade::useFrontend()->loadViewTeacherGroupEdit($DivisionCourseId);
    }

    /**
     * @param null $SubjectId
     * @param null $DivisionCourseId
     * @param null $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadTeacherGroupStudentSelect($SubjectId = null, $DivisionCourseId = null, $Data = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TeacherGroupStudentSelect'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherGroupStudentSelect',
        ));
        $ModalEmitter->setPostPayload(array(
            'SubjectId' => $SubjectId,
            'DivisionCourseId' => $DivisionCourseId,
            'Data' => $Data
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SubjectId
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return string
     */
    public function loadTeacherGroupStudentSelect($SubjectId, $DivisionCourseId, $Data): string
    {
        if (isset($Data['Subject'])) {
            $SubjectId = $Data['Subject'];
        }

        return Grade::useFrontend()->loadTeacherGroupStudentSelect($SubjectId, $DivisionCourseId, $Data);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTeacherGroupEdit($DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTeacherGroupEdit'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return string
     */
    public function saveTeacherGroupEdit($DivisionCourseId, $Data): string
    {
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);

        if (!($tblPerson = Account::useService()->getPersonByLogin())) {
            return (new Danger("Person nicht gefunden!", new Exclamation()));
        }

        if (($form = Grade::useService()->checkFormTeacherGroup($Data, $tblDivisionCourse ?: null))) {
            // display Errors on form
            return Grade::useFrontend()->getTeacherGroupEdit($form, $DivisionCourseId);
        }

        $tblType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP);
        $tblMemberTypeStudent = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT);
        $tblMemberTypeDivisionTeacher = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
        if ($tblDivisionCourse) {
            $Data['Subject'] = ($tblSubject = $tblDivisionCourse->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            DivisionCourse::useService()->updateDivisionCourse($tblDivisionCourse, $Data);

            $tempList = array();
            $createList = array();
            $removeList = array();
            if (($tblDivisionCourseMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT, false, false))) {
                foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                    // löschen
                    if (($tblStudent = $tblDivisionCourseMember->getServiceTblPerson()))  {
                        if (!isset($Data['Students'][$tblStudent->getId()])) {
                            $removeList[] = $tblDivisionCourseMember;
                        } else {
                            $tempList[$tblStudent->getId()] = $tblDivisionCourseMember;
                        }
                    }
                }
            }

            // neu
            if (isset($Data['Students'])) {
                foreach ($Data['Students'] as $personId => $value) {
                    if (($tblStudent = Person::useService()->getPersonById($personId)) && !isset($tempList[$personId])) {
                        $createList[] = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblMemberTypeStudent, $tblStudent);
                    }
                }
            }

            if (!empty($createList)) {
                DivisionCourse::useService()->createDivisionCourseMemberBulk($createList);
            }
            if (!empty($removeList)) {
                DivisionCourse::useService()->removeDivisionCourseMemberBulk($removeList);
            }
        } else {
            $Data['Type'] = $tblType->getId();
            $Data['Year'] = ($tblYear = Grade::useService()->getYear()) ? $tblYear->getId() : null;
            if (($tblDivisionCourseNew = DivisionCourse::useService()->createDivisionCourse($Data))) {
                // Schüler
                if (isset($Data['Students'])) {
                    $createList = array();
                    foreach ($Data['Students'] as $personId => $value) {
                        if (($tblStudent = Person::useService()->getPersonById($personId))) {
                            $createList[] = TblDivisionCourseMember::withParameter($tblDivisionCourseNew, $tblMemberTypeStudent, $tblStudent);
                        }
                    }

                    DivisionCourse::useService()->createDivisionCourseMemberBulk($createList);
                }

                // Lehrer
                DivisionCourse::useService()->addDivisionCourseMemberToDivisionCourse($tblDivisionCourseNew, $tblMemberTypeDivisionTeacher, $tblPerson, '');
            }
        }

        return new Success("{$tblType->getName()} wurde erfolgreich gespeichert.")
            . self::pipelineLoadViewTeacherGroups();
    }

    /**
     * @param $DivisionCourseId

     * @return Pipeline
     */
    public static function pipelineLoadViewTeacherGroupDelete($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTeacherGroupDelete',
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
    public function loadViewTeacherGroupDelete($DivisionCourseId): string
    {
        return Grade::useFrontend()->loadViewTeacherGroupDelete($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTeacherGroupDelete($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTeacherGroupDelete'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function saveTeacherGroupDelete($DivisionCourseId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->destroyDivisionCourse($tblDivisionCourse)) {
            return new Success('Der Kurs wurde erfolgreich gelöscht.')
                . self::pipelineLoadViewTeacherGroups();
        } else {
            return new Danger('Der Kurs konnte nicht gelöscht werden.');
        }
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTeacherGroupSort($DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTeacherGroupSort',
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
    public function loadViewTeacherGroupSort($DivisionCourseId): string
    {
        return Grade::useFrontend()->loadViewTeacherGroupSort($DivisionCourseId);
    }
}