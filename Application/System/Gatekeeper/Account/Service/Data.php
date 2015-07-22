<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Service;

use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblAccount;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblAuthentication;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblIdentification;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblSession;
use SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\Application\System\Information\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\System\Gatekeeper\Account\Service
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

        $this->createIdentification( 'Student' );
        $this->createIdentification( 'Teacher' );
        $this->createIdentification( 'Management' );
        $this->createIdentification( 'System' );
    }

    /**
     * @param string $Name
     *
     * @return TblIdentification
     */
    public function createIdentification( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblIdentification' )->findOneBy( array( TblIdentification::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblIdentification( $Name );
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
     * @return TblAccount
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
     * @param string $Username
     *
     * @return bool|TblAccount
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
     * @return bool|TblAccount
     */
    public function getAccountById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblAccount', $Id );
        return ( null === $Entity ? false : $Entity );
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
     * @param integer $Id
     *
     * @return bool|TblSession
     */
    public function getSessionById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblSession', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByToken( TblToken $tblToken )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccount' )->findBy( array(
            TblAccount::SERVICE_TBL_TOKEN => $tblToken->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblAccount[]
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
     * @return bool|TblAccount
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
                TblAuthentication::SERVICE_TBL_ACCOUNT        => $tblAccount->getId(),
                TblAuthentication::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId()
            ) );
        // Identification not valid
        if (null === $tblAuthentication) {
            return false;
        }

        return $tblAccount;
    }

    /**
     * @param TblAccount $tblAccount
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
     * @param TblAccount  $tblAccount
     * @param null|string $Session
     * @param integer     $Timeout
     *
     * @return TblSession
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
        /** @var TblSession $Entity */
        $Entity = $Manager->getEntity( 'TblSession' )->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount( TblAccount $tblAccount )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Password
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
         * @var TblAccount $Protocol
         * @var TblAccount $Entity
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
     * @return bool|TblAccount
     */
    public function getAccountBySession( $Session = null )
    {

        if (null === $Session) {
            $Session = session_id();
        }
        /** @var TblSession $Entity */
        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblSession' )
            ->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null === $Entity) {
            return false;
        } else {
            return $Entity->getTblAccount();
        }
    }

    /**
     * @param TblToken   $tblToken
     * @param TblAccount $tblAccount
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
         * @var TblAccount $Protocol
         * @var TblAccount $Entity
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
     * @param TblConsumer $tblConsumer
     * @param TblAccount  $tblAccount
     *
     * @return bool
     */
    public function changeConsumer( TblConsumer $tblConsumer, TblAccount $tblAccount = null )
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->Connection->getEntityManager();
        /** @var TblAccount $Entity */
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
