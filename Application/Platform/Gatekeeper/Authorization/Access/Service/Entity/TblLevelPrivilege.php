<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLevelPrivilege")
 * @Cache(usage="READ_ONLY")
 */
class TblLevelPrivilege extends Element
{

    const ATTR_TBL_LEVEL = 'tblLevel';
    const ATTR_TBL_PRIVILEGE = 'tblPrivilege';

    /**
     * @Column(type="bigint")
     */
    protected $tblLevel;
    /**
     * @Column(type="bigint")
     */
    protected $tblPrivilege;

    /**
     * @return bool|TblPrivilege
     */
    public function getTblPrivilege()
    {

        if (null === $this->tblPrivilege) {
            return false;
        } else {
            return Access::useService()->getPrivilegeById($this->tblPrivilege);
        }
    }

    /**
     * @param null|TblPrivilege $tblPrivilege
     */
    public function setTblPrivilege(TblPrivilege $tblPrivilege = null)
    {

        $this->tblPrivilege = ( null === $tblPrivilege ? null : $tblPrivilege->getId() );
    }

    /**
     * @return bool|TblLevel
     */
    public function getTblLevel()
    {

        if (null === $this->tblLevel) {
            return false;
        } else {
            return Access::useService()->getLevelById($this->tblLevel);
        }
    }

    /**
     * @param null|TblLevel $tblLevel
     */
    public function setTblLevel(TblLevel $tblLevel = null)
    {

        $this->tblLevel = ( null === $tblLevel ? null : $tblLevel->getId() );
    }
}
