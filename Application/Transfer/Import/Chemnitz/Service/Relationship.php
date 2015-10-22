<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service;

/**
 * Class Relationship
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Relationship extends Service
{

    /**
     * @param TblPerson              $tblPersonFrom
     * @param TblPerson              $tblPersonTo
     * @param Service\Entity\TblType $tblType
     * @param string                 $Remark
     *
     * @return bool
     */
    public function createRelationshipToPersonFromImport(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        Service\Entity\TblType $tblType,
        $Remark = ''
    ) {

        return $this->insertRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType, $Remark);
    }
}
