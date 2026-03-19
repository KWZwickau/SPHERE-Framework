<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceGradeType extends AbstractService
{
    /**
     * @param $id
     *
     * @return false|TblGradeType
     */
    public function getGradeTypeById($id): false|TblGradeType
    {
        return (new Data($this->getBinding()))->getGradeTypeById($id);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByCode(string $Code): bool|TblGradeType
    {
        return (new Data($this->getBinding()))->getGradeTypeByCode($Code);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll(bool $withInActive = false): false|array
    {
        return (new Data($this->getBinding()))->getGradeTypeAll($withInActive);
    }

    /**
     * @param bool $isTypeBehavior
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeList(bool $isTypeBehavior = false): false|array
    {
        return (new Data($this->getBinding()))->getGradeTypeList($isTypeBehavior);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function getIsGradeTypeUsed(TblGradeType $tblGradeType): bool
    {
        // Notenbuch
        if ((new Data($this->getBinding()))->getIsGradeTypeUsedInGradeBook($tblGradeType)) {
            return true;
        }

        // Notenaufträge
        if ((new Data($this->getBinding()))->getIsGradeTypeUsedInTask($tblGradeType)) {
            return true;
        }

        // todo weitere prüfen Zeugniseinstellungen, Zeugnisnote
        if (Generator::useService()->isGradeTypeUsed($tblGradeType)) {
            return true;
        }

        return false;
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getIsSubjectUsedInGradeBook(TblSubject $tblSubject): bool
    {
        return (new Data($this->getBinding()))->getIsSubjectUsedInGradeBook($tblSubject);
    }

    /**
     * @param $Data
     * @param TblGradeType|null $tblGradeType
     *
     * @return Form|false
     */
    public function checkFormGradeType($Data, TblGradeType $tblGradeType = null): Form|false
    {
        $error = false;
        $form = Grade::useFrontend()->formGradeType($tblGradeType?->getId());
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }
        if (isset($Data['Code']) && empty($Data['Code'])) {
            $form->setError('Data[Code]', 'Bitte geben Sie eine Abk&uuml;rzung an');
            $error = true;
        }
        if (!isset($Data['Type'])) {
            $form->setError('Data[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param array $Data
     *
     * @return TblGradeType
     */
    public function createGradeType(array $Data): TblGradeType
    {
        return (new Data($this->getBinding()))->createGradeType(
            $Data['Code'],
            $Data['Name'],
            $Data['Description'],
            $Data['Type'] == 2,
            isset($Data['IsHighlighted']),
            isset($Data['IsPartGrade']),
            isset($Data['IsIgnoredByScoreRule']),
            true
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param $Data
     *
     * @return bool
     */
    public function updateGradeType(TblGradeType $tblGradeType, $Data): bool
    {
        return (new Data($this->getBinding()))->updateGradeType(
            $tblGradeType,
            $Data['Code'],
            $Data['Name'],
            $Data['Description'],
            $Data['Type'] == 2,
            isset($Data['IsHighlighted']),
            isset($Data['IsPartGrade']),
            isset($Data['IsIgnoredByScoreRule']),
            $tblGradeType->getIsActive()
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function deleteGradeType(TblGradeType $tblGradeType): bool
    {
        return (new Data($this->getBinding()))->deleteGradeType($tblGradeType);
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param bool $IsActive
     *
     * @return bool
     */
    public function updateGradeTypeActive(TblGradeType $tblGradeType, bool $IsActive): bool
    {
        return (new Data($this->getBinding()))->updateGradeType(
            $tblGradeType,
            $tblGradeType->getCode(),
            $tblGradeType->getName(),
            $tblGradeType->getDescription(),
            $tblGradeType->getIsTypeBehavior(),
            $tblGradeType->getIsHighlighted(),
            $tblGradeType->getIsPartGrade(),
            $tblGradeType->getIsIgnoredByScoreRule(),
            $IsActive
        );
    }
}