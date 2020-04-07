<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblConsumerLogin")
 * @Cache(usage="READ_ONLY")
 */
class TblConsumerLogin extends Element
{

    const ATTR_SYSTEM_NAME = 'SystemName';
    const ATTR_TBL_CONSUMER = 'tblConsumer';

    const VALUE_SYSTEM_NAME = 'Univention';

    /**
     * @Column(type="string")
     */
    protected $SystemName;

    /**
     * @Column(type="string")
     */
    protected $tblConsumer;

    /**
     * @return string
     */
    public function getSystemName()
    {

        return $this->SystemName;
    }

    /**
     * @param string $SystemName
     */
    public function setSystemName($SystemName)
    {

        $this->SystemName = $SystemName;
    }

    /**
     * @return bool|TblConsumer
     */
    public function getTblConsumer()
    {

        if(null === $this->tblConsumer){
            return false;
        } else {
            return Consumer::useService()->getConsumerById($this->tblConsumer);
        }
    }

    /**
     * @param TblConsumer $tblConsumer
     */
    public function setTblConsumer(TblConsumer $tblConsumer)
    {

        $this->tblConsumer = (null === $tblConsumer ? null : $tblConsumer->getId());
    }
}
