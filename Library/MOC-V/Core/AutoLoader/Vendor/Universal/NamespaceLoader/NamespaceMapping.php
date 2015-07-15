<?php
namespace MOC\V\Core\AutoLoader\Vendor\Universal\NamespaceLoader;

/**
 * Class NamespaceMapping
 *
 * @package MOC\V\Core\AutoLoader\Vendor\Universal
 */
abstract class NamespaceMapping
{

    /** @var array $NamespaceMapping */
    private $NamespaceMapping = array();

    /**
     * @param string $Namespace
     * @param string $Directory
     *
     * @throws \MOC\V\Core\AutoLoader\Component\Exception\Repository\DirectoryNotFoundException
     */
    final public function addNamespaceMapping( $Namespace, $Directory )
    {

        $Directory = realpath( $Directory );
        if (!isset( $this->NamespaceMapping[$Namespace] )) {
            $this->NamespaceMapping[$Namespace] = array();
        }
        $Directory = rtrim( str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $Directory ), DIRECTORY_SEPARATOR );
        if (!in_array( $Directory, $this->NamespaceMapping[$Namespace] )) {
            array_push( $this->NamespaceMapping[$Namespace], $Directory );
        }
    }

    /**
     * @param string $Namespace
     *
     * @return array
     */
    final public function getNamespaceMapping( $Namespace )
    {

        return isset( $this->NamespaceMapping[$Namespace] ) ? $this->NamespaceMapping[$Namespace] : array();
    }

    /**
     * @return array
     */
    final public function getNamespaceList()
    {

        return array_keys( $this->NamespaceMapping );
    }
}
