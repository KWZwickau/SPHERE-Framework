<?php

namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\Billing\Accounting\Account\Service\Data;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKey;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKeyType;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountType;
use SPHERE\Application\Billing\Accounting\Account\Service\Setup;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string $EntityPath
     * @param string $EntityNamespace
     */
    public function __construct( Identifier $Identifier, $EntityPath, $EntityNamespace )
    {

        $this->Binding = new Binding( $Identifier, $EntityPath, $EntityNamespace );
        $this->Structure = new Structure( $Identifier );
    }

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService( $Simulate, $withData )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
        if (!$Simulate && $withData) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return array|bool|TblAccount[]
     */
    public function getAccountAll()
    {

        return ( new Data( $this->Binding ) )->entityAccountAll();
    }

    /**
     * @param $Id
     *
     * @return bool
     */
    public function setFibuActivate( $Id )
    {

        return ( new Data( $this->Binding ) )->actionActivateAccount( $Id );
    }

    /**
     * @param $Id
     *
     * @return bool
     */
    public function setFibuDeactivate( $Id )
    {

        return ( new Data( $this->Binding ) )->actionDeactivateAccount( $Id );
    }

    /**
     * @return bool|TblAccountKey[]
     */
    public function entityKeyValueAll()
    {

        return ( new Data( $this->Binding ) )->entityKeyValueAll();
    }

    /**
     * @return bool|TblAccountType[]
     */
    public function entityTypeValueAll()
    {

        return ( new Data( $this->Binding ) )->entityTypeValueAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountType
     */
    public function entityAccountTypeById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityAccountTypeById( $Id );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKey
     */
    public function entityAccountKeyById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityAccountKeyById( $Id );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKeyType
     */
    public function entityAccountKeyTypeById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityAccountKeyTypeById( $Id );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccount
     */
    public function entityAccountById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityAccountById( $Id );
    }

    /**
     * @param bool $IsActive
     *
     * @return bool|TblAccount[]
     */
    public function entityAccountAllByActiveState( $IsActive = true )
    {

        return ( new Data( $this->Binding ) )->entityAccountAllByActiveState( $IsActive );
    }

    /**
     * @param IFormInterface $Stage
     * @param $Account
     *
     * @return IFormInterface|string
     */
    public function executeAddAccount( IFormInterface &$Stage = null, $Account )
    {

        /**
         * Skip to Frontend
         */
        if ( null === $Account ) {
            return $Stage;
        }
        $Error = false;
        if ( isset( $Account['Description'] ) && empty( $Account['Description'] ) ) {
            $Stage->setError( 'Account[Description]', 'Bitte geben sie eine Beschreibung an' );
            $Error = true;
        }
        if ( isset( $Account['Number'] ) && empty( $Account['Number'] ) ) {
            $Stage->setError( 'Account[Number]', 'Bitte geben sie die Nummer an' );
            $Error = true;
        }
        $Account['IsActive'] = 1;

        if ( !$Error ) {
            ( new Data( $this->Binding ) )->actionAddAccount(
                $Account['Number'],
                $Account['Description'],
                $Account['IsActive'],
                ( new Data( $this->Binding ) )->entityAccountKeyById( $Account['Key'] ),
                ( new Data( $this->Binding ) )->entityAccountTypeById( $Account['Type'] ) );
            return new Success( 'Das Konto ist erfasst worden' )
            .new Redirect( '/Billing/Accounting/Account', 2 );
        }
        return $Stage;
    }

}
