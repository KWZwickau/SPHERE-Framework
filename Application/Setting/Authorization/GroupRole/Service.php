<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Data;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Entity\TblGroupRole;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Entity\TblGroupRoleLink;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Setup;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Authorization\GroupRole
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
     * @param $Id
     *
     * @return false|TblGroupRole
     */
    public function getGroupRoleById($Id)
    {
        return (new Data($this->getBinding()))->getGroupRoleById($Id);
    }

    /**
     * @return false|TblGroupRole[]
     */
    public function getGroupRoleAll()
    {
        return (new Data($this->getBinding()))->getGroupRoleAll();
    }

    /**
     * @param TblGroupRole $tblGroupRole
     *
     * @return false|TblGroupRoleLink[]
     */
    public function getGroupRoleLinkAllByGroupRole(TblGroupRole $tblGroupRole)
    {
        return (new Data($this->getBinding()))->getGroupRoleLinkAllByGroupRole($tblGroupRole);
    }

    /**
     * @param $Data
     * @param TblGroupRole|null $tblGroupRole
     *
     * @return false|Form
     */
    public function checkFormGroupRole(
        $Data,
        TblGroupRole $tblGroupRole = null
    ) {
        $error = false;

        $form = GroupRole::useFrontend()->formGroupRole($tblGroupRole ? $tblGroupRole->getId() : null);
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        } else {
            $form->setSuccess('Data[Name]');
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     *
     * @return bool
     */
    public function createGroupRole($Data)
    {
        if (($tblGroupRole = (new Data($this->getBinding()))->createGroupRole($Data['Name']))) {
            if (isset($Data['Role'])) {
                foreach ($Data['Role'] as $roleId => $value) {
                    if (($tblRole = Access::useService()->getRoleById($roleId))) {
                        (new Data($this->getBinding()))->addGroupRoleLink($tblGroupRole, $tblRole);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblGroupRole $tblGroupRole
     * @param $Data
     *
     * @return bool
     */
    public function updateGroupRole(TblGroupRole $tblGroupRole, $Data)
    {
        (new Data($this->getBinding()))->updateGroupRole($tblGroupRole, $Data['Name']);

        if (($tblGroupRoleLinkList = $this->getGroupRoleLinkAllByGroupRole($tblGroupRole))) {
            foreach ($tblGroupRoleLinkList as $tblGroupRoleLink) {
                if (($tblRole = $tblGroupRoleLink->getServiceTblRole())
                    && !isset($Data['Role'][$tblRole->getId()])
                ) {
                    (new Data($this->getBinding()))->removeGroupRoleLink($tblGroupRole, $tblRole);
                }
            }
        }

        if (isset($Data['Role'])) {
            foreach ($Data['Role'] as $roleId => $value) {
                if (($tblRole = Access::useService()->getRoleById($roleId))) {
                    (new Data($this->getBinding()))->addGroupRoleLink($tblGroupRole, $tblRole);
                }
            }
        }

        return true;
    }

    /**
     * @param TblGroupRole $tblGroupRole
     *
     * @return bool
     */
    public function destroyGroupRole(TblGroupRole $tblGroupRole)
    {
        if (($tblGroupRoleLinkList = $this->getGroupRoleLinkAllByGroupRole($tblGroupRole))) {
            foreach ($tblGroupRoleLinkList as $tblGroupRoleLink) {
                if (($tblRole = $tblGroupRoleLink->getServiceTblRole())) {
                    (new Data($this->getBinding()))->removeGroupRoleLink($tblGroupRole, $tblRole);
                }
            }
        }

        return (new Data($this->getBinding()))->removeGroupRole($tblGroupRole);
    }
}