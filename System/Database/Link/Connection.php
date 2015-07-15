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
     */
    final public function __construct(
        Identifier $Identifier,
        ITypeInterface $Type,
        $Username,
        $Password,
        $Database,
        $Host,
        $Port = null
    ) {

        $Consumer = $Identifier->getConsumer();
        $this->setConnection(
            $Username, $Password,
            $Database.( empty( $Consumer ) ? '' : '_'.$Consumer ),
            $Type->getIdentifier(),
            $Host, $Port
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
     * @return Connection
     * @throws \Exception
     */
    public function setConnection( $Username, $Password, $Database, $Driver, $Host, $Port )
    {

        try {
            $this->Connection = Database::getDatabase( $Username, $Password, $Database, $Driver, $Host, $Port );
        } catch( \Exception $E ) {
            try {
                Database::getDatabase( $Username, $Password, null, $Driver, $Host, $Port )
                    ->getSchemaManager()->createDatabase( $Database );
                $this->Connection = Database::getDatabase( $Username, $Password, $Database, $Driver, $Host, $Port );
            } catch( \Exception $E ) {
                throw new \Exception( $E->getMessage(), $E->getCode(), $E );
            }
        }
        return $this;
    }
}
