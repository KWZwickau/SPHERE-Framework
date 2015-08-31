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
 * @Table(name="tblRoleLevel")
 * @Cache(usage="READ_ONLY")
 */
class TblRoleLevel extends Element
{

    const ATTR_TBL_ROLE = 'tblRole';
    const ATTR_TBL_LEVEL = 'tblLevel';

    /**
     * @Column(type="bigint")
     */
    protected $tblRole;
    /**
     * @Column(type="bigint")
     */
    protected $tblLevel;

    /**
     * @return bool|TblRole
     */
    public function getTblRole()
    {

        if (null === $this->tblRole) {
            return false;
        } else {
            return Access::useService()->getRoleById($this->tblRole);
        }
    }

    /**
     * @param null|TblRole $tblRole
     */
    public function setTblRole(TblRole $tblRole = null)
    {

        $this->tblRole = ( null === $tblRole ? null : $tblRole->getId() );
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
