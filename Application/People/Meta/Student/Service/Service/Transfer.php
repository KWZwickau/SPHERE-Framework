<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Transfer extends Agreement
{

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferById($Id);
    }

    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferByType(TblStudent $tblStudent, TblStudentTransferType $tblStudentTransferType)
    {

        return (new Data($this->getBinding()))->getStudentTransferByType($tblStudent, $tblStudentTransferType);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentTransferType
     */
    public function getStudentTransferTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getStudentTransferTypeByIdentifier($Identifier);
    }
}
