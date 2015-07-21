<?php
namespace SPHERE\Application\System\Information\Protocol\Service;

use SPHERE\Application\System\Information\Protocol\Service\Entity\TblProtocol;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Object;

/**
 * Class Data
 *
 * @package SPHERE\Application\System\Information\Protocol\Service
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

    /**
     * @return TblProtocol[]|bool
     */
    public function getProtocolAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblProtocol' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string           $DatabaseName
     * @param null|TblAccount  $tblAccount
     * @param null|TblPerson   $tblPerson
     * @param null|TblConsumer $tblConsumer
     * @param null|Object      $FromEntity
     * @param null|Object      $ToEntity
     *
     * @return false|TblProtocol
     */
    protected function createProtocolEntry(
        $DatabaseName,
        TblAccount $tblAccount = null,
        TblPerson $tblPerson = null,
        TblConsumer $tblConsumer = null,
        Object $FromEntity = null,
        Object $ToEntity = null
    ) {

        // Skip if nothing changed
        if (null !== $FromEntity && null !== $ToEntity) {
            $From = $FromEntity->__toArray();
            sort( $From );
            $To = $ToEntity->__toArray();
            sort( $To );
            if ($From === $To) {
                return false;
            }
        }

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblProtocol();
        $Entity->setProtocolDatabase( $DatabaseName );
        $Entity->setProtocolTimestamp( time() );
        if ($tblAccount) {
            $Entity->setServiceGatekeeperAccount( $tblAccount );
            $Entity->setAccountUsername( $tblAccount->getUsername() );
        }
        if ($tblPerson) {
            $Entity->setServiceManagementPerson( $tblPerson );
            $Entity->setPersonFirstName( $tblPerson->getFirstName() );
            $Entity->setPersonLastName( $tblPerson->getLastName() );
        }
        if ($tblConsumer) {
            $Entity->setServiceGatekeeperConsumer( $tblConsumer );
            $Entity->setConsumerName( $tblConsumer->getName() );
            $Entity->setConsumerSuffix( $tblConsumer->getDatabaseSuffix() );
        }
        $Entity->setEntityFrom( ( $FromEntity ? serialize( $FromEntity ) : null ) );
        $Entity->setEntityTo( ( $ToEntity ? serialize( $ToEntity ) : null ) );

        $Manager->saveEntity( $Entity );

        return $Entity;
    }

    /**
     * @return string
     */
    protected function tableProtocolAll()
    {

        return self::extensionDataTables(
            $this->getEntityManager()->getEntity( 'TblProtocol' )
        )
            ->setCallbackFunction( function ( TblProtocol &$V ) {

                $Editor = new LayoutList( array(
                    'Database: '.$V->getProtocolDatabase(),
                    'Consumer: '.$V->getConsumerName().' '.$V->getConsumerSuffix(),
                    'Login: '.$V->getAccountUsername(),
                    'Person: '.$V->getPersonFirstName().' '.$V->getPersonLastName(),
                    'Time: '.date( 'd.m.Y H:i:s', $V->getProtocolTimestamp() ),
                ) );

                $DataOrigin = self::fixObject( unserialize( $V->getEntityFrom() ) );
                $DataCommit = self::fixObject( unserialize( $V->getEntityTo() ) );

                if ($DataOrigin && $DataCommit) {
                    $Data = (array)$DataOrigin;
                    array_walk( $Data, function ( &$Entity, $Index ) {

                        $Entity = new TableRow( array( new TableColumn( $Index ), new TableColumn( $Entity ) ) );
                    } );
                    $TableOrigin = new Table(
                        new TableHead( new TableRow(
                            new TableColumn( str_replace( '\\', '\\&shy;', get_class( $DataOrigin ) ), 2 )
                        ) ), new TableBody( $Data )
                    );
                    $Data = (array)$DataCommit;
                    array_walk( $Data, function ( &$Entity, $Index ) {

                        $Entity = new TableRow( array( new TableColumn( $Index ), new TableColumn( $Entity ) ) );
                    } );
                    $TableCommit = new Table(
                        new TableHead( new TableRow(
                            new TableColumn( str_replace( '\\', '\\&shy;', get_class( $DataCommit ) ), 2 )
                        ) ), new TableBody( $Data )
                    );

                    $V = array(
                        'Id'     => $V->getId(),
                        'Editor' => $Editor->__toString(),
                        'Origin' => $TableOrigin->__toString(),
                        'Commit' => $TableCommit->__toString()
                    );
                } elseif ($DataOrigin) {
                    $Data = (array)$DataOrigin;
                    array_walk( $Data, function ( &$Entity, $Index ) {

                        $Entity = new TableRow( array( new TableColumn( $Index ), new TableColumn( $Entity ) ) );
                    } );
                    $Table = new Table(
                        new TableHead( new TableRow(
                            new TableColumn( str_replace( '\\', '\\&shy;', get_class( $DataOrigin ) ), 2 )
                        ) ), new TableBody( $Data )
                    );
                    $V = array(
                        'Id'     => $V->getId(),
                        'Editor' => $Editor->__toString(),
                        'Origin' => $Table->__toString(),
                        'Commit' => ''
                    );
                } elseif ($DataCommit) {
                    $Data = (array)$DataCommit;
                    array_walk( $Data, function ( &$Entity, $Index ) {

                        $Entity = new TableRow( array( new TableColumn( $Index ), new TableColumn( $Entity ) ) );
                    } );
                    $Table = new Table(
                        new TableHead( new TableRow(
                            new TableColumn( str_replace( '\\', '\\&shy;', get_class( $DataCommit ) ), 2 )
                        ) ), new TableBody( $Data )
                    );
                    $V = array(
                        'Id'     => $V->getId(),
                        'Editor' => $Editor->__toString(),
                        'Origin' => '',
                        'Commit' => $Table->__toString()
                    );
                }
                return $V;
            } )
            ->getResult();
    }

    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     *
     * @since  1.1.0
     *
     * @param  object $object __PHP_Incomplete_Class
     *
     * @return object
     */
    private static function fixObject( $object )
    {

        if (!is_object( $object ) && gettype( $object ) == 'object') {
            // preg_replace_callback handler. Needed to calculate new key-length.
            $fix_key = create_function(
                '$matches',
                'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
            );
            // 1. Serialize the object to a string.
            $dump = serialize( $object );
            // 2. Change class-type to 'stdClass'.
            preg_match( '/^O:\d+:"[^"]++"/', $dump, $match );
            $dump = preg_replace( '/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $dump );
            // 3. Make private and protected properties public.
            $dump = preg_replace_callback( '/:\d+:"\0.*?\0([^"]+)"/', $fix_key, $dump );
            // 4. Unserialize the modified object again.
            $dump = unserialize( $dump );
            $dump->ERROR = new Danger( "Structure mismatch!<br/>".$match[0]."<br/>Please delete this Item" );
            return $dump;
        } else {
            return $object;
        }
    }
}
