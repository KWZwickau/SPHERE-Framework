<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Corporation\Company\Service\Data;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Setup;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Corporation\Company
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
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countCompanyAllByGroup(TblGroup $tblGroup)
    {

        return Group::useService()->countMemberByGroup($tblGroup);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getCompanyById($Id);
    }

    /**
     * @param string $Name
     * @param string $ExtendedName
     *
     * @return bool|TblCompany
     */
    public function getCompanyByName($Name, $ExtendedName)
    {

        return ( new Data($this->getBinding()) )->getCompanyByName($Name, $ExtendedName);
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return (new Data($this->getBinding()))->getCompanyAll();
    }

    /**
     * @param $Name
     *
     * @return false|TblCompany[]
     */
    public function getCompanyListLike($Name)
    {

        return (new Data($this->getBinding()))->getCompanyListLike($Name);
    }

    /** @deprecated ist wohl nach dem Import nicht immer eindeutig
     * @param integer $ImportId
     *
     * @return bool|TblCompany
     */
    public function getCompanyByImportId($ImportId)
    {
        return (new Data($this->getBinding()))->getCompanyByImportId($ImportId);
    }

    /**
     * @param integer $ImportId
     *
     * @return bool|TblCompany[]
     */
    public function getCompanyListByImportId($ImportId)
    {
        return (new Data($this->getBinding()))->getCompanyListByImportId($ImportId);
    }

    /**
     * @param array $FilterList
     *
     * @return
     */
    public function fetchIpCompanyByFilter(array $FilterList = array())
    {
        return (new Data($this->getBinding()))->fetchIpCompanyByFilter($FilterList);
    }

    /**
     * @param $Company
     *
     * @return bool|TblCompany
     */
    public function createCompanyService($Company)
    {
        if (($tblCompany = (new Data($this->getBinding()))->createCompany($Company['Name'],
            $Company['ExtendedName'],
            $Company['Description']))
        ) {
            // Add to Group
            if (isset($Company['Group'])) {
                foreach ((array)$Company['Group'] as $tblGroup) {
                    Group::useService()->addGroupCompany(
                        Group::useService()->getGroupById($tblGroup), $tblCompany
                    );
                }
            }

            return $tblCompany;
        } else {
            return false;
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $ExtendedName
     * @param string $ImportId
     *
     * @return TblCompany
     */
    public function insertCompany($Name, $Description = '', $ExtendedName = '', $ImportId = '', $ContactNumber = '')
    {

        return (new Data($this->getBinding()))->createCompany($Name, $ExtendedName, $Description, $ImportId, $ContactNumber);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Company
     *
     * @return bool
     */
    public function updateCompanyService(TblCompany $tblCompany, $Company)
    {

        if(!isset($Company['ContactNumber'])){
            $Company['ContactNumber'] = $tblCompany->getContactNumber();
        }

        if ((new Data($this->getBinding()))->updateCompany($tblCompany, $Company['Name'],
            $Company['ExtendedName'], $Company['Description'], $Company['ContactNumber'])
        ) {
            // Change Groups
            if (isset($Company['Group'])) {
                // Remove all Groups
                $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                foreach ($tblGroupList as $tblGroup) {
                    Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                }
                // Add current Groups
                foreach ((array)$Company['Group'] as $tblGroup) {
                    Group::useService()->addGroupCompany(
                        Group::useService()->getGroupById($tblGroup), $tblCompany
                    );
                }
            } else {
                // Remove all Groups
                $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                foreach ($tblGroupList as $tblGroup) {
                    Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     * @param            $Name
     * @param string     $ExtendedName
     * @param string     $Description
     * @param string     $Contactnumber
     *
     * @return bool
     */
    public function updateCompanyWithoutForm(TblCompany $tblCompany, $Name, $ExtendedName = '', $Description = '', $Contactnumber = '')
    {

        if(!$Contactnumber){
            $Contactnumber = $tblCompany->getContactNumber();
        }

        return (new Data($this->getBinding()))->updateCompany($tblCompany, $Name, $ExtendedName, $Description, $Contactnumber);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $ImportId
     *
     * @return bool
     */
    public function updateCompanyImportId(
        TblCompany $tblCompany,
        $ImportId
    ) {
        return (new Data($this->getBinding()))->updateCompanyImportId($tblCompany, $ImportId);
    }

    /** @deprecated
     * @param TblCompany $tblCompany
     * @param $Description
     *
     * @return bool
     */
    public function updateCompanyDescriptionWithoutForm(TblCompany $tblCompany, $Description = '')
    {

        return (new Data($this->getBinding()))->updateCompanyDescriptionWithoutForm($tblCompany, $Description);
    }

    /**
     * @param array  $ProcessList
     *
     * @return bool
     */
    public function updateCompanyAnonymousBulk(
        $ProcessList = array()
    ) {

        return (new Data($this->getBinding()))->updateCompanyAnonymousBulk($ProcessList);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeCompany(TblCompany $tblCompany)
    {

        if(($tblMemberList = Group::useService()->getMemberAllByCompany($tblCompany))){
            foreach($tblMemberList as $tblMember)
                Group::useService()->removeMember($tblMember);
        }
        return (new Data($this->getBinding()))->removeCompany($tblCompany);
    }
}
