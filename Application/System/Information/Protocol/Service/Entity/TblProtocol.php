<?php
namespace SPHERE\Application\System\Information\Protocol\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Protocol\Activity\Service\Entity\Gatekeeper;
use SPHERE\Application\System\Protocol\Activity\Service\Entity\Management;
use SPHERE\Application\System\Protocol\Activity\Service\Entity\TblAccount;
use SPHERE\Application\System\Protocol\Activity\Service\Entity\TblConsumer;
use SPHERE\Application\System\Protocol\Activity\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Object;

/**
 * @Entity
 * @Table(name="tblProtocol")
 * @Cache(usage="READ_WRITE")
 */
class TblProtocol extends Object
{

    /**
     * @Column(type="string")
     */
    protected $ProtocolDatabase;
    /**
     * @Column(type="integer")
     */
    protected $ProtocolTimestamp;
    /**
     * @Column(type="bigint")
     */
    protected $serviceGatekeeper_Account;
    /**
     * @Column(type="string")
     */
    protected $AccountUsername;
    /**
     * @Column(type="bigint")
     */
    protected $serviceManagement_Person;
    /**
     * @Column(type="string")
     */
    protected $PersonFirstName;
    /**
     * @Column(type="string")
     */
    protected $PersonLastName;
    /**
     * @Column(type="bigint")
     */
    protected $serviceGatekeeper_Consumer;
    /**
     * @Column(type="string")
     */
    protected $ConsumerName;
    /**
     * @Column(type="string")
     */
    protected $ConsumerSuffix;
    /**
     * @Column(type="text")
     */
    protected $EntityFrom;
    /**
     * @Column(type="text")
     */
    protected $EntityTo;

    /**
     * @return string
     */
    public function getProtocolDatabase()
    {

        return $this->ProtocolDatabase;
    }

    /**
     * @param string $ProtocolDatabase
     */
    public function setProtocolDatabase( $ProtocolDatabase )
    {

        $this->ProtocolDatabase = $ProtocolDatabase;
    }

    /**
     * @return integer
     */
    public function getProtocolTimestamp()
    {

        return $this->ProtocolTimestamp;
    }

    /**
     * @param integer $ProtocolTimestamp
     */
    public function setProtocolTimestamp( $ProtocolTimestamp )
    {

        $this->ProtocolTimestamp = $ProtocolTimestamp;
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceGatekeeperAccount()
    {

        if (null === $this->serviceGatekeeper_Account) {
            return false;
        } else {
            return Gatekeeper::serviceAccount()->entityAccountById( $this->serviceGatekeeper_Account );
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setServiceGatekeeperAccount( TblAccount $tblAccount = null )
    {

        $this->serviceGatekeeper_Account = ( null === $tblAccount ? null : $tblAccount->getId() );
    }

    /**
     * @return string
     */
    public function getAccountUsername()
    {

        return $this->AccountUsername;
    }

    /**
     * @param string $AccountUsername
     */
    public function setAccountUsername( $AccountUsername )
    {

        $this->AccountUsername = $AccountUsername;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceManagementPerson()
    {

        if (null === $this->serviceManagement_Person) {
            return false;
        } else {
            return Management::servicePerson()->entityPersonById( $this->serviceManagement_Person );
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceManagementPerson( TblPerson $tblPerson = null )
    {

        $this->serviceManagement_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getPersonFirstName()
    {

        return $this->PersonFirstName;
    }

    /**
     * @param string $PersonFirstName
     */
    public function setPersonFirstName( $PersonFirstName )
    {

        $this->PersonFirstName = $PersonFirstName;
    }

    /**
     * @return string
     */
    public function getPersonLastName()
    {

        return $this->PersonLastName;
    }

    /**
     * @param string $PersonLastName
     */
    public function setPersonLastName( $PersonLastName )
    {

        $this->PersonLastName = $PersonLastName;
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

    /**
     * @return string
     */
    public function getConsumerName()
    {

        return $this->ConsumerName;
    }

    /**
     * @param string $ConsumerName
     */
    public function setConsumerName( $ConsumerName )
    {

        $this->ConsumerName = $ConsumerName;
    }

    /**
     * @return string
     */
    public function getConsumerSuffix()
    {

        return $this->ConsumerSuffix;
    }

    /**
     * @param string $ConsumerSuffix
     */
    public function setConsumerSuffix( $ConsumerSuffix )
    {

        $this->ConsumerSuffix = $ConsumerSuffix;
    }

    /**
     * @return string
     */
    public function getEntityFrom()
    {

        return $this->EntityFrom;
    }

    /**
     * @param string $EntityFrom
     */
    public function setEntityFrom( $EntityFrom )
    {

        $this->EntityFrom = $EntityFrom;
    }

    /**
     * @return string
     */
    public function getEntityTo()
    {

        return $this->EntityTo;
    }

    /**
     * @param string $EntityTo
     */
    public function setEntityTo( $EntityTo )
    {

        $this->EntityTo = $EntityTo;
    }
}
