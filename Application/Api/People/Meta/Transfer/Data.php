<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Data as DataAPP;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

class Data extends DataAPP
{
    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     * @param TblCompany|null        $tblCompany
     * @param TblType|null           $tblType
     * @param TblCourse|null         $tblCourse
     * @param                        $TransferDate
     * @param                        $Remark
     *
     * @return TblStudentTransfer
     * Create or Update
     */
    public function createStudentTransfer(
        TblStudent $tblStudent,
        TblStudentTransferType $tblStudentTransferType,
        TblCompany $tblCompany = null,
        TblType $tblType = null,
        TblCourse $tblCourse = null,
        $TransferDate = null,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentTransfer $Entity */
        $Entity = $Manager->getEntity('TblStudentTransfer')->findOneBy(array(
            TblStudentTransfer::ATTR_TBL_STUDENT       => $tblStudent->getId(),
            TblStudentTransfer::ATTR_TBL_TRANSFER_TYPE => $tblStudentTransferType->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblStudentTransfer();

            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentTransferType($tblStudentTransferType);
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setServiceTblType($tblType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setTransferDate(($TransferDate ? new \DateTime($TransferDate) : null));
            $Entity->setRemark($Remark);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;

            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentTransferType($tblStudentTransferType);
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setServiceTblType($tblType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setTransferDate(($TransferDate ? new \DateTime($TransferDate) : null));
            $Entity->setRemark($Remark);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}