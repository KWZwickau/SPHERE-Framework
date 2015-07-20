<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data\Binding;

use SPHERE\Application\System\Gatekeeper\Account\Data\Connection;
use SPHERE\Application\System\Gatekeeper\Account\Data\Entity\TblAccount;
use SPHERE\Application\System\Gatekeeper\Account\Data\Entity\TblSession;

/**
 * Class Account
 *
 * @package SPHERE\Application\System\Gatekeeper\Account\Data\Binding
 */
class Account extends Connection
{

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
        $Entity = $this->Entity->getEntityManager()->getEntity( 'TblSession' )
            ->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null === $Entity) {
            return false;
        } else {
            return $Entity->getTblAccount();
        }
    }


    /**
     * @param string           $Username
     * @param string           $Password
     * @param TblAccountType   $tblAccountType
     * @param TblAccountRole   $tblAccountRole
     * @param null|TblToken    $tblToken
     * @param null|TblPerson   $tblPerson
     * @param null|TblConsumer $tblConsumer
     *
     * @return TblAccount
     */
    public function actionCreateAccount(
        $Username,
        $Password,
        $tblAccountType,
        $tblAccountRole = null,
        $tblToken = null,
        $tblPerson = null,
        $tblConsumer = null
    ) {

        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccount' )
            ->findOneBy( array( TblAccount::ATTR_USERNAME => $Username ) );
        if (null === $Entity) {
            $Entity = new TblAccount( $Username );
            $Entity->setPassword( hash( 'sha256', $Password ) );
            $Entity->setTblAccountType( $tblAccountType );
            $Entity->setTblAccountRole( $tblAccountRole );
            $Entity->setDataGatekeeperToken( $tblToken );
            $Entity->setDataManagementPerson( $tblPerson );
            $Entity->setDataGatekeeperConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            System::DataProtocol()->executeCreateInsertEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
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

        $Entity = $this->getEntityManager()->getEntity( 'TblAccount' )
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

        $Entity = $this->getEntityManager()->getEntityById( 'TblAccount', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccountSession
     */
    public function getSessionById( $Id )
    {

        $Entity = $this->getEntityManager()->getEntityById( 'TblAccountSession', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool|Entity\TblAccount[]
     */
    public function getAccountAllByToken( TblToken $tblToken )
    {

        $EntityList = $this->getEntityManager()->getEntity( 'TblAccount' )->findBy( array(
            TblAccount::ATTR_Data_GATEKEEPER_TOKEN => $tblToken->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|Entity\TblAccount[]
     */
    public function getAccountAllByPerson( TblPerson $tblPerson )
    {

        $EntityList = $this->getEntityManager()->getEntity( 'TblAccount' )->findBy( array(
            TblAccount::ATTR_Data_MANAGEMENT_PERSON => $tblPerson->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string         $Username
     * @param string         $Password
     * @param TblAccountType $tblAccountType
     *
     * @return bool|TblAccount
     */
    public function entityAccountByCredential( $Username, $Password, TblAccountType $tblAccountType )
    {

        $Entity = $this->getEntityManager()->getEntity( 'TblAccount' )
            ->findOneBy( array(
                TblAccount::ATTR_USERNAME         => $Username,
                TblAccount::ATTR_PASSWORD         => hash( 'sha256', $Password ),
                TblAccount::ATTR_TBL_ACCOUNT_TYPE => $tblAccountType->getId()
            ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblAccountRole $tblAccountRole
     *
     * @return bool|TblAccess[]
     */
    public function entityAccessAllByAccountRole( TblAccountRole $tblAccountRole )
    {

        $EntityList = $this->getEntityManager()->getEntity( 'TblAccountAccessList' )->findBy( array(
            TblAccountAccessList::ATTR_TBL_ACCOUNT_ROLE => $tblAccountRole->getId()
        ) );
        array_walk( $EntityList, function ( TblAccountAccessList &$V ) {

            $V = $V->getDataGatekeeperAccess();
        } );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAccountSession[]
     */
    public function entitySessionAllByAccount( TblAccount $tblAccount )
    {

        $EntityList = $this->getEntityManager()->getEntity( 'TblAccountSession' )->findBy( array(
            TblAccountSession::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblAccount  $tblAccount
     * @param null|string $Session
     * @param integer     $Timeout
     *
     * @return TblAccountSession
     */
    public function createSession( TblAccount $tblAccount, $Session = null, $Timeout = 1800 )
    {

        if (null === $Session) {
            $Session = session_id();
        }
        $Manager = $this->Binding->getEntityManager();
        /** @var TblSession $Entity */
        $Entity = $Manager->getEntity( 'TblSession' )
            ->findOneBy( array( TblSession::ATTR_SESSION => $Session ) );
        if (null !== $Entity) {
            System::DataProtocol()->executeCreateDeleteEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
            $Manager->killEntity( $Entity );
        }
        $Entity = new TblAccountSession( $Session );
        $Entity->setTblAccount( $tblAccount );
        $Entity->setTimeout( time() + $Timeout );
        $Manager->saveEntity( $Entity );
        System::DataProtocol()->executeCreateInsertEntry( $this->getDatabaseHandler()->getDatabaseName(), $Entity );
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblAccountType
     */
    public function actionCreateAccountType( $Name )
    {

        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccountType' )
            ->findOneBy( array( TblAccountType::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblAccountType();
            $Entity->setName( $Name );
            $Manager->saveEntity( $Entity );
            System::DataProtocol()->executeCreateInsertEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblAccountRole
     */
    public function actionCreateAccountRole( $Name )
    {

        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccountRole' )
            ->findOneBy( array( TblAccountRole::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblAccountRole();
            $Entity->setName( $Name );
            $Manager->saveEntity( $Entity );
            System::DataProtocol()->executeCreateInsertEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
        }
        return $Entity;
    }

    /**
     * @param null|string $Session
     *
     * @return bool
     */
    public function actionDestroySession( $Session = null )
    {

        if (null === $Session) {
            $Session = session_id();
        }

        $Manager = $this->getEntityManager();
        /** @var TblAccountSession $Entity */
        $Entity = $Manager->getEntity( 'TblAccountSession' )
            ->findOneBy( array( TblAccountSession::ATTR_SESSION => $Session ) );
        if (null !== $Entity) {
            System::DataProtocol()->executeCreateDeleteEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
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
    public function actionDestroyAccount( TblAccount $tblAccount )
    {

        $Manager = $this->getEntityManager();
        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        if (null !== $Entity) {
            System::DataProtocol()->executeCreateDeleteEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param string          $Password
     * @param null|TblAccount $tblAccount
     *
     * @return bool
     */
    public function actionChangePassword( $Password, TblAccount $tblAccount = null )
    {

        if (null === $tblAccount) {
            $tblAccount = $this->entityAccountBySession();
        }
        $Manager = $this->getEntityManager();
        /**
         * @var TblAccount $Protocol
         * @var TblAccount $Entity
         */
        $Entity = $Manager->getEntityById( 'TblAccount', $tblAccount->getId() );
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPassword( hash( 'sha256', $Password ) );
            $Manager->saveEntity( $Entity );
            System::DataProtocol()->executeCreateUpdateEntry( $this->getDatabaseHandler()->getDatabaseName(),
                $Protocol,
                $Entity );
            return true;
        }
        return false;
    }
}
