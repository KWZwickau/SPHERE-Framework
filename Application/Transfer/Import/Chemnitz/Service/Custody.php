<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\People\Meta\Custody\Service;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Custody
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Custody extends Service
{

    /**
     * @param TblPerson $tblPerson
     * @param           $Occupation
     * @param           $Employment
     * @param           $Remark
     */
    public function createMetaFromImport(
        TblPerson $tblPerson,
        $Occupation,
        $Employment = '',
        $Remark = ''
    ) {

        $this->insertMeta($tblPerson, $Occupation, $Employment, $Remark);
    }
}
