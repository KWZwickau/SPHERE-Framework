<?php
namespace SPHERE\System\Database\Link;

use MOC\V\Component\Database\Database;
use SPHERE\System\Database\ITypeInterface;

/**
 * Class Connection
 *
 * @package SPHERE\System\Database\Link
 */
class Connection
{

    /** @var null|Database $Connection */
    private $Connection = null;

    /**
     * @param Identifier     $Identifier
     * @param ITypeInterface $Type
     * @param string         $Username
     * @param string         $Password
     * @param string         $Database
     * @param string         $Host
     * @param null|int       $Port
     *
     * @param int            $Timeout
     *
     * @throws \Exception
     */
    final public function __construct(
        Identifier $Identifier,
        ITypeInterface $Type,
        $Username,
        $Password,
        $Database,
        $Host,
        $Port = null,
        $Timeout = 5
    ) {

        $Consumer = $Identifier->getConsumer();
        $this->setConnection(
            $Username, $Password,
            $Database.( empty( $Consumer ) ? '' : '_'.$Consumer ),
            $Type->getIdentifier(),
            $Host, $Port, $Timeout
        );
    }

    /**
     * @return null|Database
     */
    public function getConnection()
    {

        return $this->Connection;
    }

    /**
     * @param string   $Username
     * @param string   $Password
     * @param string   $Database
     * @param string   $Driver
     * @param string   $Host
     * @param null|int $Port
     *
     * @param int      $Timeout
     *
     * @return Connection
     * @throws \Exception
     */
    public function setConnection( $Username, $Password, $Database, $Driver, $Host, $Port, $Timeout = 5 )
    {

        try {
            $this->Connection = Database::getDatabase( $Username, $Password, $Database, $Driver, $Host, $Port, $Timeout );
        } catch( \Exception $E ) {
            try {
                Database::getDatabase( $Username, $Password, null, $Driver, $Host, $Port, $Timeout )
                    ->getSchemaManager()->createDatabase( $Database );
                $this->Connection = Database::getDatabase( $Username, $Password, $Database, $Driver, $Host, $Port, $Timeout );
            } catch( \Exception $E ) {
                throw new \Exception( $E->getMessage(), $E->getCode(), $E );
            }
        }
        return $this;
    }
}
