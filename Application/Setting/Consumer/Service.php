<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Consumer\Service\Data;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Application\Setting\Consumer\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Consumer
 */
class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Cluster
     * @param $Application
     * @param null $Module
     * @param $Identifier
     * @return false|TblSetting
     */
    public function getSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier
    ) {

        return (new Data($this->getBinding()))->getSetting(
            $Cluster, $Application, $Module, $Identifier
        );
    }

    /**
     * @param TblAccount $tblAccountStudent
     *
     * @return false|TblStudentCustody[]
     */
    public function getStudentCustodyByStudent(TblAccount $tblAccountStudent)
    {

        return (new Data($this->getBinding()))->getStudentCustodyByStudent($tblAccountStudent);
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyByStudentAndCustody(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody)
    {

        return (new Data($this->getBinding()))->getStudentCustodyByStudentAndCustody($tblAccountStudent,
            $tblAccountCustody);
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     * @param TblAccount $tblAccountBlocker
     *
     * @return false|TblStudentCustody
     */
    public function createStudentCustody(
        TblAccount $tblAccountStudent,
        TblAccount $tblAccountCustody,
        TblAccount $tblAccountBlocker
    ) {

        return (new Data($this->getBinding()))->createStudentCustody($tblAccountStudent, $tblAccountCustody,
            $tblAccountBlocker);
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeStudentCustody(TblStudentCustody $tblStudentCustody)
    {

        return (new Data($this->getBinding()))->removeStudentCustody($tblStudentCustody);
    }
}