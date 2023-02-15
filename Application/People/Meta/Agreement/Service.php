<?php
namespace SPHERE\Application\People\Meta\Agreement;

use SPHERE\Application\People\Meta\Agreement\Service\Data;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreement;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementCategory;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementType;
use SPHERE\Application\People\Meta\Agreement\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Agreement
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblPersonAgreement[]
     */
    public function getPersonAgreementAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPersonAgreementAllByPerson($tblPerson);
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     *
     * @return false|TblPersonAgreement[]
     */
    public function getPersonAgreementAllByType(TblPersonAgreementType $tblPersonAgreementType)
    {

        return (new Data($this->getBinding()))->getPersonAgreementAllByType($tblPersonAgreementType);
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     * @param TblPerson $tblPerson
     *
     * @return false|TblPersonAgreement
     */
    public function getPersonAgreementByTypeAndPerson(
        TblPersonAgreementType $tblPersonAgreementType,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getPersonAgreementByTypeAndPerson($tblPersonAgreementType, $tblPerson);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreement
     */
    public function getPersonAgreementById($Id)
    {

        return (new Data($this->getBinding()))->getPersonAgreementById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreementType
     */
    public function getPersonAgreementTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPersonAgreementTypeById($Id);
    }

    /**
     * @param string                     $Name
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool|TblPersonAgreementType
     */
    public function getPersonAgreementTypeByNameAndCategory($Name, TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        return (new Data($this->getBinding()))->getPersonAgreementTypeByNameAndCategory($Name, $tblPersonAgreementCategory);
    }

    /**
     * @return bool|TblPersonAgreementType[]
     */
    public function getPersonAgreementTypeAll()
    {

        return (new Data($this->getBinding()))->getPersonAgreementTypeAll();
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool|TblPersonAgreementType[]
     */
    public function getPersonAgreementTypeAllByCategory(TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        return (new Data($this->getBinding()))->getPersonAgreementTypeAllByCategory($tblPersonAgreementCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreementCategory
     */
    public function getPersonAgreementCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getPersonAgreementCategoryById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPersonAgreementCategory
     */
    public function getPersonAgreementCategoryByName($Name)
    {

        return (new Data($this->getBinding()))->getPersonAgreementCategoryByName($Name);
    }

    /**
     * @return bool|TblPersonAgreementCategory[]
     */
    public function getPersonAgreementCategoryAll()
    {

        return (new Data($this->getBinding()))->getPersonAgreementCategoryAll();
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblPersonAgreementCategory
     */
    public function createPersonAgreementCategory($Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createPersonAgreementCategory($Name, $Description);
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return TblPersonAgreementType
     */
    public function createPersonAgreementType(TblPersonAgreementCategory $tblPersonAgreementCategory, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createPersonAgreementType($tblPersonAgreementCategory, $Name, $Description);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Meta
     *
     * @return bool
     */
    public function updatePersonAgreement(TblPerson $tblPerson, $Meta)
    {

        if ($tblPerson) {
            /*
             * Agreement
             */
            $tblPersonAgreementAllByPerson = $this->getPersonAgreementAllByPerson($tblPerson);
            if ($tblPersonAgreementAllByPerson) {
                foreach ($tblPersonAgreementAllByPerson as $tblPersonAgreement) {
                    if (!isset(
                        $Meta['Agreement']
                        [$tblPersonAgreement->getTblPersonAgreementType()->getTblPersonAgreementCategory()->getId()]
                        [$tblPersonAgreement->getTblPersonAgreementType()->getId()]
                    )
                    ) {
                        (new Data($this->getBinding()))->removePersonAgreement($tblPersonAgreement);
                    }
                }
            }
            if (isset($Meta['Agreement'])) {
                foreach ($Meta['Agreement'] as $Category => $Items) {
                    $tblPersonAgreementCategory = $this->getPersonAgreementCategoryById($Category);
                    if ($tblPersonAgreementCategory) {
                        foreach ($Items as $Type => $Value) {
                            $tblPersonAgreementType = $this->getPersonAgreementTypeById($Type);
                            if ($tblPersonAgreementType) {
                                (new Data($this->getBinding()))->addPersonAgreement($tblPerson,
                                    $tblPersonAgreementType);
                            }
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return bool
     */
    public function updatePersonAgreementCategory(TblPersonAgreementCategory $tblPersonAgreementCategory, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->updatePersonAgreementCategory($tblPersonAgreementCategory, $Name, $Description);
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     * @param string                  $Name
     * @param string                  $Description
     *
     * @return bool
     */
    public function updatePersonAgreementType(TblPersonAgreementType $tblPersonAgreementType, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->updatePersonAgreementType($tblPersonAgreementType, $Name, $Description);
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool
     */
    public function destroyPersonAgreementCategory(TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        if(($tblPersonAgreementTypeList = $this->getPersonAgreementTypeAllByCategory($tblPersonAgreementCategory))){
            foreach($tblPersonAgreementTypeList as $tblPersonAgreementType){
                $this->destroyPersonAgreementType($tblPersonAgreementType);
            }
        }
        return (new Data($this->getBinding()))->destroyPersonAgreementCategory($tblPersonAgreementCategory);
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     *
     * @return bool
     */
    public function destroyPersonAgreementType(TblPersonAgreementType $tblPersonAgreementType)
    {

        if(($tblPersonAgreementList = $this->getPersonAgreementAllByType($tblPersonAgreementType))){
            foreach($tblPersonAgreementList as $tblPersonAgreement){
                $this->removePersonAgreement($tblPersonAgreement);
            }
        }
        return (new Data($this->getBinding()))->destroyPersonAgreementType($tblPersonAgreementType);
    }

    /**
     * @param TblPersonAgreement $tblPersonAgreement
     *
     * @return bool
     */
    public function removePersonAgreement(TblPersonAgreement $tblPersonAgreement)
    {

        return (new Data($this->getBinding()))->removePersonAgreement($tblPersonAgreement);
    }
}
