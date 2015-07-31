<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
 */
class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        $this->createIdentification( 'Student', 'Schüler / Eltern' );
        $this->createIdentification( 'Teacher', 'Lehrer' );
        $this->createIdentification( 'Management', 'Verwaltung' );
        $this->createIdentification( 'System', 'System' );

        $tblToken = Token::useService()->getTokenById( 1 );
        $tblConsumer = Consumer::useService()->getConsumerById( 1 );
        $tblAccount = $this->createAccount( 'System', 'System', $tblToken, $tblConsumer );
        $tblIdentification = $this->getIdentificationByName( 'System' );
        $this->addAccountAuthentication( $tblAccount, $tblIdentification );
        $tblRole = Access::useService()->getRoleByName( 'Administrator' );
        $this->addAccountAuthorization( $tblAccount, $tblRole );
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblIdentification
     */
    public function createIdentification( $Name, $Description = '' )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblIdentification' )->findOneBy( array( TblIdentification::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblIdentification( $Name );
            $Entity->setDescription( $Description );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string           $Username
     * @param string           $Password
     * @param null|TblToken    $tblToken
     * @param null|TblConsumer $tblConsumer
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount
     */
    public function createAccount( $Username, $Password, $tblToken = null, $tblConsumer = null )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccount' )->findOneBy( array( TblAccount::ATTR_USERNAME => $Username ) );
        if (null === $Entity) {
            $Entity = new TblAccount( $Username );
            $Entity->setPassword( hash( 'sha256', $Password ) );
            $Entity->setServiceTblToken( $tblToken );
            $Entity->setServiceTblConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblIdentification' )
            ->findOneBy( array( TblIdentification::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblAccount        $tblAccount
     * @param TblIdentification $tblIdentification
     *
     * @return TblAuthentication
     */
    public function addAccountAuthentication( TblAccount $tblAccount, TblIdentification $tblIdentification )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAuthentication' )
            ->findOneBy( array(
                TblAuthentication::ATTR_TBL_ACCOUNT        => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblAuthentication();
            $Entity->setTblAccount( $tblAccount );
            $Entity->setTblIdentification( $tblIdentification );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param TblRole                                                                                 $tblRole
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization
     */
    public function addAccountAuthorization( TblAccount $tblAccount, TblRole $tblRole )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAuthorization' )
            ->findOneBy( array(
                TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthorization::SERVICE_TBL_ROLE => $tblRole->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblAuthorization();
            $Entity->setTblAccount( $tblAccount );
            $Entity->setServiceTblRole( $tblRole );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @return TblIdentification[]|bool
     */
    public function getIdentificationAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblIdentification' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole     $tblRole
     *
     * @return bool
     */
    public function removeAccountAuthorization( TblAccount $tblAccount, TblRole $tblRole )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization $Entity */
        $Entity = $Manager->getEntity( 'TblAccountRole' )
            ->findOneBy( array(
                TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthorization::SERVICE_TBL_ROLE => $tblRole->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param TblIdentification                                                                       $tblIdentification
     *
     * @return bool
     */
    public function removeAccountAuthentication( TblAccount $tblAccount, TblIdentification $tblIdentification )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthentication $Entity */
        $Entity = $Manager->getEntity( 'TblAccountIdentification' )
            ->findOneBy( array(
                TblAuthentication::ATTR_TBL_ACCOUNT        => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param string $Username
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount
     */
    public function getAccountByUsername( $Username )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )
            ->findOneBy( array( TblAccount::ATTR_USERNAME => $Username ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount
     */
    public function getAccountById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblAccount', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount[]|bool
     */
    public function getAccountAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblIdentification', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession
     */
    public function getSessionById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblSession', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken $tblToken
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount[]
     */
    public function getAccountAllByToken( TblToken $tblToken )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )->findBy( array(
            TblAccount::SERVICE_TBL_TOKEN => $tblToken->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization[]
     */
    public function getAuthorizationAllByAccount( TblAccount $tblAccount )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAuthorization' )->findBy( array(
            TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount[]
     */
    public function getAccountAllByConsumer( TblConsumer $tblConsumer )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )->findBy( array(
            TblAccount::SERVICE_TBL_CONSUMER => $tblConsumer->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string            $Username
     * @param string            $Password
     * @param TblIdentification $tblIdentification
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount
     */
    public function getAccountByCredential( $Username, $Password, TblIdentification $tblIdentification = null )
    {

        $tblAccount = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )
            ->findOneBy( array(
                TblAccount::ATTR_USERNAME => $Username,
                TblAccount::ATTR_PASSWORD => hash( 'sha256', $Password )
            ) );
        // Account not available
        if (null === $tblAccount) {
            return false;
        }

        $tblAuthentication = $this->Connection->getEntityManager()->getEntity( 'TblAuthentication' )
            ->findOneBy( array(
                TblAuthentication::ATTR_TBL_ACCOUNT        => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ) );
        // Identification not valid
        if (null === $tblAuthentication) {
            return false;
        }

        return $tblAccount;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     *
     * @return bool|TblSession[]
     */
    public function getSessionAllByAccount( TblAccount $tblAccount )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblSession' )->findBy( array(
            TblSession::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param null|string                                                                             $Session
     * @param integer                                                                                 $Timeout
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession
     */
    public function createSession( TblAccount $tblAccount, $Session = null, $Timeout = 1800 )
    {

        if (null === $Session) {
            $Session = session_id();
        }
        $Manager = $this->Connection->getEntityManager();
        /** @var TblSession $Entity */
        $Entity = $Manager->getEntity( 'TblSession' )->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
        }
        $Entity = new TblSession( $Session );
        $Entity->setTblAccount( $tblAccount );
        $Entity->setTimeout( time() + $Timeout );
        $Manager->saveEntity( $Entity );
        Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        return $Entity;
    }

    /**
     * @param null|string $Session
     *
     * @return bool
     */
    public function destroySession( $Session = null )
    {

        if (null === $Session) {
            $Session = session_id();
        }

        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession $Entity */
        $Entity = $Manager->getEntity( 'TblSession' )->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount( TblAccount $tblAccount )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Entity */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param string                                                                                  $Password
     *
     * @return bool
     */
    public function changePassword( $Password, TblAccount $tblAccount = null )
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->Connection->getEntityManager();
        /**
         * @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Protocol
         * @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Entity
         */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPassword( hash( 'sha256', $Password ) );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(), $Protocol, $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param null|string $Session
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount
     */
    public function getAccountBySession( $Session = null )
    {

        if (null === $Session) {
            $Session = session_id();
        }
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession $Entity */
        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblSession' )
            ->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null === $Entity) {
            return false;
        } else {
            return $Entity->getTblAccount();
        }
    }

    /**
     * @param TblToken                                                                                $tblToken
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeToken( TblToken $tblToken, TblAccount $tblAccount = null )
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->Connection->getEntityManager();
        /**
         * @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Protocol
         * @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Entity
         */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblToken( $tblToken );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(), $Protocol, $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblConsumer                                                                             $tblConsumer
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeConsumer( TblConsumer $tblConsumer, TblAccount $tblAccount = null )
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $Entity */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(), $Protocol, $Entity );
            return true;
        }
        return false;
    }
}
