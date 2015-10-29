<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\People\Meta\Common\Service;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Common
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Common extends Service
{

    /**
     * @param TblPerson $tblPerson
     * @param           $Birthday
     * @param           $Birthplace
     * @param           $Denomination
     * @param int       $Gender
     * @param string    $Nationality
     * @param int       $IsAssistance
     * @param string    $AssistanceActivity
     * @param string    $Remark
     */
    public function createMetaFromImport(
        TblPerson $tblPerson,
        $Birthday,
        $Birthplace,
        $Denomination,
        $Gender = 0,
        $Nationality = '',
        $IsAssistance = 0,
        $AssistanceActivity = '',
        $Remark = ''
    ) {

        $this->insertMeta($tblPerson, $Birthday, $Birthplace, $Gender, $Nationality, $Denomination, $IsAssistance,
            $AssistanceActivity, $Remark);
    }

}
