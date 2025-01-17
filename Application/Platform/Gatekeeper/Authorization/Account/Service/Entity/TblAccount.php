<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblAccount extends Element
{

    const ATTR_USERNAME = 'Username';
    const ATTR_PASSWORD = 'Password';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';
    const SERVICE_TBL_TOKEN = 'serviceTblToken';
    const ATTR_USER_ALIAS = 'UserAlias';
    const ATTR_AUTHENTICATOR_APP_SECRET = 'AuthenticatorAppSecret';

    /**
     * @Column(type="string")
     */
    protected $Username;
    /**
     * @Column(type="string")
     */
    protected $Password;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToken;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

    /**
     * @Column(type="string")
     */
    protected ?string $UserAlias;
    /**
     * @Column(type="string")
     */
    protected ?string $BackupMail;

    /**
     * @Column(type="string")
     */
    protected $AuthenticatorAppSecret;

    /**
     * @param string $Username
     */
    public function __construct($Username)
    {

        $this->Username = $Username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param string $Password
     */
    public function setPassword($Password)
    {

        $this->Password = $Password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @param string $Username
     */
    public function setUsername($Username)
    {

        $this->Username = $Username;
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
     * @return bool|TblToken
     */
    public function getServiceTblToken()
    {

        if (null === $this->serviceTblToken) {
            return false;
        } else {
            return Token::useService()->getTokenById($this->serviceTblToken);
        }
    }

    /**
     * @param null|TblToken $tblToken
     */
    public function setServiceTblToken(TblToken $tblToken = null)
    {

        $this->serviceTblToken = ( null === $tblToken ? null : $tblToken->getId() );
    }

    /**
     * @return string
     */
    public function getUserAlias()
    {
        if(!$this->UserAlias){
            return '';
        }
        return strtolower($this->UserAlias);
    }

    /**
     * @param string $UserAlias
     */
    public function setUserAlias($UserAlias)
    {
        if($UserAlias){
            $this->UserAlias = strtolower($UserAlias);
        } else {
            $this->UserAlias = null;
        }

    }

    /**
     * @return string
     */
    public function getRecoveryMail()
    {
        if(!$this->BackupMail){
            return '';
        }
        return strtolower($this->BackupMail);
    }

    /**
     * @param string $RecoveryMail
     */
    public function setRecoveryMail($RecoveryMail)
    {

        if($RecoveryMail){
            $this->BackupMail = strtolower($RecoveryMail);
        } else {
            $this->BackupMail = null;
        }
    }

    /**
     * @return string
     */
    public function getAuthenticatorAppSecret()
    {
        return $this->AuthenticatorAppSecret;
    }

    /**
     * @param string $AuthenticatorAppSecret
     */
    public function setAuthenticatorAppSecret($AuthenticatorAppSecret)
    {
        $this->AuthenticatorAppSecret = $AuthenticatorAppSecret;
    }

    /**
     * @return float|int
     */
    public function getSessionTimeOut()
    {
        if ($this->getHasAuthentication(TblIdentification::NAME_SYSTEM)) {
            $Timeout = ( 60 * 60 * 4 );
        } elseif ($this->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)
            || $this->getHasAuthentication(TblIdentification::NAME_TOKEN)
        ) {
            $Timeout = ( 60 * 60 * 2);
        } elseif ($this->getHasAuthentication(TblIdentification::NAME_CREDENTIAL)
            || $this->getHasAuthentication(TblIdentification::NAME_USER_CREDENTIAL)
        ) {
            $Timeout = ( 60 * 30 );
        } else {
            $Timeout = ( 60 * 10 );
        }

        return $Timeout;
    }

    /**
     * @param string $IdentificationName
     *
     * @return bool
     */
    public function getHasAuthentication(string $IdentificationName): bool
    {
        return Account::useService()->getHasAuthenticationByAccountAndIdentificationName($this, $IdentificationName);
    }
}
