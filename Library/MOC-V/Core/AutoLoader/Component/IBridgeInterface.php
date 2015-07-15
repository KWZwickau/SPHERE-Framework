<?php
namespace MOC\V\Core\AutoLoader\Component;

use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Core\AutoLoader\Component
 */
interface IBridgeInterface
{

    /**
     * @return string
     */
    public function getLoaderHash();

    /**
     * @return IBridgeInterface
     */
    public function registerLoader();

    /**
     * @return IBridgeInterface
     */
    public function unregisterLoader();

    /**
     * @param string $ClassName
     *
     * @return bool
     */
    public function loadSourceFile( $ClassName );

    /**
     * @param NamespaceParameter $Namespace
     * @param DirectoryParameter $Directory
     *
     * @return IBridgeInterface
     */
    public function addNamespaceDirectoryMapping( NamespaceParameter $Namespace, DirectoryParameter $Directory );
}
