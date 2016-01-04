<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
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

    /**
     * @param $Id
     *
     * @return bool|TblStudentTransferType
     */
    public function getStudentTransferTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferTypeById($Id);
    }

    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     * @param TblCompany|null        $tblCompany
     * @param TblType|null           $tblType
     * @param TblCourse|null         $tblCourse
     * @param                        $TransferDate
     *
     * @param                        $Remark
     */
    public function insertStudentTransfer(
        TblStudent $tblStudent,
        TblStudentTransferType $tblStudentTransferType,
        TblCompany $tblCompany = null,
        TblType $tblType = null,
        TblCourse $tblCourse = null,
        $TransferDate,
        $Remark
    ) {

        $tblStudentTransfer = $this->getStudentTransferByType(
            $tblStudent,
            $tblStudentTransferType
        );
        if ($tblStudentTransfer) {
            (new Data($this->getBinding()))->updateStudentTransfer(
                $tblStudentTransfer,
                $tblStudent,
                $tblStudentTransferType,
                $tblCompany,
                $tblType,
                $tblCourse,
                $TransferDate,
                $Remark
            );
        } else {
            (new Data($this->getBinding()))->createStudentTransfer(
                $tblStudent,
                $tblStudentTransferType,
                $tblCompany,
                $tblType,
                $tblCourse,
                $TransferDate,
                $Remark
            );
        }
    }

}
