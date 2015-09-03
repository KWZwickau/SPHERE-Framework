<?php
namespace MOC\V\Core\AutoLoader\Component\Bridge\Repository;

use MOC\V\Core\AutoLoader\Component\Bridge\Bridge;
use MOC\V\Core\AutoLoader\Component\IBridgeInterface;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;
use MOC\V\Core\AutoLoader\Vendor\Universal\NamespaceLoader;

/**
 * Class UniversalNamespace
 *
 * @package MOC\V\Core\AutoLoader\Component\Bridge
 */
class UniversalNamespace extends Bridge implements IBridgeInterface
{

    /** @var NamespaceLoader $Instance */
    private $Instance = null;

    /**
     *
     */
    public function __construct()
    {

        $this->Instance = new NamespaceLoader();
    }

    /**
     * @param string $ClassName
     *
     * @return bool
     */
    public function loadSourceFile($ClassName)
    {

        return $this->Instance->loadClass($ClassName);
    }

    /**
     * @param NamespaceParameter $Namespace
     * @param DirectoryParameter $Directory
     *
     * @return IBridgeInterface
     */
    public function addNamespaceDirectoryMapping(NamespaceParameter $Namespace, DirectoryParameter $Directory)
    {

        $this->Instance->addNamespaceMapping($Namespace->getNamespace(), $Directory->getDirectory());
        return $this;
    }

    /**
     * @return string
     */
    public function getLoaderHash()
    {

        return $this->Instance->getLoaderHash();
    }
}
