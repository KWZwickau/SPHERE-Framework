<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Univention\Service\Data;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\Service\Setup;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Univention
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
     * @param string $Type
     *
     * @return false|TblUnivention
     */
    public function getUnivention($Type)
    {

        return (new Data($this->getBinding()))->getUniventionByType($Type);
    }

    /**
     * @param string $Type
     * @param string $Value
     *
     * @return TblUnivention
     */
    public function createUnivention($Type, $Value)
    {

        return (new Data($this->getBinding()))->createUnivention($Type, $Value);
    }

    /**
     * @return array|bool|TblAccount
     */
    public function getAccountAllForAPITransfer()
    {

        // Mitarbeiter / Lehrer
        $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_TOKEN);
        $tblAccountList = Account::useService()->getAccountListByIdentification($tblIdentification);

        if(!is_array($tblAccountList)){
            $tblAccountList = array();
        }

        // Student
        if($tblUserAccountList = AccountUser::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT)){
            foreach($tblUserAccountList as $tblUserAccount){
                if($tblUserAccount->getServiceTblAccount()){
                    $tblAccountList[] = $tblUserAccount->getServiceTblAccount();
                }
            }
        }
        return (!empty($tblAccountList) ? $tblAccountList : false);
    }

    /**
     * @param Element $tbl
     * @param array   $DateCompare
     */
    public function setDateList(Element $tbl, &$DateCompare)
    {

        if(($update = $tbl->getEntityUpdate())){
            $DateCompare[] = $update;
        } else {
            $DateCompare[] = $tbl->getEntityCreate();
        }
    }
}