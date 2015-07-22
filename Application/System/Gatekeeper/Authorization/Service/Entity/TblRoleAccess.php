<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblRoleAccess")
 */
class TblRoleAccess extends Element
{

    const ATTR_TBL_ROLE = 'tblRole';
    const ATTR_TBL_ACCESS = 'tblAccess';

    /**
     * @Column(type="bigint")
     */
    protected $tblRole;
    /**
     * @Column(type="bigint")
     */
    protected $tblAccess;

    /**
     * @return bool|TblRole
     */
    public function getTblRole()
    {

        if (null === $this->tblRole) {
            return false;
        } else {
            return Authorization::useService()->getRoleById( $this->tblRole );
        }
    }

    /**
     * @param null|TblRole $tblRole
     */
    public function setTblRole( TblRole $tblRole = null )
    {

        $this->tblRole = ( null === $tblRole ? null : $tblRole->getId() );
    }

    /**
     * @return bool|TblAccess
     */
    public function getTblAccess()
    {

        if (null === $this->tblAccess) {
            return false;
        } else {
            return Authorization::useService()->getAccessById( $this->tblAccess );
        }
    }

    /**
     * @param null|TblAccess $tblAccess
     */
    public function setTblAccess( TblAccess $tblAccess = null )
    {

        $this->tblAccess = ( null === $tblAccess ? null : $tblAccess->getId() );
    }
}
