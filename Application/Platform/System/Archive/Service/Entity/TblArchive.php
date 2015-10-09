<?php
namespace SPHERE\Application\Platform\System\Archive\Service\Entity;

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
 * @Table(name="tblArchive")
 * @Cache(usage="READ_WRITE")
 */
class TblArchive extends Element
{

    const ARCHIVE_TYPE_CREATE = 0;
    const ARCHIVE_TYPE_UPDATE = 1;

    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';
    /**
     * @Column(type="text")
     */
    public $Entity;
    /**
     * @Column(type="integer")
     */
    protected $ArchiveType;
    /**
     * @Column(type="string")
     */
    protected $ArchiveDatabase;
    /**
     * @Column(type="integer")
     */
    protected $ArchiveTimestamp;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $AccountUsername;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;
    /**
     * @Column(type="string")
     */
    protected $ConsumerName;
    /**
     * @Column(type="string")
     */
    protected $ConsumerAcronym;

    /**
     * @param int $ArchiveType
     */
    public function __construct($ArchiveType = TblArchive::ARCHIVE_TYPE_CREATE)
    {

        $this->ArchiveType = $ArchiveType;
    }

    /**
     * @return string
     */
    public function getArchiveDatabase()
    {

        return $this->ArchiveDatabase;
    }

    /**
     * @param string $ArchiveDatabase
     */
    public function setArchiveDatabase($ArchiveDatabase)
    {

        $this->ArchiveDatabase = $ArchiveDatabase;
    }

    /**
     * @return integer
     */
    public function getArchiveTimestamp()
    {

        return $this->ArchiveTimestamp;
    }

    /**
     * @param integer $ArchiveTimestamp
     */
    public function setArchiveTimestamp($ArchiveTimestamp)
    {

        $this->ArchiveTimestamp = $ArchiveTimestamp;
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
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
    public function setAccountUsername($AccountUsername)
    {

        $this->AccountUsername = $AccountUsername;
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
    public function setServiceTblConsumer(
        TblConsumer $tblConsumer = null
    ) {

        $this->serviceTblConsumer = ( null === $tblConsumer ? null : $tblConsumer->getId() );
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
    public function setConsumerName($ConsumerName)
    {

        $this->ConsumerName = $ConsumerName;
    }

    /**
     * @return string
     */
    public function getConsumerAcronym()
    {

        return $this->ConsumerAcronym;
    }

    /**
     * @param string $ConsumerAcronym
     */
    public function setConsumerAcronym($ConsumerAcronym)
    {

        $this->ConsumerAcronym = $ConsumerAcronym;
    }

    /**
     * @return string
     */
    public function getEntity()
    {

        return $this->Entity;
    }

    /**
     * @param string $Entity
     */
    public function setEntity($Entity)
    {

        $this->Entity = $Entity;
    }

    /**
     * @return integer
     */
    public function getArchiveType()
    {

        return $this->ArchiveType;
    }

    /**
     * @param integer $ArchiveType
     */
    public function setArchiveType($ArchiveType)
    {

        $this->ArchiveType = $ArchiveType;
    }

}
