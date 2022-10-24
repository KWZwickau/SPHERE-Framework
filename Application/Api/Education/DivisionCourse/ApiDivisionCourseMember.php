<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

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

        $Dispatcher->registerMethod('loadSortMemberContent');
        $Dispatcher->registerMethod('openSortMemberModal');
        $Dispatcher->registerMethod('saveSortMemberModal');

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

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     *
     * @return Pipeline
     */
    public static function pipelineLoadSortMemberContent($DivisionCourseId, string $MemberTypeIdentifier = ''): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SortMemberContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSortMemberContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberTypeIdentifier' => $MemberTypeIdentifier
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     *
     * @return string
     */
    public function loadSortMemberContent($DivisionCourseId, string $MemberTypeIdentifier = ''): string
    {
        return DivisionCourse::useFrontend()->loadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier);
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     * @param string $sortType
     *
     * @return Pipeline
     */
    public static function pipelineOpenSortMemberModal($DivisionCourseId, string $MemberTypeIdentifier = '', string $sortType = ''): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openSortMemberModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberTypeIdentifier' => $MemberTypeIdentifier,
            'sortType' => $sortType,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $DivisionCourseId
     * @param string $MemberTypeIdentifier
     * @param string $sortType
     *
     * @return string
     */
    public static function openSortMemberModal($DivisionCourseId = null, string $MemberTypeIdentifier = '', string $sortType = ''): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier($MemberTypeIdentifier))) {
            return new Danger('Mitglieds-Typ nicht gefunden', new Exclamation());
        }

        $button = (new Standard('Ja', '/Education/Lesson/Division/Sort/Alphabetically', new Ok(), array('DivisionCourseId' => $DivisionCourseId)))
            ->ajaxPipelineOnClick(self::pipelineSaveSortMemberModal($DivisionCourseId, $MemberTypeIdentifier, $sortType));

        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER) {
            $memberName = $tblDivisionCourse->getDivisionTeacherName();
        } else {
            $memberName = $tblMemberType->getName();
        }

        return new Title($memberName, $sortType)
            . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
            . new Panel('"'.new Bold($sortType).'" Sollen alle ' . $memberName . ' des Kurses neu sortiert werden?',
                $button . new Close('Nein'), Panel::PANEL_TYPE_WARNING);
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     * @param string $sortType
     *
     * @return Pipeline
     */
    public static function pipelineSaveSortMemberModal($DivisionCourseId, string $MemberTypeIdentifier = '', string $sortType = ''): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveSortMemberModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'MemberTypeIdentifier' => $MemberTypeIdentifier,
            'sortType' => $sortType,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     * @param string $sortType
     *
     * @return string
     */
    public static function saveSortMemberModal($DivisionCourseId = null, string $MemberTypeIdentifier = '', string $sortType = ''): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier($MemberTypeIdentifier))) {
            return new Danger('Mitglieds-Typ nicht gefunden', new Exclamation());
        }

        if ($sortType == 'Sortierung Geschlecht (alphabetisch)') {
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, $MemberTypeIdentifier, true, false))) {
                $maleList = array();
                $femaleList = array();
                $otherList = array();
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        if (($tblGender = $tblPerson->getGender())) {
                            if ($tblGender->getName() == 'Männlich') {
                                $maleList[] = $tblMember;
                                continue;
                            } elseif ($tblGender->getName() == 'Weiblich') {
                                $femaleList[] = $tblMember;
                                continue;
                            }
                        }
                        $otherList[] = $tblMember;
                    }
                }

                if (!empty($maleList)) {
                    $maleList = (new Extension())->getSorter($maleList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                }
                if (!empty($femaleList)) {
                    $femaleList = (new Extension())->getSorter($femaleList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                }
                if (!empty($otherList)) {
                    $otherList = (new Extension())->getSorter($otherList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                }

                if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Sort', 'SortMaleFirst')) && !$tblSetting->getValue()) {
                    $tblMemberList = array_merge($femaleList, $maleList, $otherList);
                } else {
                    $tblMemberList = array_merge($maleList, $femaleList, $otherList);
                }
            }
        // 'Sortierung alphabetisch'
        } else {
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, $MemberTypeIdentifier, true, false))) {
                $tblMemberList = (new Extension())->getSorter($tblMemberList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            }
        }

        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER) {
            $memberName = $tblDivisionCourse->getDivisionTeacherName();
        } else {
            $memberName = $tblMemberType->getName();
        }

        if ($tblMemberList) {
            $count = 1;
            /** @var TblDivisionCourseMember $tblMember */
            foreach ($tblMemberList as $tblMember) {
                $tblMember->setSortOrder($count++);
            }
            DivisionCourse::useService()->updateDivisionCourseMemberBulkSortOrder($tblMemberList, $MemberTypeIdentifier, $tblDivisionCourse->getType() ?: null);

            return new Success('Die ' . $memberName . ' wurden erfolgreich sortiert.')
                . self::pipelineLoadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier)
                . self::pipelineClose();
        }

        return new Danger('Die ' . $memberName . ' konnten nicht sortiert werden.')
                . self::pipelineLoadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier)
                . self::pipelineClose();
    }
}