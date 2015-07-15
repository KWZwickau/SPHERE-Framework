<?php
namespace MOC\V\Core\AutoLoader;

require_once( __DIR__.'/Exception/AutoLoaderException.php' );

require_once( __DIR__.'/Component/Exception/ComponentException.php' );
require_once( __DIR__.'/Component/Exception/Repository/DirectoryNotFoundException.php' );
require_once( __DIR__.'/Component/Exception/Repository/EmptyDirectoryException.php' );
require_once( __DIR__.'/Component/Exception/Repository/EmptyNamespaceException.php' );

require_once( __DIR__.'/Component/IVendorInterface.php' );
require_once( __DIR__.'/Vendor/Vendor.php' );

require_once( __DIR__.'/Component/IParameterInterface.php' );
require_once( __DIR__.'/Component/Parameter/Parameter.php' );
require_once( __DIR__.'/Component/Parameter/Repository/NamespaceParameter.php' );
require_once( __DIR__.'/Component/Parameter/Repository/DirectoryParameter.php' );

require_once( __DIR__.'/Component/IBridgeInterface.php' );
require_once( __DIR__.'/Component/Bridge/Bridge.php' );
require_once( __DIR__.'/Component/Bridge/Repository/MultitonNamespace.php' );
require_once( __DIR__.'/Component/Bridge/Repository/UniversalNamespace.php' );

require_once( __DIR__.'/Vendor/Multiton/NamespaceLoader.php' );
require_once( __DIR__.'/Vendor/Universal/NamespaceLoader/NamespaceMapping.php' );
require_once( __DIR__.'/Vendor/Universal/NamespaceLoader/NamespaceSearch.php' );
require_once( __DIR__.'/Vendor/Universal/NamespaceLoader.php' );

use MOC\V\Core\AutoLoader\Component\Bridge\Repository\MultitonNamespace;
use MOC\V\Core\AutoLoader\Component\Bridge\Repository\UniversalNamespace;
use MOC\V\Core\AutoLoader\Component\IBridgeInterface;
use MOC\V\Core\AutoLoader\Component\IVendorInterface;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;
use MOC\V\Core\AutoLoader\Vendor\Vendor;

/**
 * Class AutoLoader
 *
 * @package MOC\V\Core\AutoLoader
 */
class AutoLoader implements IVendorInterface
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
     * @param string      $Namespace
     * @param string      $Directory
     * @param null|string $Prefix
     *
     * @return IBridgeInterface
     */
    public static function getNamespaceAutoLoader( $Namespace, $Directory, $Prefix = null )
    {

        return self::getMultitonNamespaceAutoLoader( $Namespace, $Directory, $Prefix );
    }

    /**
     * @param string      $Namespace
     * @param string      $Directory
     * @param null|string $Prefix
     *
     * @return IBridgeInterface
     */
    public static function getMultitonNamespaceAutoLoader( $Namespace, $Directory, $Prefix = null )
    {

        $Loader = new AutoLoader(
            new Vendor(
                new MultitonNamespace(
                    new NamespaceParameter( $Namespace ),
                    new DirectoryParameter( $Directory ),
                    new NamespaceParameter( $Prefix ) )
            )
        );
        $Loader->getBridgeInterface()->registerLoader();

        return $Loader->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @param string $Namespace
     * @param string $Directory
     *
     * @return IBridgeInterface
     */
    public static function getUniversalNamespaceAutoLoader( $Namespace, $Directory )
    {

        $Loader = new AutoLoader(
            new Vendor(
                new UniversalNamespace()
            )
        );
        $Loader->getBridgeInterface()->addNamespaceDirectoryMapping(
            new NamespaceParameter( $Namespace ), new DirectoryParameter( $Directory )
        );
        $Loader->getBridgeInterface()->registerLoader();

        return $Loader->getBridgeInterface();
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
     * @return IBridgeInterface
     */
    public function setBridgeInterface( IBridgeInterface $BridgeInterface )
    {

        return $this->VendorInterface->setBridgeInterface( $BridgeInterface );
    }
}
