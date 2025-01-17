<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;

/**
 * Class Agreement
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Agreement extends Liberation
{

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentAgreement[]
     */
    public function getStudentAgreementAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentAgreementAllByStudent($tblStudent);
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return false|TblStudentAgreement[]
     */
    public function getStudentAgreementAllByType(TblStudentAgreementType $tblStudentAgreementType)
    {

        return (new Data($this->getBinding()))->getStudentAgreementAllByType($tblStudentAgreementType);
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     * @param TblStudent $tblStudent
     *
     * @return false|TblStudentAgreement
     */
    public function getStudentAgreementByTypeAndStudent(
        TblStudentAgreementType $tblStudentAgreementType,
        TblStudent $tblStudent
    ) {

        return (new Data($this->getBinding()))->getStudentAgreementByTypeAndStudent($tblStudentAgreementType, $tblStudent);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreement
     */
    public function getStudentAgreementById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementType
     */
    public function getStudentAgreementTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeById($Id);
    }

    /**
     * @param string                      $Name
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return false|TblStudentAgreementType
     */
    public function getStudentAgreementTypeByNameAndCategory($Name, TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeByNameAndCategory($Name, $tblStudentAgreementCategory);
    }

    /**
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeAll();
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAllByCategory(TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementCategory
     */
    public function getStudentAgreementCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementCategoryById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblStudentAgreementCategory
     */
    public function getStudentAgreementCategoryByName($Name)
    {

        return (new Data($this->getBinding()))->getStudentAgreementCategoryByName($Name);
    }

    /**
     * @return bool|TblStudentAgreementCategory[]
     */
    public function getStudentAgreementCategoryAll()
    {

        return (new Data($this->getBinding()))->getStudentAgreementCategoryAll();
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentAgreementCategory
     */
    public function createStudentAgreementCategory($Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createStudentAgreementCategory($Name, $Description);
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return TblStudentAgreementType
     */
    public function createStudentAgreementType(TblStudentAgreementCategory $tblStudentAgreementCategory, $Name, $Description = '', $isUnlocked = false)
    {

        return (new Data($this->getBinding()))->createStudentAgreementType($tblStudentAgreementCategory, $Name, $Description, $isUnlocked);
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return bool
     */
    public function updateStudentAgreementCategory(TblStudentAgreementCategory $tblStudentAgreementCategory, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->updateStudentAgreementCategory($tblStudentAgreementCategory, $Name, $Description);
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     * @param string                  $Name
     * @param string                  $Description
     * @param bool                    $isUnlocked
     *
     * @return bool
     */
    public function updateStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType, $Name, $Description = '', $isUnlocked = false)
    {

        return (new Data($this->getBinding()))->updateStudentAgreementType($tblStudentAgreementType, $Name, $Description, $isUnlocked);
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return bool
     */
    public function destroyStudentAgreementCategory(TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        if(($tblStudentAgreementTypeList = $this->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))){
            foreach($tblStudentAgreementTypeList as $tblStudentAgreementType){
                $this->destroyStudentAgreementType($tblStudentAgreementType);
            }
        }
        return (new Data($this->getBinding()))->destroyStudentAgreementCategory($tblStudentAgreementCategory);
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return bool
     */
    public function destroyStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType)
    {

        if(($tblStudentAgreementList = $this->getStudentAgreementAllByType($tblStudentAgreementType))){
            foreach($tblStudentAgreementList as $tblStudentAgreement){
                $this->removeStudentAgreement($tblStudentAgreement);
            }
        }
        return (new Data($this->getBinding()))->destroyStudentAgreementType($tblStudentAgreementType);
    }

    /**
     * @param TblStudentAgreement $tblStudentAgreement
     *
     * @return bool
     */
    public function removeStudentAgreement(TblStudentAgreement $tblStudentAgreement)
    {

        return (new Data($this->getBinding()))->removeStudentAgreement($tblStudentAgreement);
    }
}
