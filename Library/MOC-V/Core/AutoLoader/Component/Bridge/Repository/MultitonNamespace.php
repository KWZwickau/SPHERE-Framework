<?php
namespace MOC\V\Core\AutoLoader\Component\Bridge\Repository;

use MOC\V\Core\AutoLoader\Component\Bridge\Bridge;
use MOC\V\Core\AutoLoader\Component\IBridgeInterface;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;
use MOC\V\Core\AutoLoader\Exception\AutoLoaderException;
use MOC\V\Core\AutoLoader\Vendor\Multiton\NamespaceLoader;

/**
 * Class MultitonNamespace
 *
 * @package MOC\V\Core\AutoLoader\Component\Bridge
 */
class MultitonNamespace extends Bridge implements IBridgeInterface
{

    /** @var NamespaceLoader $Instance */
    private $Instance = null;

    /**
     * @param NamespaceParameter $NamespaceParameter
     * @param DirectoryParameter $DirectoryParameter
     * @param NamespaceParameter $PrefixParameter
     */
    function __construct(
        NamespaceParameter $NamespaceParameter,
        DirectoryParameter $DirectoryParameter,
        NamespaceParameter $PrefixParameter = null
    ) {

        $this->Instance = new NamespaceLoader(
            $NamespaceParameter->getNamespace(),
            $DirectoryParameter->getDirectory(),
            ( null === $PrefixParameter ? null : $PrefixParameter->getNamespace() )
        );
    }

    /**
     * @param string $ClassName
     *
     * @return bool
     * @throws AutoLoaderException
     */
    public function loadSourceFile( $ClassName )
    {

        return $this->Instance->loadClass( $ClassName );
    }

    /**
     * @param NamespaceParameter $Namespace
     * @param DirectoryParameter $Directory
     *
     * @return IBridgeInterface
     * @throws AutoLoaderException
     */
    public function addNamespaceDirectoryMapping( NamespaceParameter $Namespace, DirectoryParameter $Directory )
    {

        throw new AutoLoaderException( __METHOD__.' MUST NOT be used!' );
    }

    /**
     * @return string
     */
    public function getLoaderHash()
    {

        return $this->Instance->getLoaderHash();
    }
}
