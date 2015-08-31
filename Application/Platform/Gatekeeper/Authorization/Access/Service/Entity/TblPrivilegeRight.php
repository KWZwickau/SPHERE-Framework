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
 * @Table(name="tblPrivilegeRight")
 * @Cache(usage="READ_ONLY")
 */
class TblPrivilegeRight extends Element
{

    const ATTR_TBL_PRIVILEGE = 'tblPrivilege';
    const ATTR_TBL_RIGHT = 'tblRight';

    /**
     * @Column(type="bigint")
     */
    protected $tblPrivilege;
    /**
     * @Column(type="bigint")
     */
    protected $tblRight;

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
     * @return bool|TblRight
     */
    public function getTblRight()
    {

        if (null === $this->tblRight) {
            return false;
        } else {
            return Access::useService()->getRightById($this->tblRight);
        }
    }

    /**
     * @param null|TblRight $tblRight
     */
    public function setTblRight(TblRight $tblRight = null)
    {

        $this->tblRight = ( null === $tblRight ? null : $tblRight->getId() );
    }
}
