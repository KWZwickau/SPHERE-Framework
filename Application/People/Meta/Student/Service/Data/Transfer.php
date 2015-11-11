<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
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

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentTransfer',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferType
     */
    public function getStudentTransferTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransferType',
            $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudent
     */
    public function getStudentTransferTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransferType', array(
                TblStudentTransferType::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }

    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferByType(TblStudent $tblStudent, TblStudentTransferType $tblStudentTransferType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransfer', array(
                TblStudentTransfer::ATTR_TBL_STUDENT       => $tblStudent->getId(),
                TblStudentTransfer::ATTR_TBL_TRANSFER_TYPE => $tblStudentTransferType->getId()
            ));
    }
}
