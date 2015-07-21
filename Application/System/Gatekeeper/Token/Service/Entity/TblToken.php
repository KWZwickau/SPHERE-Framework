<?php
namespace SPHERE\Application\System\Gatekeeper\Token\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Object;

/**
 * @Entity
 * @Table(name="tblToken")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblToken extends Object
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_SERVICE_GATEKEEPER_CONSUMER = 'serviceGatekeeper_Consumer';

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
    protected $serviceGatekeeper_Consumer;

    /**
     * @param string $Identifier
     */
    function __construct( $Identifier )
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
    public function setIdentifier( $Identifier )
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
        return str_pad( $this->Serial, 8, '0', STR_PAD_LEFT );
    }

    /**
     * @param string $Serial
     */
    public function setSerial( $Serial )
    {

        $this->Serial = $Serial;
    }

    /**
     * @return bool|TblConsumer
     */
    public function getServiceGatekeeperConsumer()
    {

        if (null === $this->serviceGatekeeper_Consumer) {
            return false;
        } else {
            return Gatekeeper::serviceConsumer()->entityConsumerById( $this->serviceGatekeeper_Consumer );
        }
    }

    /**
     * @param null|TblConsumer $tblConsumer
     */
    public function setServiceGatekeeperConsumer( TblConsumer $tblConsumer = null )
    {

        $this->serviceGatekeeper_Consumer = ( null === $tblConsumer ? null : $tblConsumer->getId() );
    }
}
