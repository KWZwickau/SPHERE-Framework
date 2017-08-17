<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.08.2017
 * Time: 16:27
 */

namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblRoleConsumer")
 * @Cache(usage="READ_ONLY")
 */
class TblRoleConsumer extends Element
{

    const ATTR_TBL_ROLE = 'tblRole';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';

    /**
     * @Column(type="bigint")
     */
    protected $tblRole;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

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
     * @return bool|TblConsumer
     */
    public function getServiceTblConsumer()
    {

        if (null === $this->serviceTblConsumer) {
            return false;
        } else {
            return Consumer::useService()->getConsumerById($this->serviceTblConsumer);
        }
    }

    /**
     * @param null|TblConsumer $tblConsumer
     */
    public function setServiceTblConsumer(TblConsumer $tblConsumer = null)
    {

        $this->serviceTblConsumer = ( null === $tblConsumer ? null : $tblConsumer->getId() );
    }
}