<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\Contact\Phone\Service;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Phone
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Phone extends Service
{

    /**
     * @param TblPerson              $tblPerson
     * @param                        $Number
     * @param Service\Entity\TblType $tblType
     *
     * @param string                 $Remark
     */
    public function createPhoneToPersonFromImport(
        TblPerson $tblPerson,
        $Number,
        Service\Entity\TblType $tblType,
        $Remark = ''
    ) {

        $this->insertPhoneToPerson($tblPerson, $Number, $tblType, $Remark);
    }
}
