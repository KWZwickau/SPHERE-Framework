<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToken")
 * @Cache(usage="READ_ONLY")
 */
class TblToken extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_SERIAL = 'Serial';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="string")
     */
    protected $Serial;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

    /**
     * @param string $Identifier
     */
    public function __construct($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getSerial()
    {

        if ($this->Serial === null) {
            return null;
        }
        return str_pad($this->Serial, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $Serial
     */
    public function setSerial($Serial)
    {

        $this->Serial = $Serial;
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

    /**
     * @return bool|TblAccount[]
     */
    public function getAccountAllByToken()
    {

        return Account::useService()->getAccountAllByToken($this);
    }
}
