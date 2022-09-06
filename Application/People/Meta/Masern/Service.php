<?php
namespace SPHERE\Application\People\Meta\Masern;

use DateTime;
use SPHERE\Application\People\Meta\Masern\Service\Data;
use SPHERE\Application\People\Meta\Masern\Service\Setup;
use SPHERE\Application\People\Meta\Masern\Service\Entity\TblPersonMasern;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Masern
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
     * @return false|TblPersonMasern
     */
    public function getPersonMasernByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPersonMasernByPerson($tblPerson);
    }

    /**
     * @param TblPerson                 $tblPerson
     * @param DateTime|null             $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return TblPersonMasern
     */
    public function createPersonMasern(
        TblPerson $tblPerson,
        DateTime $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        return (new Data($this->getBinding()))->createPersonMasern($tblPerson, $MasernDate, $MasernDocumentType, $MasernCreatorType);
    }

    /**
     * @param TblPersonMasern           $tblPersonMasern
     * @param TblPerson                 $tblPerson
     * @param DateTime|null             $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return bool
     */
    public function updatePersonMasern(
        TblPersonMasern $tblPersonMasern,
        TblPerson $tblPerson,
        DateTime $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        return (new Data($this->getBinding()))->updatePersonMasern($tblPersonMasern, $tblPerson, $MasernDate, $MasernDocumentType, $MasernCreatorType);
    }
}
