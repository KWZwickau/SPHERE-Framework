<?php
namespace MOC\V\Component\Database;

use MOC\V\Component\Database\Component\Bridge\Repository\Doctrine2ORM;
use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Component\IVendorInterface;
use MOC\V\Component\Database\Component\Parameter\Repository\DatabaseParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\DriverParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\HostParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PasswordParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PortParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\UsernameParameter;
use MOC\V\Component\Database\Exception\DatabaseException;
use MOC\V\Component\Database\Vendor\Vendor;

/**
 * Class Database
 *
 * @package MOC\V\Component\Database
 */
class Database implements IVendorInterface
{

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    function __construct( IVendorInterface $VendorInterface )
    {

        $this->setVendorInterface( $VendorInterface );
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param string $Database
     * @param int    $Driver
     * @param string $Host
     * @param null   $Port
     *
     * @throws DatabaseException
     * @return IBridgeInterface
     */
    public static function getDatabase( $Username, $Password, $Database, $Driver, $Host, $Port = null )
    {

        return self::getDoctrineDatabase( $Username, $Password, $Database, $Driver, $Host, $Port );
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param string $Database
     * @param int    $Driver
     * @param string $Host
     * @param null   $Port
     *
     * @return IBridgeInterface
     */
    public static function getDoctrineDatabase( $Username, $Password, $Database, $Driver, $Host, $Port = null )
    {

        $Doctrine = new Database(
            new Vendor(
                new Doctrine2ORM()
            )
        );

        $Doctrine->getBridgeInterface()->registerConnection(
            new UsernameParameter( $Username ),
            new PasswordParameter( $Password ),
            new DatabaseParameter( $Database ),
            new DriverParameter( $Driver ),
            new HostParameter( $Host ),
            new PortParameter( $Port )
        );

        return $Doctrine->getBridgeInterface();
    }

    /**
     * @return \MOC\V\Component\Database\Component\IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface( IVendorInterface $VendorInterface )
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return \MOC\V\Component\Database\Component\IBridgeInterface
     */
    public function setBridgeInterface( IBridgeInterface $BridgeInterface )
    {

        return $this->VendorInterface->setBridgeInterface( $BridgeInterface );
    }
}
