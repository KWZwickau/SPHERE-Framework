<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceGradeType extends AbstractService
{
    /**
     * @param $id
     *
     * @return false|TblGradeType
     */
    public function getGradeTypeById($id)
    {
        return (new Data($this->getBinding()))->getGradeTypeById($id);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByCode(string $Code)
    {
        return (new Data($this->getBinding()))->getGradeTypeByCode($Code);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll(bool $withInActive = false)
    {
        return (new Data($this->getBinding()))->getGradeTypeAll($withInActive);
    }

    /**
     * @param bool $isTypeBehavior
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeList(bool $isTypeBehavior = false)
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
     * @param IFormInterface|null $form
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function createGradeType(IFormInterface $form = null, $GradeType)
    {
        /**
         * Skip to Frontend
         */
        if (null === $GradeType) {
            return $form;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $form->setError('GradeType[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $form->setError('GradeType[Code]', 'Bitte geben Sie eine Abk&uuml;rzung an');
            $Error = true;
        }
        if (!isset($GradeType['Type'])) {
            $form->setError('GradeType[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createGradeType(
                $GradeType['Code'],
                $GradeType['Name'],
                $GradeType['Description'],
                $GradeType['Type'] == 2,
                isset($GradeType['IsHighlighted']),
                isset($GradeType['IsPartGrade']),
                true
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfasst worden')
                . new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param IFormInterface|null $form
     * @param                     $Id
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function updateGradeType(IFormInterface $form = null, $Id, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType || null === $Id) {
            return $form;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $form->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $form->setError('GradeType[Code]', 'Bitte geben sie eine Abkürzung an');
            $Error = true;
        }
        if (!isset($GradeType['Type'])) {
            $form->setError('GradeType[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }

        $tblGradeType = $this->getGradeTypeById($Id);
        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateGradeType(
                $tblGradeType,
                $GradeType['Code'],
                $GradeType['Name'],
                $GradeType['Description'],
                $GradeType['Type'] == 2,
                isset($GradeType['IsHighlighted']),
                isset($GradeType['IsPartGrade']),
                $tblGradeType->getIsActive()
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfolgreich gespeichert worden')
                . new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
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
            $IsActive
        );
    }
}