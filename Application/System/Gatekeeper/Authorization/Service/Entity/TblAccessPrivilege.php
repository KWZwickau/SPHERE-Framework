<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAccessPrivilege")
 */
class TblAccessPrivilege extends Element
{

    const ATTR_TBL_ACCESS = 'tblAccess';
    const ATTR_TBL_PRIVILEGE = 'tblPrivilege';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccess;
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
            return Authorization::useService()->getPrivilegeById( $this->tblPrivilege );
        }
    }

    /**
     * @param null|TblPrivilege $tblPrivilege
     */
    public function setTblPrivilege( TblPrivilege $tblPrivilege = null )
    {

        $this->tblPrivilege = ( null === $tblPrivilege ? null : $tblPrivilege->getId() );
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
