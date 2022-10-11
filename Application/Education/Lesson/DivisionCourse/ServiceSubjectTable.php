<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTableLink;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceSubjectTable extends AbstractService
{
    /**
     * @param $Id
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableById($Id)
    {
        return (new Data($this->getBinding()))->getSubjectTableById($Id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     *
     * @return false|TblSubjectTable[]
     */
    public function getSubjectTableListBy(TblType $tblSchoolType, ?int $level = null)
    {
        return (new Data($this->getBinding()))->getSubjectTableListBy($tblSchoolType, $level);
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkListBySchoolType(TblType $tblSchoolType)
    {
        return (new Data($this->getBinding()))->getSubjectTableLinkListBySchoolType($tblSchoolType);
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return false|TblSubjectTableLink[]
     */
    public function getSubjectTableLinkListBySubjectTable(TblSubjectTable $tblSubjectTable)
    {
        return (new Data($this->getBinding()))->getSubjectTableLinkListBySubjectTable($tblSubjectTable);
    }

    /**
     * @param $SchoolTypeId
     * @param $Data
     * @param TblSubjectTable|null $tblSubjectTable
     *
     * @return false|Form
     */
    public function checkFormSubjectTable($SchoolTypeId, $Data, TblSubjectTable $tblSubjectTable = null)
    {
        $error = false;
        $form = DivisionCourse::useFrontend()->formSubjectTable($tblSubjectTable ? $tblSubjectTable->getId() : null, $SchoolTypeId);

        if (!isset($Data['Level']) || empty($Data['Level'])) {
            $form->setError('Data[Level]', 'Bitte geben Sie eine Klassenstufe ein');
            $error = true;
        } else {
            $form->setSuccess('Data[Level]');
        }

        if (!isset($Data['Subject']) || (!($Data['Subject'] < 0) && !Subject::useService()->getSubjectById($Data['Subject']))) {
            $form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
            $error = true;
        } else {
            $form->setSuccess('Data[Subject]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblType $tblSchoolType
     * @param array $Data
     *
     * @return false|TblSubjectTable
     */
    public function createSubjectTable(TblType $tblSchoolType, array $Data)
    {
        list($tblSubject, $studentMetaIdentifier) = $this->getSubjectAndStudentMetaIdentifier($Data);

        return (new Data($this->getBinding()))->createSubjectTable(TblSubjectTable::withParameter(
            $tblSchoolType,
            $Data['Level'],
            $tblSubject ?: null,
            $Data['TypeName'],
            (new Data($this->getBinding()))->getSubjectTableRankingForNewSubjectTable($tblSchoolType, $tblSubject ?: null, $studentMetaIdentifier),
            $Data['HoursPerWeek'] !== '' ? $Data['HoursPerWeek'] : null,
            $studentMetaIdentifier,
            isset($Data['HasGrading'])
        ));
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     * @param array $Data
     *
     * @return bool
     */
    public function updateSubjectTable(TblSubjectTable $tblSubjectTable, array $Data): bool
    {
        list($tblSubject, $studentMetaIdentifier) = $this->getSubjectAndStudentMetaIdentifier($Data);

        return (new Data($this->getBinding()))->updateSubjectTable(
            $tblSubjectTable,
            $Data['Level'],
            $Data['TypeName'],
            $tblSubject ?: null,
            $studentMetaIdentifier,
            $Data['HoursPerWeek'] !== '' ? $Data['HoursPerWeek'] : null,
            isset($Data['HasGrading'])
        );
    }

    /**
     * @param array $Data
     *
     * @return array
     */
    private function getSubjectAndStudentMetaIdentifier(array $Data): array
    {
        $studentMetaIdentifier = isset($Data['StudentMetaIdentifier']) && $Data['StudentMetaIdentifier'] !== 0 ? $Data['StudentMetaIdentifier'] : '';
        $tblSubject = false;
        switch ($Data['Subject']) {
            case TblSubjectTable::SUBJECT_FS_1_Id: $studentMetaIdentifier = 'FS_1'; break;
            case TblSubjectTable::SUBJECT_FS_2_Id: $studentMetaIdentifier = 'FS_2'; break;
            case TblSubjectTable::SUBJECT_FS_3_Id: $studentMetaIdentifier = 'FS_3'; break;
            case TblSubjectTable::SUBJECT_FS_4_Id: $studentMetaIdentifier = 'FS_4'; break;
            case TblSubjectTable::SUBJECT_RELIGION: $studentMetaIdentifier = 'RELIGION'; break;
            case TblSubjectTable::SUBJECT_PROFILE: $studentMetaIdentifier = 'PROFILE'; break;
            case TblSubjectTable::SUBJECT_ORIENTATION: $studentMetaIdentifier = 'ORIENTATION'; break;
            default: $tblSubject = Subject::useService()->getSubjectById($Data['Subject']);
        }

        return array($tblSubject, $studentMetaIdentifier);
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return bool
     */
    public function destroySubjectTable(TblSubjectTable $tblSubjectTable): bool
    {
        return (new Data($this->getBinding()))->destroySubjectTable($tblSubjectTable);
    }

    /**
     * @param $SchoolTypeId
     * @param $Data
     * @param TblSubjectTableLink|null $tblSubjectTableLink
     *
     * @return false|Form
     */
    public function checkFormSubjectTableLink($SchoolTypeId, $Data, TblSubjectTableLink $tblSubjectTableLink = null)
    {
        $error = false;
        $form = DivisionCourse::useFrontend()->formSubjectTableLink($tblSubjectTableLink ? $tblSubjectTableLink->getId() : null, $SchoolTypeId, $Data);

        if (!isset($Data['Level']) || empty($Data['Level'])) {
            $form->setError('Data[Level]', 'Bitte geben Sie eine Klassenstufe ein');
            $error = true;
        } else {
            $form->setSuccess('Data[Level]');
        }

        if (!isset($Data['MinCount']) || empty($Data['MinCount'])) {
            $form->setError('Data[MinCount]', 'Bitte geben Sie eine Mindestanzahl ein');
            $error = true;
        } else {
            $form->setSuccess('Data[MinCount]');
        }

        return $error ? $form : false;
    }

    /**
     * @param array $Data
     *
     * @return bool
     */
    public function createSubjectTableLink(array $Data): bool
    {
        if (isset($Data['SubjectTables']) && count($Data['SubjectTables']) > 1) {
            $linkId = (new Data($this->getBinding()))->getNextLinkId();
            foreach ($Data['SubjectTables'] as $subjectTableId => $value) {
                if (($tblSubjectTable = $this->getSubjectTableById($subjectTableId))) {
                    (new Data($this->getBinding()))->createSubjectTableLink($linkId, $Data['MinCount'], $tblSubjectTable);
                }
            }

            return true;
        }

        return false;
    }
}