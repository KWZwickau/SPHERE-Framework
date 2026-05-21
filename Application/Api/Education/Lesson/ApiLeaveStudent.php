<?php

namespace SPHERE\Application\Api\Education\Lesson;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\LeaveStudent\LeaveStudent;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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
        $Dispatcher->registerMethod('saveEditModal');

        $Dispatcher->registerMethod('cancelLeaveStudent');
        $Dispatcher->registerMethod('openConfirmModal');
        $Dispatcher->registerMethod('saveLeaveStudent');

        $Dispatcher->registerMethod('saveDocumentDate');

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
     * @noinspection PhpUnused
     */
    public function loadContent($SchoolTypeId = null, $YearId = null, $Data = null): string
    {
        $loadFormData = false;
        if ($SchoolTypeId && $YearId) {
            $loadFormData = true;
            $Data = [];
            $Data['SchoolType'] = $SchoolTypeId;
            $Data['Year'] = $YearId;
        }

        return LeaveStudent::useFrontend()->loadContent($Data, $loadFormData);
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
     * @noinspection PhpUnused
     */
    public function openAddStudentModal($SchoolTypeId, $YearId, ?array $Data = null): string
    {
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data ?: []);
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
     * @noinspection PhpUnused
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
     * @noinspection PhpUnused
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

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditModal($SchoolTypeId, $YearId, $Identifier): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
            'Identifier' => $Identifier
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $Identifier
     * @param null|array $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openEditModal($SchoolTypeId, $YearId, $Identifier, ?array $Data = null): string
    {
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data ?: null);
        }

        return LeaveStudent::useFrontend()->loadEditModalContent($SchoolTypeId, $YearId, $Identifier, $Data);
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $Identifier
     * @param $EditData
     *
     * @return Pipeline
     */
    public static function pipelineEditModalSave($SchoolTypeId, $YearId, $Identifier, $EditData): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
            'Identifier' => $Identifier,
            'EditData' => $EditData,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $YearId
     * @param null $Identifier
     * @param null $EditData
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditModal($SchoolTypeId = null, $YearId = null, $Identifier = null, $EditData = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            || !($tblYear = Term::useService()->getYearById($YearId))
        ) {
            return new Danger('Daten konnten nicht gespeichert werden', new Exclamation());
        }

        $Data = [];
        if (($tblLeaveStudent = LeaveStudent::useService()->getLeaveStudentBy($tblSchoolType, $tblYear))) {
            $Data = $tblLeaveStudent->getData();
        }


        $value = $EditData[$Identifier] ?? null;
        if (isset($EditData['Persons'])) {
            foreach ($EditData['Persons'] as $personId => $selected) {
                if (isset($Data[$personId][$Identifier])) {
                    $Data[$personId][$Identifier] = $value;
                }
            }
        }

        LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data);

        return new Success('Daten wurde erfolgreich übernommen', new SuccessIcon())
            . self::pipelineClose()
            . self::pipelineLoadContent($SchoolTypeId, $YearId);
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenConfirmModal($SchoolTypeId, $YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openConfirmModal',
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
     * @noinspection PhpUnused
     */
    public function openConfirmModal($SchoolTypeId, $YearId, ?array $Data = null): string
    {
        $dataList = [];
        $count = 0;
        if ($Data) {
            foreach ($Data as $personId => $item) {
                if (isset($item['Select'])
                    && ($tblPerson = Person::useService()->getPersonById($personId))
                ) {
                    $count++;
                    $dataList[] = $tblPerson->getLastFirstNameWithCallNameUnderline(true);
                }
            }
        }

        return new Title("Bestätigung Unwiderruflich Speichern")
            . new Warning("Sie sind dabei eine Massenänderung vorzunehmen, welche nicht rückgängig gemacht werden kann. Bitte Speichern Sie die Schulabgänger 
                erst wenn das ausgewählte Schuljahr beendet ist.", new Exclamation())
            . new Panel(
                new Question() . " $count ausgewählte Schulabgänger wirklich speichern?",
                $dataList,
                Panel::PANEL_TYPE_DANGER
            )
            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                ->ajaxPipelineOnClick(self::pipelineSaveLeaveStudent($SchoolTypeId, $YearId, $Data))
            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                ->ajaxPipelineOnClick(self::pipelineClose());
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineCancelLeaveStudent($SchoolTypeId, $YearId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'cancelLeaveStudent'
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
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function cancelLeaveStudent($SchoolTypeId = null, $YearId = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            || !($tblYear = Term::useService()->getYearById($YearId))
        ) {
            return new Danger('Daten konnten nicht gespeichert werden', new Exclamation());
        }

        // Daten leeren
        LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, [], false);

        return new Success('Daten wurde zurückgesetzt', new SuccessIcon())
            . self::pipelineLoadContent($SchoolTypeId, $YearId);
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $Data
     *
     * @return Pipeline
     */
    public static function pipelineSaveLeaveStudent($SchoolTypeId, $YearId, $Data): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveLeaveStudent'
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearId' => $YearId,
            'Data' => $Data
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $YearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveLeaveStudent($SchoolTypeId = null, $YearId = null, $Data = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            || !($tblYear = Term::useService()->getYearById($YearId))
        ) {
            return new Danger('Daten konnten nicht gespeichert werden', new Exclamation());
        }

        $tblLeaveStudent = LeaveStudent::useService()->updateLeaveStudent($tblSchoolType, $tblYear, $Data ?: [], true);

        $tblFuturYears = [];
        $endDate = $tblYear->getEndDateTime();
        if (($tblYearList = Term::useService()->getYearAll())) {
            foreach ($tblYearList as $year) {
                $startDate = $year->getStartDateTime();
                if ($startDate > $endDate) {
                    $tblFuturYears[$year->getId()] = $year;
                }
            }
        }

        $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
        $tblGroupArchive = Group::useService()->getGroupByMetaTable('ARCHIVE');
        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE);
        $bulkStudentTransferSave = [];
        $bulkStudentTransferProtocol = [];
        $count = 0;
        foreach ($Data as $personId => $item) {
            if (isset($item['Select'])
                && ($tblPerson = Person::useService()->getPersonById($personId))
            ) {
                $count++;
                if (!empty($item['LeaveDate']) || !empty($item['Company'])) {
                    if (!($tblStudent = $tblPerson->getStudent())) {
                        $tblStudent = Student::useService()->createStudent($tblPerson);
                    }

                    if (!($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))) {
                        $tblStudentTransfer = new TblStudentTransfer();
                        $bulkStudentTransferProtocol[] = false;
                        $tblStudentTransfer->setTblStudent($tblStudent);
                        $tblStudentTransfer->setTblStudentTransferType($tblStudentTransferType);
                        $tblStudentTransfer->setRemark('');
                    } else {
                        $bulkStudentTransferProtocol[] = clone $tblStudentTransfer;
                    }

                    if (!empty($item['LeaveDate'])) {
                        $tblStudentTransfer->setTransferDate(new DateTime($item['LeaveDate']));
                    }
                    if (!empty($item['Company']) && ($tblCompany = Company::useService()->getCompanyById($item['Company']))) {
                        $tblStudentTransfer->setServiceTblCompany($tblCompany);
                    }

                    $bulkStudentTransferSave[] = $tblStudentTransfer;
                }

                // Gruppen
                if (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)) {
                    Group::useService()->removeGroupPerson($tblGroupStudent, $tblPerson);
                }
                if (!Group::useService()->existsGroupPerson($tblGroupArchive, $tblPerson)) {
                    Group::useService()->addGroupPerson($tblGroupArchive, $tblPerson);
                }
                if (!empty($item['GroupIndividual'])
                    && ($tblGroup = Group::useService()->getGroupById($item['GroupIndividual']))
                    && !Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                ) {
                    Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                }

                // Schülerbildung löschen, falls es diese für die zukunft gibt
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblYearTemp = $tblStudentEducation->getServiceTblYear())
                            && isset($tblFuturYears[$tblYearTemp->getId()])
                        ) {
                            DivisionCourse::useService()->destroyStudentEducation($tblStudentEducation);
                        }
                    }
                }
            }
        }

        if (!empty($bulkStudentTransferSave)) {
            Student::useService()->bulkSaveEntityList($bulkStudentTransferSave, $bulkStudentTransferProtocol);
        }

        return new Success(@"$count Schüler wurden zu Schulabgänger gemacht", new SuccessIcon())
            . LeaveStudent::useFrontend()->loadPrintView($tblLeaveStudent)
            . self::pipelineClose();
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineSaveDocumentDate($SchoolTypeId, $YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DocumentDateContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDocumentDate',
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
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveDocumentDate($SchoolTypeId, $YearId, $Data = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            || !($tblYear = Term::useService()->getYearById($YearId))
        ) {
            return new Danger('Datum der Ausstellung konnte nicht gespeichert werden', new Exclamation());
        }

        LeaveStudent::useService()->updateLeaveStudentSetDocumentDate($tblSchoolType, $tblYear, empty($Data['Date']) ? null : new DateTime($Data['Date']));

        return '';
    }
}