<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTableLink;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Structure\Form;

abstract class ServiceSubjectTable extends ServiceStudentSubject
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
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableBy(TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getSubjectTableBy($tblSchoolType, $level, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($level = $tblStudentEducation->getLevel())
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            return (new Data($this->getBinding()))->getSubjectTableBy($tblSchoolType, $level, $tblSubject);
        }

        return false;
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
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkBySubjectTable(TblSubjectTable $tblSubjectTable)
    {
        return (new Data($this->getBinding()))->getSubjectTableLinkBySubjectTable($tblSubjectTable);
    }

    /**
     * @param $LinkId
     *
     * @return false|TblSubjectTableLink[]
     */
    public function getSubjectTableLinkListByLinkId($LinkId)
    {
        return (new Data($this->getBinding()))->getSubjectTableLinkListByLinkId($LinkId);
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
            case TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_1_Id: $studentMetaIdentifier = 'FOREIGN_LANGUAGE_1'; break;
            case TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_2_Id: $studentMetaIdentifier = 'FOREIGN_LANGUAGE_2'; break;
            case TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_3_Id: $studentMetaIdentifier = 'FOREIGN_LANGUAGE_3'; break;
            case TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_4_Id: $studentMetaIdentifier = 'FOREIGN_LANGUAGE_4'; break;
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
        if (($tblSubjectTableLink = $this->getSubjectTableLinkBySubjectTable($tblSubjectTable))) {
            (new Data($this->getBinding()))->destroySubjectTableLink($tblSubjectTableLink);
        }

        return (new Data($this->getBinding()))->destroySubjectTable($tblSubjectTable);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkById($Id)
    {
        return (new Data($this->getBinding()))->getSubjectTableLinkById($Id);
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

    /**
     * @param TblSubjectTableLink $tblSubjectTableLink
     * @param array $Data
     *
     * @return bool
     */
    public function updateSubjectTableLink(TblSubjectTableLink $tblSubjectTableLink, array $Data): bool
    {
        if (($tblSubjectTableLinkList = DivisionCourse::useService()->getSubjectTableLinkListByLinkId($tblSubjectTableLink->getLinkId()))) {
            foreach ($tblSubjectTableLinkList as $item) {
                // löschen
                if (!isset($Data['SubjectTables'][$item->getTblSubjectTable()->getId()])) {
                    (new Data($this->getBinding()))->destroySubjectTableLink($item);
                // update
                } else {
                    (new Data($this->getBinding()))->updateSubjectTableLink($item, $Data['MinCount']);
                }
            }
        }

        // neu
        if (isset($Data['SubjectTables'])) {
            foreach ($Data['SubjectTables'] as $subjectTableId => $value) {
                if (($tblSubjectTable = $this->getSubjectTableById($subjectTableId))) {
                    (new Data($this->getBinding()))->createSubjectTableLink($tblSubjectTableLink->getLinkId(), $Data['MinCount'], $tblSubjectTable);
                }
            }
        }

        return true;
    }

    /**
     * @param TblSubjectTableLink $tblSubjectTableLink
     *
     * @return bool
     */
    public function destroySubjectTableLink(TblSubjectTableLink $tblSubjectTableLink): bool
    {
        if (($tblSubjectTableLinkList = DivisionCourse::useService()->getSubjectTableLinkListByLinkId($tblSubjectTableLink->getLinkId()))) {
            foreach ($tblSubjectTableLinkList as $item) {
                (new Data($this->getBinding()))->destroySubjectTableLink($item);
            }
        }

        return true;
    }
}